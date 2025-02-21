<?php
require_once 'Database.php';

class Entrega extends Database
{
    public function crearEntrega($idContrato, $idInstitucion, $fechaEntrega, $estado, $firmaResponsable, $comentarios, $usuarioCreador)
    {
        try {
            $sql = "INSERT INTO entregas (id_contrato, id_institucion, fecha_entrega, estado, firma_responsable, comentarios, usuario_creador) 
                    VALUES (:idContrato, :idInstitucion, :fechaEntrega, :estado, :firmaResponsable, :comentarios, :usuarioCreador)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':idContrato', $idContrato, PDO::PARAM_INT);
            $stmt->bindParam(':idInstitucion', $idInstitucion, PDO::PARAM_INT);
            $stmt->bindParam(':fechaEntrega', $fechaEntrega);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':firmaResponsable', $firmaResponsable);
            $stmt->bindParam(':comentarios', $comentarios);
            $stmt->bindParam(':usuarioCreador', $usuarioCreador);
            $stmt->execute();
            return $this->conn->lastInsertId(); // Retornar el ID generado
        } catch (PDOException $e) {
            print_r($e);
            return false;
        }
    }
    

    public function agregarDetalle($idEntrega, $idEquipo, $cantidad, $precio, $comentario)
    {
        try {
            $sql = "INSERT INTO entregas_detalle (id_entrega, id_equipo, cantidad, precio, comentario) 
                    VALUES (:idEntrega, :idEquipo, :cantidad, :precio, :comentario)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':idEntrega', $idEntrega, PDO::PARAM_INT);
            $stmt->bindParam(':idEquipo', $idEquipo, PDO::PARAM_INT);
            $stmt->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
            $stmt->bindParam(':precio', $precio);
            $stmt->bindParam(':comentario', $comentario);
            return $stmt->execute();
        } catch (PDOException $e) {
            print_r($e);
            return false;
        }
    }

    public function listarEntregas()
    {
        try {
            $sql = "SELECT e.id_entrega, e.fecha_entrega, e.estado, e.firma_responsable, e.comentarios, 
                           i.nombre_ce, i.codigo_infraestructura,c.nombre_contrato 
                    FROM entregas e 
                    JOIN centros_educativos i ON e.id_institucion = i.centro_id
                    JOIN contratos c ON e.id_contrato = c.id;";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    public function obtenerPorId($idEntrega){
        try {
            $sql = "SELECT 
                        e.id_entrega, 
                        e.id_contrato, 
                        e.id_institucion, 
                        e.fecha_entrega, 
                        e.estado, 
                        e.comentarios, 
                        e.firma_responsable 
                    FROM entregas e
                    WHERE e.id_entrega = :idEntrega";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':idEntrega', $idEntrega, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }
    public function obtenerCompletoPorId($idEntrega){
        try {
            $sql = "SELECT e.id_entrega, e.fecha_entrega, e.estado,
                         e.firma_responsable, 
                           e.comentarios, 
                           i.nombre_ce, i.codigo_infraestructura,
                           c.numero_contrato,c.nombre_contrato 

                    FROM entregas e 
                    JOIN centros_educativos i ON e.id_institucion = i.centro_id
                    JOIN contratos c ON e.id_contrato = c.id
                    WHERE e.id_entrega = :idEntrega";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':idEntrega', $idEntrega, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function listarDetalles($idEntrega)
    {
        try {
            $sql = "SELECT d.id_detalle, eq.nombre_equipo, d.cantidad, d.precio, d.comentario 
                    FROM entregas_detalle d 
                    JOIN equipos eq ON d.id_equipo = eq.id_equipo 
                    WHERE d.id_entrega = :idEntrega";
            //echo $sql;
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':idEntrega', $idEntrega, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    public function listarContratos()
    {
        try {
            $sql = "SELECT id, nombre_contrato FROM contratos";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function listarInstituciones()
    {
        try {
            $sql = "SELECT centro_id, nombre_ce FROM centros_educativos";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function listarEquipos()
    {
        try {
            $sql = "SELECT id_equipo, nombre_equipo FROM equipos";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    public function eliminarDetalleEntrega($id) {
        $sql = "DELETE FROM entregas_detalle WHERE id_detalle = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
}
