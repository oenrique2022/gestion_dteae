<?php
require_once __DIR__ . '/Database.php';

class Contrato {
    private $conn;
    private static $columnasContratosEquiposCache = null;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    /** @param list<array<string,mixed>> $filas */
    private static function filasClavesMinusculas(array $filas): array {
        return array_map(
            static function (array $row) {
                return array_change_key_case($row, CASE_LOWER);
            },
            $filas
        );
    }

    /** @return array<string,bool> */
    private function columnasContratosEquipos(): array {
        if (is_array(self::$columnasContratosEquiposCache)) {
            return self::$columnasContratosEquiposCache;
        }
        $cols = [];
        try {
            $st = $this->conn->query('SHOW COLUMNS FROM contratos_equipos');
            $rows = $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
            foreach ($rows as $r) {
                $k = strtolower((string) ($r['Field'] ?? ''));
                if ($k !== '') {
                    $cols[$k] = true;
                }
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }
        self::$columnasContratosEquiposCache = $cols;
        return $cols;
    }
    public function crearContratoCompleto($datosContrato, $detallesEquipos, $entregas, $archivos) {
        $this->conn->beginTransaction();
        try {
            // 1. Guardar Contrato Principal
            $sql1 = "INSERT INTO contratos (numero_contrato, nombre_contrato, fecha_inicio, fecha_fin, proveedor_id, fuente_financiamiento_id, nombre_encargado, comentarios, usuario_creador) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'System')";
            $stmt1 = $this->conn->prepare($sql1);
            $stmt1->execute([
                $datosContrato['numero_contrato'], $datosContrato['nombre_contrato'], $datosContrato['fecha_inicio'],
                $datosContrato['fecha_fin'], $datosContrato['proveedor_id'], $datosContrato['fuente_financiamiento_id'],
                $datosContrato['nombre_encargado'], $datosContrato['comentarios']
            ]);
            $idContrato = $this->conn->lastInsertId();

            // 2. Guardar Líneas de Equipos
            $colsCE = $this->columnasContratosEquipos();
            $campos = ['contrato_id', 'equipo_id', 'cantidad', 'precio', 'marca'];
            $extractores = [
                static fn($ctx, $eq) => $ctx['idContrato'],
                static fn($ctx, $eq) => $eq['id'] ?? null,
                static fn($ctx, $eq) => $eq['cantidad'] ?? 0,
                static fn($ctx, $eq) => $eq['precio'] ?? 0,
                static fn($ctx, $eq) => $eq['marca'] ?? '',
            ];
            if (!empty($colsCE['modelo'])) {
                $campos[] = 'modelo';
                $extractores[] = static fn($ctx, $eq) => $eq['modelo'] ?? '';
            }
            if (!empty($colsCE['descripcion'])) {
                $campos[] = 'descripcion';
                $extractores[] = static fn($ctx, $eq) => $eq['descripcion'] ?? '';
            }
            $ph = implode(', ', array_fill(0, count($campos), '?'));
            $sql2 = 'INSERT INTO contratos_equipos (' . implode(', ', $campos) . ") VALUES ($ph)";
            $stmt2 = $this->conn->prepare($sql2);
            foreach ($detallesEquipos as $equipo) {
                $vals = [];
                foreach ($extractores as $ex) {
                    $vals[] = $ex(['idContrato' => $idContrato], $equipo);
                }
                $stmt2->execute($vals);
            }

            // 3. Guardar Archivos Adjuntos (LÓGICA RESTAURADA)
            $sql3 = "INSERT INTO documentos_contratos (contrato_id, nombre_archivo, ruta_archivo, descripcion) VALUES (?, ?, ?, ?)";
            $stmt3 = $this->conn->prepare($sql3);
            $rutaBase = '/uploads/contratos/';
            foreach ($archivos as $archivo) {
                $nombreOriginal = $archivo['nombre'];
                $nombreUnico = $idContrato . '_' . uniqid() . '_' . preg_replace("/[^a-zA-Z0-9\._-]/", "_", $nombreOriginal);
                $rutaDestino = PROJECT_ROOT . '/public' . $rutaBase . $nombreUnico;
                
                if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                    $stmt3->execute([$idContrato, $nombreOriginal, $rutaBase . $nombreUnico, $archivo['descripcion']]);
                }
            }
            
            // 4. Guardar Entregas y su Detalle
            $sql_entrega = "INSERT INTO entregas (id_contrato, id_institucion, fecha_entrega, estado, firma_responsable, comentarios, usuario_creador) VALUES (?, ?, ?, ?, ?, ?, 'System')";
            $stmt_entrega = $this->conn->prepare($sql_entrega);
            $sql_detalle = "INSERT INTO entregas_detalle (id_entrega, id_equipo, cantidad, precio, comentario) VALUES (?, ?, ?, ?, ?)";
            $stmt_detalle = $this->conn->prepare($sql_detalle);

            foreach ($entregas as $entrega) {
                $stmt_entrega->execute([ $idContrato, $entrega['id_institucion'], !empty($entrega['fecha_entrega']) ? $entrega['fecha_entrega'] : null, $entrega['estado'], $entrega['firma_responsable'], $entrega['comentarios'] ]);
                $idEntrega = $this->conn->lastInsertId();

                if (!empty($entrega['items'])) {
                    foreach ($entrega['items'] as $item) {
                        if ($item['cantidad'] > 0) {
                            $stmt_detalle->execute([$idEntrega, $item['equipo_id'], $item['cantidad'], 0.00, '']);
                        }
                    }
                }
            }

            $this->conn->commit();
            return [
                'success' => true,
                'message' => 'Contrato creado exitosamente.',
                'id_contrato' => (int) $idContrato,
            ];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error al crear el contrato: ' . $e->getMessage()];
        }
    }

    public function leerTodos() {
        try {
            $query = "SELECT 
                        c.id, 
                        c.numero_contrato, 
                        c.nombre_contrato, 
                        c.fecha_inicio, 
                        p.nombre_proveedor 
                      FROM contratos c
                      LEFT JOIN proveedores p ON c.proveedor_id = p.id_proveedor
                      ORDER BY c.fecha_inicio DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    public function eliminar($id) {
        // Para que esto funcione bien, asegúrate de que tus tablas
        // (contratos_equipos, documentos_contratos, entregas) tengan configurado
        // "ON DELETE CASCADE" en sus llaves foráneas hacia la tabla de contratos.
        // Si no, la base de datos podría impedir el borrado.
        try {
            $query = "DELETE FROM contratos WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            // El error puede ocurrir si hay registros hijos y no hay CASCADE
            error_log($e->getMessage());
            return false;
        }
    }
    // ... (después de los métodos que ya tienes) ...
    public function leerUnoCompleto($id) {
        $contrato = [];
        try {
            // 1. Obtener datos generales del contrato (sin cambios)
            $stmt = $this->conn->prepare("SELECT * FROM contratos WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $gen = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$gen) {
                return null;
            }
            $contrato['generales'] = array_change_key_case($gen, CASE_LOWER);
    
            // 2. Obtener equipos asociados (sin cambios)
            $stmt = $this->conn->prepare("SELECT * FROM contratos_equipos WHERE contrato_id = :id");
            $stmt->execute([':id' => $id]);
            $contrato['equipos'] = self::filasClavesMinusculas($stmt->fetchAll(PDO::FETCH_ASSOC));
    
            // 3. Obtener documentos asociados (sin cambios)
            $stmt = $this->conn->prepare("SELECT * FROM documentos_contratos WHERE contrato_id = :id");
            $stmt->execute([':id' => $id]);
            $contrato['documentos'] = self::filasClavesMinusculas($stmt->fetchAll(PDO::FETCH_ASSOC));
            if (!empty($contrato['documentos'])) {
                $idsDoc = array_values(array_filter(array_map(
                    static function ($d) {
                        return isset($d['id']) ? (int) $d['id'] : 0;
                    },
                    $contrato['documentos']
                )));
                $comentariosPorDoc = [];
                if (!empty($idsDoc)) {
                    try {
                        $ph = implode(',', array_fill(0, count($idsDoc), '?'));
                        $qCom = $this->conn->prepare(
                            "SELECT * FROM comentarios_documentos_contrato WHERE documento_id IN ($ph) ORDER BY fecha_comentario DESC, id DESC"
                        );
                        $qCom->execute($idsDoc);
                        $rowsCom = self::filasClavesMinusculas($qCom->fetchAll(PDO::FETCH_ASSOC));
                        foreach ($rowsCom as $rowCom) {
                            $k = (int) ($rowCom['documento_id'] ?? 0);
                            if ($k <= 0) {
                                continue;
                            }
                            if (!isset($comentariosPorDoc[$k])) {
                                $comentariosPorDoc[$k] = [];
                            }
                            $comentariosPorDoc[$k][] = $rowCom;
                        }
                    } catch (PDOException $e) {
                        error_log('comentarios_documentos_contrato (tabla opcional): ' . $e->getMessage());
                    }
                }
                foreach ($contrato['documentos'] as &$docRef) {
                    $idDoc = isset($docRef['id']) ? (int) $docRef['id'] : 0;
                    $docRef['comentarios_adicionales'] = $comentariosPorDoc[$idDoc] ?? [];
                }
                unset($docRef);
            }
    
            // 4. Entregas y detalles (misma lógica que en la UI: centros = id_institucion por fila en `entregas`)
            // SQL 1: SELECT * FROM entregas WHERE id_contrato = :id ORDER BY id_entrega
            // SQL 2: SELECT * FROM entregas_detalle WHERE id_entrega IN (...) ORDER BY id_entrega, id_equipo
            $stmt_entregas = $this->conn->prepare("SELECT * FROM entregas WHERE id_contrato = :id ORDER BY id_entrega");
            $stmt_entregas->execute([':id' => $id]);
            $filas_entregas = self::filasClavesMinusculas($stmt_entregas->fetchAll(PDO::FETCH_ASSOC));

            if ($filas_entregas === []) {
                $contrato['entregas'] = [];
            } else {
                $ids_entrega = array_values(array_filter(
                    array_column($filas_entregas, 'id_entrega'),
                    static function ($v) {
                        return $v !== null && $v !== '';
                    }
                ));
                $detalle_por_entrega = [];
                if ($ids_entrega !== []) {
                    $placeholders = implode(',', array_fill(0, count($ids_entrega), '?'));
                    $stmt_detalles = $this->conn->prepare(
                        "SELECT * FROM entregas_detalle WHERE id_entrega IN ($placeholders) ORDER BY id_entrega, id_equipo"
                    );
                    $stmt_detalles->execute($ids_entrega);
                    $todas_lineas = self::filasClavesMinusculas($stmt_detalles->fetchAll(PDO::FETCH_ASSOC));

                    foreach ($todas_lineas as $linea) {
                        $ie = (int) $linea['id_entrega'];
                        if (!isset($detalle_por_entrega[$ie])) {
                            $detalle_por_entrega[$ie] = [];
                        }
                        $detalle_por_entrega[$ie][] = $linea;
                    }
                }

                $docs_por_inst = [];
                try {
                    $stmt_docs_contrato = $this->conn->prepare(
                        'SELECT * FROM documentos_entrega WHERE id_contrato = :id ORDER BY fecha_subida DESC'
                    );
                    $stmt_docs_contrato->execute([':id' => $id]);
                    $docs_todas = self::filasClavesMinusculas($stmt_docs_contrato->fetchAll(PDO::FETCH_ASSOC));
                    foreach ($docs_todas as $docRow) {
                        $ki = (string) ($docRow['id_institucion'] ?? '');
                        if ($ki === '') {
                            continue;
                        }
                        if (!isset($docs_por_inst[$ki])) {
                            $docs_por_inst[$ki] = [];
                        }
                        $docs_por_inst[$ki][] = $docRow;
                    }
                } catch (PDOException $e) {
                    error_log('documentos_entrega (tabla opcional): ' . $e->getMessage());
                }

                foreach ($filas_entregas as &$fila_entrega) {
                    $ie = (int) $fila_entrega['id_entrega'];
                    $fila_entrega['detalle'] = $detalle_por_entrega[$ie] ?? [];
                    $ki = (string) ($fila_entrega['id_institucion'] ?? '');
                    $fila_entrega['documentos_entrega'] = $docs_por_inst[$ki] ?? [];
                }
                unset($fila_entrega);
                $contrato['entregas'] = $filas_entregas;
            }
    
            return $contrato;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public function eliminarDocumento($id_documento) {
        try {
            // Primero, obtener la ruta del archivo para borrarlo del servidor
            $stmt = $this->conn->prepare("SELECT ruta_archivo FROM documentos_contratos WHERE id = :id");
            $stmt->execute([':id' => $id_documento]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado) {
                $rutaCompleta = PROJECT_ROOT . '/public' . $resultado['ruta_archivo']; // <-- LÍNEA CORRECTA Y DINÁMICA
                if (file_exists($rutaCompleta)) {
                    unlink($rutaCompleta); // Borra el archivo físico
                }
            }

            // Luego, borrar el registro de la base de datos
            $stmt_delete = $this->conn->prepare("DELETE FROM documentos_contratos WHERE id = :id");
            return $stmt_delete->execute([':id' => $id_documento]);

        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    public function agregarComentarioDocumento($idDocumento, $comentario) {
        $idDocumento = (int) $idDocumento;
        $comentario = trim((string) $comentario);
        if ($idDocumento <= 0) {
            return ['success' => false, 'message' => 'Documento no válido.'];
        }
        if ($comentario === '') {
            return ['success' => false, 'message' => 'Escriba un comentario.'];
        }

        try {
            $qDoc = $this->conn->prepare('SELECT id FROM documentos_contratos WHERE id = ?');
            $qDoc->execute([$idDocumento]);
            if (!$qDoc->fetch(PDO::FETCH_ASSOC)) {
                return ['success' => false, 'message' => 'No se encontró el archivo.'];
            }

            $ins = $this->conn->prepare(
                'INSERT INTO comentarios_documentos_contrato (documento_id, comentario, fecha_comentario) VALUES (?, ?, NOW())'
            );
            $ins->execute([$idDocumento, $comentario]);
            $idComentario = (int) $this->conn->lastInsertId();

            $qNew = $this->conn->prepare('SELECT * FROM comentarios_documentos_contrato WHERE id = ?');
            $qNew->execute([$idComentario]);
            $row = $qNew->fetch(PDO::FETCH_ASSOC);
            $comentarioRow = $row ? self::filasClavesMinusculas([$row])[0] : [
                'id' => $idComentario,
                'documento_id' => $idDocumento,
                'comentario' => $comentario,
                'fecha_comentario' => date('Y-m-d H:i:s'),
            ];

            return [
                'success' => true,
                'message' => 'Comentario agregado.',
                'comentario' => $comentarioRow,
            ];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            if (strpos($e->getMessage(), 'comentarios_documentos_contrato') !== false) {
                return [
                    'success' => false,
                    'message' => 'Ejecute el script SQL database/comentarios_documentos_contrato.sql para habilitar comentarios adicionales.',
                ];
            }
            return ['success' => false, 'message' => 'No se pudo guardar el comentario: ' . $e->getMessage()];
        }
    }
    // ... (después del método crearContratoCompleto y los otros que ya tienes) ...

    public function actualizarContratoCompleto($idContrato, $datosContrato, $detallesEquipos, $entregas, $archivos) {
        $this->conn->beginTransaction();
        try {
            // 1. Actualizar Contrato Principal
            $sql1 = "UPDATE contratos SET numero_contrato = ?, nombre_contrato = ?, fecha_inicio = ?, fecha_fin = ?, proveedor_id = ?, fuente_financiamiento_id = ?, nombre_encargado = ?, comentarios = ? WHERE id = ?";
            $stmt1 = $this->conn->prepare($sql1);
            $stmt1->execute([
                $datosContrato['numero_contrato'], $datosContrato['nombre_contrato'], $datosContrato['fecha_inicio'],
                $datosContrato['fecha_fin'], $datosContrato['proveedor_id'], $datosContrato['fuente_financiamiento_id'],
                $datosContrato['nombre_encargado'], $datosContrato['comentarios'], $idContrato
            ]);

            // 2. Limpiar y re-insertar Líneas de Equipos
            $this->conn->prepare("DELETE FROM contratos_equipos WHERE contrato_id = ?")->execute([$idContrato]);
            $colsCE = $this->columnasContratosEquipos();
            $campos = ['contrato_id', 'equipo_id', 'cantidad', 'precio', 'marca'];
            $extractores = [
                static fn($ctx, $eq) => $ctx['idContrato'],
                static fn($ctx, $eq) => $eq['id'] ?? null,
                static fn($ctx, $eq) => $eq['cantidad'] ?? 0,
                static fn($ctx, $eq) => $eq['precio'] ?? 0,
                static fn($ctx, $eq) => $eq['marca'] ?? '',
            ];
            if (!empty($colsCE['modelo'])) {
                $campos[] = 'modelo';
                $extractores[] = static fn($ctx, $eq) => $eq['modelo'] ?? '';
            }
            if (!empty($colsCE['descripcion'])) {
                $campos[] = 'descripcion';
                $extractores[] = static fn($ctx, $eq) => $eq['descripcion'] ?? '';
            }
            $ph = implode(', ', array_fill(0, count($campos), '?'));
            $sql2 = 'INSERT INTO contratos_equipos (' . implode(', ', $campos) . ") VALUES ($ph)";
            $stmt2 = $this->conn->prepare($sql2);
            foreach ($detallesEquipos as $equipo) {
                $vals = [];
                foreach ($extractores as $ex) {
                    $vals[] = $ex(['idContrato' => $idContrato], $equipo);
                }
                $stmt2->execute($vals);
            }
            
            // 3. Guardar NUEVOS Archivos Adjuntos (LÓGICA RESTAURADA)
            $sql3 = "INSERT INTO documentos_contratos (contrato_id, nombre_archivo, ruta_archivo, descripcion) VALUES (?, ?, ?, ?)";
            $stmt3 = $this->conn->prepare($sql3);
            $rutaBase = '/uploads/contratos/';
            foreach ($archivos as $archivo) {
                $nombreOriginal = $archivo['nombre'];
                $nombreUnico = $idContrato . '_' . uniqid() . '_' . preg_replace("/[^a-zA-Z0-9\._-]/", "_", $nombreOriginal);
                $rutaDestino = PROJECT_ROOT . '/public' . $rutaBase . $nombreUnico;
                if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                    $stmt3->execute([$idContrato, $nombreOriginal, $rutaBase . $nombreUnico, $archivo['descripcion']]);
                }
            }

            // 4. Limpiar y re-insertar Entregas y su Detalle
            $stmt_delete_detalles = $this->conn->prepare("DELETE FROM entregas_detalle WHERE id_entrega IN (SELECT id_entrega FROM entregas WHERE id_contrato = ?)");
            $stmt_delete_detalles->execute([$idContrato]);
            $this->conn->prepare("DELETE FROM entregas WHERE id_contrato = ?")->execute([$idContrato]);

            $sql_entrega = "INSERT INTO entregas (id_contrato, id_institucion, fecha_entrega, estado, firma_responsable, comentarios, usuario_creador) VALUES (?, ?, ?, ?, ?, ?, 'System')";
            $stmt_entrega = $this->conn->prepare($sql_entrega);
            $sql_detalle = "INSERT INTO entregas_detalle (id_entrega, id_equipo, cantidad, precio, comentario) VALUES (?, ?, ?, ?, ?)";
            $stmt_detalle = $this->conn->prepare($sql_detalle);

            foreach ($entregas as $entrega) {
                $stmt_entrega->execute([ $idContrato, $entrega['id_institucion'], !empty($entrega['fecha_entrega']) ? $entrega['fecha_entrega'] : null, $entrega['estado'], $entrega['firma_responsable'], $entrega['comentarios'] ]);
                $idEntrega = $this->conn->lastInsertId();
                if (!empty($entrega['items'])) {
                    foreach ($entrega['items'] as $item) {
                        if ($item['cantidad'] > 0) {
                            $stmt_detalle->execute([$idEntrega, $item['equipo_id'], $item['cantidad'], 0.00, '']);
                        }
                    }
                }
            }

            $this->conn->commit();
            return ['success' => true, 'message' => 'Contrato actualizado exitosamente.', 'id_contrato' => (int) $idContrato];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error al actualizar el contrato: ' . $e->getMessage()];
        }
    }

    /**
     * Sube un PDF de comprobante para una entrega identificada por contrato y centro.
     * Marca la(s) entrega(s) como "Entregado" para ese par.
     *
     * @param array{nombre:string,tmp_name:string,size:int,type?:string,error:int} $archivo
     * @return array{success:bool,message?:string,documento?:array,estado?:string}
     */
    public function subirDocumentoEntregaCentro($idContrato, $idInstitucion, array $archivo, $comentario) {
        $idContrato = (int) $idContrato;
        $idInstitucion = (int) $idInstitucion;
        if ($idContrato <= 0 || $idInstitucion <= 0) {
            return ['success' => false, 'message' => 'Datos de contrato o centro no válidos.'];
        }
        if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || empty($archivo['tmp_name'])) {
            return ['success' => false, 'message' => 'No se recibió el archivo o hubo un error de subida.'];
        }
        $nombreOriginal = (string) ($archivo['nombre'] ?? basename((string) $archivo['tmp_name']));
        $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            return ['success' => false, 'message' => 'Solo se permiten archivos PDF.'];
        }
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($archivo['tmp_name']);
            if ($mime !== false && $mime !== 'application/pdf') {
                return ['success' => false, 'message' => 'El archivo no es un PDF válido.'];
            }
        }

        try {
            $chk = $this->conn->prepare(
                'SELECT COUNT(*) FROM entregas WHERE id_contrato = ? AND id_institucion = ?'
            );
            $chk->execute([$idContrato, $idInstitucion]);
            if ((int) $chk->fetchColumn() === 0) {
                return [
                    'success' => false,
                    'message' => 'No hay una entrega registrada para ese centro. Guarde el contrato con la entrega antes de subir el PDF.',
                ];
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [
                'success' => false,
                'message' => 'Falta la tabla documentos_entrega en la base de datos. Ejecute el script database/documentos_entrega.sql.',
            ];
        }

        $rutaBase = '/uploads/entregas_centro/';
        $dirFs = PROJECT_ROOT . '/public' . $rutaBase;
        if (!is_dir($dirFs) && !@mkdir($dirFs, 0755, true) && !is_dir($dirFs)) {
            return ['success' => false, 'message' => 'No se pudo crear la carpeta de archivos en el servidor.'];
        }

        $nombreUnico = $idContrato . '_' . $idInstitucion . '_' . uniqid('', true) . '.pdf';
        $rutaRel = $rutaBase . $nombreUnico;
        $rutaDestino = PROJECT_ROOT . '/public' . $rutaRel;

        if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            return ['success' => false, 'message' => 'No se pudo guardar el archivo en el servidor.'];
        }

        $comentario = trim((string) $comentario);
        if ($comentario === '') {
            $comentario = 'Sin comentario';
        }

        try {
            $this->conn->beginTransaction();
            $sqlIns = 'INSERT INTO documentos_entrega (id_contrato, id_institucion, nombre_archivo, ruta_archivo, comentario, fecha_subida) VALUES (?, ?, ?, ?, ?, NOW())';
            $stmtIns = $this->conn->prepare($sqlIns);
            $stmtIns->execute([$idContrato, $idInstitucion, $nombreOriginal, $rutaRel, $comentario]);
            $idDoc = (int) $this->conn->lastInsertId();

            $sqlUp = 'UPDATE entregas SET estado = ? WHERE id_contrato = ? AND id_institucion = ?';
            $stmtUp = $this->conn->prepare($sqlUp);
            $stmtUp->execute(['Entregado', $idContrato, $idInstitucion]);

            $this->conn->commit();

            $stmtRow = $this->conn->prepare('SELECT * FROM documentos_entrega WHERE id = ?');
            $stmtRow->execute([$idDoc]);
            $fila = $stmtRow->fetch(PDO::FETCH_ASSOC);
            $documento = $fila ? self::filasClavesMinusculas([$fila])[0] : [
                'id' => $idDoc,
                'nombre_archivo' => $nombreOriginal,
                'ruta_archivo' => $rutaRel,
                'comentario' => $comentario,
                'fecha_subida' => date('Y-m-d H:i:s'),
            ];

            return [
                'success' => true,
                'message' => 'PDF registrado correctamente.',
                'documento' => $documento,
                'estado' => 'Entregado',
            ];
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            @unlink($rutaDestino);
            error_log($e->getMessage());
            if (strpos($e->getMessage(), 'documentos_entrega') !== false) {
                return [
                    'success' => false,
                    'message' => 'Ejecute el script SQL database/documentos_entrega.sql para crear la tabla documentos_entrega.',
                ];
            }
            return ['success' => false, 'message' => 'Error al guardar el registro: ' . $e->getMessage()];
        }
    }

    public function obtenerRutasEntregaContrato($idContrato) {
        $idContrato = (int) $idContrato;
        if ($idContrato <= 0) {
            return ['success' => false, 'message' => 'Contrato no válido.'];
        }
        try {
            $q = $this->conn->prepare(
                "SELECT DISTINCT e.id_institucion, COALESCE(ce.codigo_infraestructura, '') AS codigo_infraestructura,
                        COALESCE(ce.nombre_ce, CONCAT('ID ', e.id_institucion)) AS nombre_ce
                 FROM entregas e
                 LEFT JOIN centros_educativos ce ON ce.centro_id = e.id_institucion
                 WHERE e.id_contrato = ?
                 ORDER BY nombre_ce"
            );
            $q->execute([$idContrato]);
            $centros = self::filasClavesMinusculas($q->fetchAll(PDO::FETCH_ASSOC));

            $qR = $this->conn->prepare("SELECT * FROM rutas_entrega WHERE contrato_id = ? ORDER BY id ASC");
            $qR->execute([$idContrato]);
            $rutasRows = self::filasClavesMinusculas($qR->fetchAll(PDO::FETCH_ASSOC));
            $rutasPorCentro = [];
            foreach ($rutasRows as $row) {
                $rutasPorCentro[(string) ($row['id_institucion'] ?? '')] = $row;
            }

            $data = [];
            foreach ($centros as $ce) {
                $idInst = (int) ($ce['id_institucion'] ?? 0);
                if ($idInst <= 0) {
                    continue;
                }
                $k = (string) $idInst;
                $ruta = $rutasPorCentro[$k] ?? null;
                $data[] = [
                    'id_institucion' => $idInst,
                    'centro' => trim((string) ($ce['codigo_infraestructura'] ?? '') . ' ' . (string) ($ce['nombre_ce'] ?? '')),
                    'ruta' => $ruta,
                ];
            }

            return ['success' => true, 'data' => $data];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            if (strpos($e->getMessage(), 'rutas_entrega') !== false) {
                return ['success' => false, 'message' => 'Ejecute el script SQL database/rutas_entrega.sql.'];
            }
            return ['success' => false, 'message' => 'No se pudieron cargar las rutas: ' . $e->getMessage()];
        }
    }

    public function guardarRutaEntrega(array $data) {
        $idContrato = (int) ($data['id_contrato'] ?? 0);
        $idInstitucion = (int) ($data['id_institucion'] ?? 0);
        $responsable = trim((string) ($data['responsable_entrega'] ?? ''));
        $motorista = trim((string) ($data['motorista'] ?? ''));
        $vehiculo = trim((string) ($data['vehiculo'] ?? ''));
        $placas = trim((string) ($data['placas'] ?? ''));
        $estado = trim((string) ($data['estado'] ?? 'Programada'));
        $comentarios = trim((string) ($data['comentarios'] ?? ''));
        $fechaProgramada = trim((string) ($data['fecha_programada'] ?? ''));
        $fechaEnRuta = trim((string) ($data['fecha_en_ruta'] ?? ''));
        $fechaEntregado = trim((string) ($data['fecha_entregado'] ?? ''));

        if ($idContrato <= 0 || $idInstitucion <= 0) {
            return ['success' => false, 'message' => 'Contrato o centro no válido.'];
        }
        if ($responsable === '' || $motorista === '' || $vehiculo === '' || $placas === '') {
            return ['success' => false, 'message' => 'Complete responsable, motorista, vehículo y placas.'];
        }
        $estados = ['Programada', 'En ruta', 'Entregado'];
        if (!in_array($estado, $estados, true)) {
            $estado = 'Programada';
        }
        $fechaProgramada = $fechaProgramada !== '' ? $fechaProgramada : null;
        $fechaEnRuta = $fechaEnRuta !== '' ? $fechaEnRuta : null;
        $fechaEntregado = $fechaEntregado !== '' ? $fechaEntregado : null;
        $comentarios = $comentarios !== '' ? $comentarios : null;

        try {
            $this->conn->beginTransaction();
            $qExist = $this->conn->prepare('SELECT id FROM rutas_entrega WHERE contrato_id = ? AND id_institucion = ?');
            $qExist->execute([$idContrato, $idInstitucion]);
            $exist = $qExist->fetch(PDO::FETCH_ASSOC);

            if ($exist && isset($exist['id'])) {
                $idRuta = (int) $exist['id'];
                $qUp = $this->conn->prepare(
                    'UPDATE rutas_entrega
                     SET responsable_entrega = ?, motorista = ?, vehiculo = ?, placas = ?, estado = ?,
                         fecha_programada = ?, fecha_en_ruta = ?, fecha_entregado = ?, comentarios = ?, fecha_actualizacion = NOW()
                     WHERE id = ?'
                );
                $qUp->execute([
                    $responsable, $motorista, $vehiculo, $placas, $estado,
                    $fechaProgramada, $fechaEnRuta, $fechaEntregado, $comentarios, $idRuta
                ]);
            } else {
                $qIns = $this->conn->prepare(
                    'INSERT INTO rutas_entrega
                     (contrato_id, id_institucion, responsable_entrega, motorista, vehiculo, placas, estado, fecha_programada, fecha_en_ruta, fecha_entregado, comentarios, fecha_creacion, fecha_actualizacion)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
                );
                $qIns->execute([
                    $idContrato, $idInstitucion, $responsable, $motorista, $vehiculo, $placas, $estado,
                    $fechaProgramada, $fechaEnRuta, $fechaEntregado, $comentarios
                ]);
                $idRuta = (int) $this->conn->lastInsertId();
            }

            $this->conn->commit();
            return ['success' => true, 'message' => 'Ruta guardada.', 'id_ruta' => $idRuta];
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log($e->getMessage());
            if (strpos($e->getMessage(), 'rutas_entrega') !== false) {
                return ['success' => false, 'message' => 'Ejecute el script SQL database/rutas_entrega.sql.'];
            }
            return ['success' => false, 'message' => 'No se pudo guardar la ruta: ' . $e->getMessage()];
        }
    }

    public function listarDocumentosRutaEntrega($idRuta) {
        $idRuta = (int) $idRuta;
        if ($idRuta <= 0) {
            return ['success' => false, 'message' => 'Ruta no válida.'];
        }
        try {
            $q = $this->conn->prepare('SELECT * FROM documentos_rutas_entrega WHERE ruta_id = ? ORDER BY fecha_subida DESC, id DESC');
            $q->execute([$idRuta]);
            return ['success' => true, 'data' => self::filasClavesMinusculas($q->fetchAll(PDO::FETCH_ASSOC))];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            if (strpos($e->getMessage(), 'documentos_rutas_entrega') !== false) {
                return ['success' => false, 'message' => 'Ejecute el script SQL database/documentos_rutas_entrega.sql.'];
            }
            return ['success' => false, 'message' => 'No se pudieron listar documentos: ' . $e->getMessage()];
        }
    }

    public function subirDocumentoRutaEntrega($idRuta, array $archivo, $comentario) {
        $idRuta = (int) $idRuta;
        if ($idRuta <= 0) {
            return ['success' => false, 'message' => 'Ruta no válida.'];
        }
        if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || empty($archivo['tmp_name'])) {
            return ['success' => false, 'message' => 'No se recibió el archivo.'];
        }
        $nombreOriginal = (string) ($archivo['nombre'] ?? basename((string) $archivo['tmp_name']));
        if (strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION)) !== 'pdf') {
            return ['success' => false, 'message' => 'Solo se permiten archivos PDF.'];
        }

        $rutaBase = '/uploads/rutas_entrega/';
        $dirFs = PROJECT_ROOT . '/public' . $rutaBase;
        if (!is_dir($dirFs) && !@mkdir($dirFs, 0755, true) && !is_dir($dirFs)) {
            return ['success' => false, 'message' => 'No se pudo crear la carpeta de archivos.'];
        }
        $nombreUnico = 'ruta_' . $idRuta . '_' . uniqid('', true) . '.pdf';
        $rutaRel = $rutaBase . $nombreUnico;
        $rutaDestino = PROJECT_ROOT . '/public' . $rutaRel;
        if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            return ['success' => false, 'message' => 'No se pudo guardar el archivo en el servidor.'];
        }
        $comentario = trim((string) $comentario);
        if ($comentario === '') {
            $comentario = 'Sin comentario';
        }

        try {
            $q = $this->conn->prepare(
                'INSERT INTO documentos_rutas_entrega (ruta_id, nombre_archivo, ruta_archivo, comentario, fecha_subida) VALUES (?, ?, ?, ?, NOW())'
            );
            $q->execute([$idRuta, $nombreOriginal, $rutaRel, $comentario]);
            $idDoc = (int) $this->conn->lastInsertId();

            $qRow = $this->conn->prepare('SELECT * FROM documentos_rutas_entrega WHERE id = ?');
            $qRow->execute([$idDoc]);
            $row = $qRow->fetch(PDO::FETCH_ASSOC);
            $doc = $row ? self::filasClavesMinusculas([$row])[0] : [
                'id' => $idDoc,
                'ruta_id' => $idRuta,
                'nombre_archivo' => $nombreOriginal,
                'ruta_archivo' => $rutaRel,
                'comentario' => $comentario,
                'fecha_subida' => date('Y-m-d H:i:s'),
            ];
            return ['success' => true, 'message' => 'Archivo PDF cargado.', 'documento' => $doc];
        } catch (PDOException $e) {
            @unlink($rutaDestino);
            error_log($e->getMessage());
            if (strpos($e->getMessage(), 'documentos_rutas_entrega') !== false) {
                return ['success' => false, 'message' => 'Ejecute el script SQL database/documentos_rutas_entrega.sql.'];
            }
            return ['success' => false, 'message' => 'No se pudo registrar el documento: ' . $e->getMessage()];
        }
    }
}
?>