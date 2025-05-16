<?php
session_start();

header('Content-Type: application/json');

$id = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fileToUpload'])) {
    $base_directory = realpath("/mvmup_stor/$id");
    $path = isset($_POST['path']) ? $_POST['path'] : '';
    $target_dir = $base_directory . '/' . trim($path, '/');

    // Verificar que la ruta sea válida
    if (strpos($target_dir, $base_directory) !== 0) {
        echo json_encode(["success" => false, "message" => "Ruta inválida."]);
        exit;
    }

    // Crear el archivo de destino
    $target_file = $target_dir . '/' . basename($_FILES["fileToUpload"]["name"]);

    // Verificar si el archivo ya existe
    if (file_exists($target_file)) {
        echo json_encode(["success" => false, "message" => "El archivo ya existe."]);
        exit;
    }

    // Verificar el tamaño del archivo (limite: 50MB)
    if ($_FILES["fileToUpload"]["size"] > 50 * 1024 * 1024) {
        echo json_encode(["success" => false, "message" => "El archivo excede el tamaño permitido (50MB)."]);
        exit;
    }

    // Subir el archivo
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        echo json_encode(["success" => true, "message" => "Archivo subido con éxito."]);
        exit;
    } else {
        echo json_encode(["success" => false, "message" => "Error al subir el archivo."]);
        exit;
    }
}
echo json_encode(["success" => false, "message" => "Solicitud inválida."]);
?>