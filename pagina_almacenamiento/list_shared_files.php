<?php
session_start();
require_once "../conexion.php";

if (!isset($_SESSION['id'])) {
    die(json_encode(["error" => "Usuario no autenticado."]));
}

$user_id = $_SESSION['id'];
$path = isset($_GET['path']) ? $_GET['path'] : '';

if (empty($path)) {
    die(json_encode(["error" => "Ruta no especificada."]));
}

$base_directory = realpath("/mvmup_stor");
$full_path = realpath($base_directory . '/' . ltrim($path, '/'));

// Validar que la ruta sea válida y esté dentro del directorio base
if (!$full_path || strpos($full_path, $base_directory) !== 0) {
    die(json_encode(["error" => "No tienes permiso para acceder a esta carpeta."]));
}

$stmt = $conn->prepare("SELECT file_path FROM shared_files WHERE shared_with_id = ? AND file_path = ?");
$stmt->bind_param("is", $user_id, $full_path);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die(json_encode(["error" => "No tienes permiso para acceder a esta carpeta."]));
}

if (is_dir($full_path)) {
    $items = array_diff(scandir($full_path), ['.', '..']);
    $files = [];

    foreach ($items as $item) {
        $item_path = $full_path . DIRECTORY_SEPARATOR . $item;
        $files[] = [
            "name" => $item,
            "path" => $item_path,
            "is_dir" => is_dir($item_path)
        ];
    }

    echo json_encode($files);
} else {
    echo json_encode(["error" => "La ruta especificada no es una carpeta válida."]);
}

$conn->close();
?>
