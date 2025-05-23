<?php
session_start();
require_once "../conexion.php"; 

$id = $_SESSION['id'];
$base_directory = "/mvmup_stor/$id";
$path = isset($_GET['path']) ? $_GET['path'] : '';
$directory = realpath($base_directory . '/' . $path);

if (strpos($directory, realpath($base_directory)) !== 0) {
    echo json_encode(['error' => 'Acceso no permitido']);
    exit;
}


if (!is_dir($base_directory)) {
    echo json_encode(['error' => 'La carpeta del usuario no existe']);
    exit;
}


$ownFiles = array_diff(scandir($directory), array('.', '..'));
$result = [];

foreach ($ownFiles as $file) {
    $file_path = $directory . '/' . $file;

  
    $stmt = $conn->prepare("SELECT file_path FROM shared_files WHERE file_path = ? AND owner_id = ?");
    $stmt->bind_param("si", $file_path, $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
  
        $result[] = [
            'name' => $file,
            'is_dir' => is_dir($file_path),
            'path' => $path . '/' . $file,
            'shared' => true 
        ];
    } else {
        
        $result[] = [
            'name' => $file,
            'is_dir' => is_dir($file_path),
            'path' => $path . '/' . $file,
            'shared' => false
        ];
    }

    $stmt->close();
}


echo json_encode($result);

$conn->close();
?>