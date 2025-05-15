<?php
session_start();

$id = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fileToUpload'])) {
    $base_directory = "/mvmup_stor/$id";
    $path = isset($_POST['path']) ? $_POST['path'] : '';
    $target_dir = realpath($base_directory) . '/' . trim($path, '/');
    
    // Verificar que la ruta sea válida
    if (strpos($target_dir, realpath($base_directory)) !== 0) {
        header("Location: /pagina_almacenamiento/index.html?message=Ruta inválida&type=error");
        exit;
    }

    // Crear el archivo de destino
    $target_file = $target_dir . '/' . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;

    // Verificar si el archivo ya existe
    if (file_exists($target_file)) {
        header("Location: /pagina_almacenamiento/index.html?message=El archivo ya existe&type=error");
        exit;
    }

    // Verificar el tamaño del archivo (limite: 50MB)
    if ($_FILES["fileToUpload"]["size"] > 50 * 1024 * 1024) {
        header("Location: /pagina_almacenamiento/index.html?message=El archivo excede el tamaño permitido&type=error");
        exit;
    }

    // Subir el archivo
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        header("Location: /pagina_almacenamiento/index.html?message=Archivo subido con éxito&type=success");
        exit;
    } else {
        header("Location: /pagina_almacenamiento/index.html?message=Error al subir el archivo&type=error");
        exit;
    }
    exit;
}
?>