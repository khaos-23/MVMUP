<?php
session_start();
require_once "../conexion.php";

$id = $_SESSION['id'];
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['file'], $data['recipient'])) {
    $recipientEmail = $data['recipient'];
    $file = $data['file']; 

    
    $base_directory = "/mvmup_stor/$id";
    $full_path = realpath($base_directory . '/' . ltrim($file, '/'));

    
    if (!$full_path || strpos($full_path, realpath($base_directory)) !== 0) {
        echo json_encode(['message' => 'El archivo o carpeta especificado no es válido o no existe.']);
        exit;
    }

    
    $stmt = $conn->prepare("SELECT file_path FROM shared_files WHERE (shared_with_id = ? OR owner_id = ?) AND file_path = ?");
    $stmt->bind_param("iis", $id, $id, $full_path);
    $stmt->execute();
    $result = $stmt->get_result();

    if (strpos($full_path, realpath($base_directory)) === 0 || $result->num_rows > 0) {
        
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $recipientEmail);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo json_encode(['message' => 'El destinatario no existe']);
            exit;
        }

        $recipientRow = $result->fetch_assoc();
        $recipientId = $recipientRow['id'];

        
        $stmt = $conn->prepare("INSERT INTO shared_files (owner_id, shared_with_id, file_path) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $id, $recipientId, $full_path);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Archivo o carpeta compartido correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al compartir el archivo o carpeta.']);
        }
    } else {
        echo json_encode(['message' => 'No tienes permiso para compartir este archivo o carpeta']);
    }
}

$conn->close();
?>