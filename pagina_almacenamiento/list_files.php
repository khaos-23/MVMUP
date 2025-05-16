<?php
session_start();
require_once "../conexion.php"; 

if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'Usuario no autenticado.']);
    exit;
}

$id = $_SESSION['id'];
$base_directory = realpath("/mvmup_stor/$id");
$path = isset($_GET['path']) ? $_GET['path'] : '';
$directory = realpath($base_directory . '/' . ltrim($path, '/'));

if (!$directory || strpos($directory, $base_directory) !== 0) {
    echo json_encode(['error' => 'Acceso no permitido']);
    exit;
}

if (!is_dir($directory)) {
    echo json_encode(['error' => 'La carpeta del usuario no existe']);
    exit;
}

$ownFiles = array_diff(scandir($directory), array('.', '..'));
$result = [];

foreach ($ownFiles as $file) {
    $file_path = $directory . '/' . $file;
    // Calcula la ruta relativa correctamente
    $relative_path = ltrim(($path ? $path . '/' : '') . $file, '/');

    $stmt = $conn->prepare("SELECT file_path FROM shared_files WHERE file_path = ? AND owner_id = ?");
    $stmt->bind_param("si", $file_path, $id);
    $stmt->execute();
    $stmt->store_result();

    $result[] = [
        'name' => $file,
        'is_dir' => is_dir($file_path),
        'path' => $relative_path, // <-- siempre relativo
        'shared' => $stmt->num_rows > 0
    ];

    $stmt->close();
}

echo json_encode($result);

$conn->close();
?>