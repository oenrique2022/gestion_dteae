<?php
require_once 'Database.php';

class Contrato extends Database
{
    public function crearContrato($nombreContrato, $proveedorId, $fuenteFinanciamientoId, $nombreEncargado, $comentarios, $usuarioCreador,$numeroContrato, $fechaInicio, $fechaFinal)
    {
        try {
            $sql = "INSERT INTO contratos 
                    (nombre_contrato, proveedor_id, fuente_financiamiento_id, nombre_encargado, comentarios, usuario_creador,numero_contrato,fecha_inicio,fecha_fin) 
                    VALUES (:nombreContrato, :proveedorId, :fuenteFinanciamientoId, :nombreEncargado, :comentarios, :usuarioCreador, :numeroContrato, :fechaInicio, :fechaFin)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':nombreContrato', $nombreContrato);
            $stmt->bindParam(':proveedorId', $proveedorId, PDO::PARAM_INT);
            $stmt->bindParam(':fuenteFinanciamientoId', $fuenteFinanciamientoId, PDO::PARAM_INT);
            $stmt->bindParam(':nombreEncargado', $nombreEncargado);
            $stmt->bindParam(':comentarios', $comentarios);
            $stmt->bindParam(':usuarioCreador', $usuarioCreador);
            $stmt->bindParam(':numeroContrato', $numeroContrato);
            $stmt->bindParam(':fechaInicio', $fechaInicio);
            $stmt->bindParam(':fechaFin', $fechaFinal);
            return $stmt->execute();
        } catch (PDOException $e) {
            print_r($e);
            return false;
        }
    }

    public function listarContratos()
    {
        try {
            $sql = "SELECT c.id, c.nombre_contrato,c.nombre_contrato, p.nombre_proveedor AS proveedor, f.nombre AS fuente_financiamiento, 
                           c.nombre_encargado, c.comentarios, c.fecha_creacion 
                    FROM contratos c
                    LEFT JOIN proveedores p ON c.proveedor_id = p.id_proveedor
                    LEFT JOIN fuentes_financiamiento f ON c.fuente_financiamiento_id = f.id";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerPorId($id)
    {
        try {
            $sql = "SELECT c.id, c.nombre_contrato,c.numero_contrato, c.proveedor_id, c.fuente_financiamiento_id, c.fecha_inicio, c.fecha_fin,
                           c.nombre_encargado, c.comentarios, c.fecha_creacion, c.fecha_modificacion 
                    FROM contratos c
                    WHERE c.id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function actualizarContrato(
        $id,
        $nombreContrato,
        $proveedorId,
        $fuenteFinanciamientoId,
        $nombreEncargado,
        $comentarios,
        $numeroContrato,
        $fechaInicio,
        $fechaFinal
    ) {
        try {
            $sql = "UPDATE contratos SET 
                        nombre_contrato = :nombreContrato,
                        proveedor_id = :proveedorId,
                        fuente_financiamiento_id = :fuenteFinanciamientoId,
                        nombre_encargado = :nombreEncargado,
                        comentarios = :comentarios,
                        numero_contrato = :numeroContrato,
                        fecha_inicio = :fechaInicio,
                        fecha_fin = :fechaFinal,
                        fecha_modificacion = NOW()
                    WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':nombreContrato', $nombreContrato);
            $stmt->bindParam(':proveedorId', $proveedorId);
            $stmt->bindParam(':fuenteFinanciamientoId', $fuenteFinanciamientoId);
            $stmt->bindParam(':nombreEncargado', $nombreEncargado);
            $stmt->bindParam(':comentarios', $comentarios);
            $stmt->bindParam(':numeroContrato', $numeroContrato);
            $stmt->bindParam(':fechaInicio', $fechaInicio);
            $stmt->bindParam(':fechaFinal', $fechaFinal);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function guardarDocumentos($contratoId, $documentos)
    {
        foreach ($documentos as $documento) {
            $sql = "INSERT INTO documentos_contratos (contrato_id, nombre_documento) VALUES (:contratoId, :nombreDocumento)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':contratoId', $contratoId, PDO::PARAM_INT);
            $stmt->bindParam(':nombreDocumento', $documento);
            $stmt->execute();
        }
    }
    

    public function eliminarContrato($id)
    {
        try {
            $sql = "DELETE FROM contratos WHERE id_contrato = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    ///
    public function agregarEquipo($contratoId, $equipoId, $cantidad, $marca, $precio)
{
    try {
        $sql = "INSERT INTO contratos_equipos (contrato_id, equipo_id, cantidad, marca, precio) 
                VALUES (:contratoId, :equipoId, :cantidad, :marca, :precio)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':contratoId', $contratoId);
        $stmt->bindParam(':equipoId', $equipoId);
        $stmt->bindParam(':cantidad', $cantidad);
        $stmt->bindParam(':marca', $marca);
        $stmt->bindParam(':precio', $precio);
        return $stmt->execute();
    } catch (PDOException $e) {
        return false;
    }
}

public function listarEquiposAsociados($contratoId)
{
    try {
        $sql = "SELECT ce.id, e.nombre_equipo, ce.cantidad, ce.marca, ce.precio, 
                       (ce.cantidad * ce.precio) AS total
                FROM contratos_equipos ce
                JOIN equipos e ON ce.equipo_id = e.id_equipo
                WHERE ce.contrato_id = :contratoId";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':contratoId', $contratoId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

}
