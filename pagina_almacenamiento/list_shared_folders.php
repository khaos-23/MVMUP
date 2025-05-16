<?php
session_start();
require_once "../conexion.php";

if (!isset($_SESSION['id'])) {
    die(json_encode(["error" => "Usuario no autenticado."]));
}

$user_id = $_SESSION['id'];


$stmt = $conn->prepare("SELECT file_path FROM shared_files WHERE shared_with_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$shared_items = [];
while ($row = $result->fetch_assoc()) {
    $item_path = $row['file_path'];
    $shared_items[] = [
        "name" => basename($item_path),
        "path" => $item_path,
        "is_dir" => is_dir($item_path),
        "shared_id" => $user_id // Añadido para identificar el usuario que tiene acceso
    ];
}

header('Content-Type: application/json');
if (empty($shared_items)) {
    echo json_encode(["error" => "No se encontraron archivos o carpetas compartidos."]);
} else {
    echo json_encode($shared_items);
}

$conn->close();
?>