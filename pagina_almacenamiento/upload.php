<?php
session_start();

$id = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fileToUpload'])) {
    $base_directory = "/mvmup_stor/$id";
    $path = isset($_POST['path']) ? $_POST['path'] : '';
    $target_dir = realpath($base_directory) . '/' . trim($path, '/');
    
    // Verificar que la ruta sea válida
    if (strpos($target_dir, realpath($base_directory)) !== 0) {
        
        exit;
    }

    // Crear el archivo de destino
    $target_file = $target_dir . '/' . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;

    // Verificar si el archivo ya existe
    if (file_exists($target_file)) {
        
        exit;
    }

    // Verificar el tamaño del archivo (limite: 50MB)
    if ($_FILES["fileToUpload"]["size"] > 50 * 1024 * 1024) {
        
        exit;
    }

    // Subir el archivo
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        
    } else {
        
    }
}
?>