<?php
require_once __DIR__ . '/Database.php';

class Contrato {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
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
            $sql2 = "INSERT INTO contratos_equipos (contrato_id, equipo_id, cantidad, precio, marca) VALUES (?, ?, ?, ?, ?)";
            $stmt2 = $this->conn->prepare($sql2);
            foreach ($detallesEquipos as $equipo) {
                $stmt2->execute([$idContrato, $equipo['id'], $equipo['cantidad'], $equipo['precio'], $equipo['marca']]);
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
            return ['success' => true, 'message' => 'Contrato creado exitosamente con ID: ' . $idContrato];
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
            $contrato['generales'] = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if(!$contrato['generales']) return null;
    
            // 2. Obtener equipos asociados (sin cambios)
            $stmt = $this->conn->prepare("SELECT * FROM contratos_equipos WHERE contrato_id = :id");
            $stmt->execute([':id' => $id]);
            $contrato['equipos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // 3. Obtener documentos asociados (sin cambios)
            $stmt = $this->conn->prepare("SELECT * FROM documentos_contratos WHERE contrato_id = :id");
            $stmt->execute([':id' => $id]);
            $contrato['documentos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // 4. Entregas y detalles (una consulta para detalles: evita N+1 si hay muchas entregas)
            $stmt_entregas = $this->conn->prepare("SELECT * FROM entregas WHERE id_contrato = :id ORDER BY id_entrega");
            $stmt_entregas->execute([':id' => $id]);
            $filas_entregas = $stmt_entregas->fetchAll(PDO::FETCH_ASSOC);

            if ($filas_entregas === []) {
                $contrato['entregas'] = [];
            } else {
                $ids_entrega = array_column($filas_entregas, 'id_entrega');
                $placeholders = implode(',', array_fill(0, count($ids_entrega), '?'));
                $stmt_detalles = $this->conn->prepare(
                    "SELECT * FROM entregas_detalle WHERE id_entrega IN ($placeholders) ORDER BY id_entrega, id_equipo"
                );
                $stmt_detalles->execute($ids_entrega);
                $todas_lineas = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);

                $detalle_por_entrega = [];
                foreach ($todas_lineas as $linea) {
                    $ie = $linea['id_entrega'];
                    if (!isset($detalle_por_entrega[$ie])) {
                        $detalle_por_entrega[$ie] = [];
                    }
                    $detalle_por_entrega[$ie][] = $linea;
                }

                foreach ($filas_entregas as &$fila_entrega) {
                    $ie = $fila_entrega['id_entrega'];
                    $fila_entrega['detalle'] = $detalle_por_entrega[$ie] ?? [];
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
            $sql2 = "INSERT INTO contratos_equipos (contrato_id, equipo_id, cantidad, precio, marca) VALUES (?, ?, ?, ?, ?)";
            $stmt2 = $this->conn->prepare($sql2);
            foreach ($detallesEquipos as $equipo) {
                $stmt2->execute([$idContrato, $equipo['id'], $equipo['cantidad'], $equipo['precio'], $equipo['marca']]);
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
            return ['success' => true, 'message' => 'Contrato actualizado exitosamente.'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error al actualizar el contrato: ' . $e->getMessage()];
        }
    }
}
?>