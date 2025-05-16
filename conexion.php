<?php

require_once('config.php');

// Crear conexion
$conn = new mysqli($servername, $username, $password, $dbname);
// Verificar conexion
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
