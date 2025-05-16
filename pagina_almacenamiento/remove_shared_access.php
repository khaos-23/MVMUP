<?php
session_start();
require_once "../conexion.php";

header('Content-Type: application/json');

$id = $_SESSION['id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['file']) || empty($data['file'])) {
    echo json_encode(['success' => false, 'error' => 'No se especificó el archivo o carpeta.']);
    exit;
}

$file = $data['file'];
$full_path = realpath($file);

if (!$full_path) {
    echo json_encode(['success' => false, 'error' => 'Ruta inválida.']);
    exit;
}

// Eliminar solo el registro de acceso para este usuario
$stmt = $conn->prepare("DELETE FROM shared_files WHERE shared_with_id = ? AND file_path = ?");
$stmt->bind_param("is", $id, $full_path);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'No se pudo eliminar el acceso.']);
}

$stmt->close();
$conn->close();
?>
