<?php
session_start();
require_once '../../includes/Usuario.php';

if (isset($_GET['id'])) {
    $id_usuario = $_GET['id'];
    
    $usuarioModel = new Usuario();
    $usuarioModel->eliminarUsuario($id_usuario);
    
    $_SESSION['mensaje'] = "Usuario eliminado exitosamente.";
    header("Location: listar_usuarios.php");
} else {
    die("ID de usuario no especificado.");
}
