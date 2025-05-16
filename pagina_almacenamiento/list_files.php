<?php
session_start();
require_once "../conexion.php"; 

if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

$id = $_SESSION['id'];
$base_directory = realpath("/mvmup_stor/$id");
$path = isset($_GET['path']) ? $_GET['path'] : '';
$directory = $base_directory;

// Si se solicita una subcarpeta, calcular la ruta real
if ($path !== '') {
    $requested = $base_directory . '/' . ltrim($path, '/');
    $real_requested = realpath($requested);
    if ($real_requested && strpos($real_requested, $base_directory) === 0 && is_dir($real_requested)) {
        $directory = $real_requested;
    } else {
        echo json_encode(['error' => 'Acceso no permitido o carpeta no existe']);
        exit;
    }
}

if (!is_dir($directory)) {
    echo json_encode(['error' => 'La carpeta del usuario no existe']);
    exit;
}

$ownFiles = array_diff(scandir($directory), array('.', '..'));
$result = [];

foreach ($ownFiles as $file) {
    $file_path = $directory . '/' . $file;
    // Calcular la ruta relativa desde la base del usuario
    $relative_path = ltrim(str_replace($base_directory, '', $file_path), '/');

    $stmt = $conn->prepare("SELECT file_path FROM shared_files WHERE file_path = ? AND owner_id = ?");
    $stmt->bind_param("si", $file_path, $id);
    $stmt->execute();
    $stmt->store_result();

    $result[] = [
        'name' => $file,
        'is_dir' => is_dir($file_path),
        'path' => $relative_path,
        'shared' => $stmt->num_rows > 0
    ];

    $stmt->close();
}

echo json_encode($result);

$conn->close();
?>