<?php
session_start();
require_once "../conexion.php";

$id = $_SESSION['id'];

if (isset($_GET['file'])) {
    // Reconstruir la ruta absoluta desde la ruta relativa
    $relative_path = $_GET['file'];
    $base_directory = realpath("/mvmup_stor");
    $file = realpath($base_directory . '/' . ltrim($relative_path, '/'));

    $stmt = $conn->prepare("
        SELECT file_path 
        FROM shared_files 
        WHERE shared_with_id = ? 
        AND (file_path = ? OR ? LIKE CONCAT(file_path, '/%'))
    ");
    $stmt->bind_param("iss", $id, $file, $file);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0 && file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    } else {
        http_response_code(403);
        echo "No tienes permiso para descargar este archivo.";
        exit;
    }
} else {
    http_response_code(400);
    echo "Archivo no especificado.";
    exit;
}
?>