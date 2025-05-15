<?php
session_start();

$id = $_SESSION['id'];
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['folder'])) {
    $base_directory = "/mvmup_stor/$id";
    $folder = realpath($base_directory) . '/' . trim($data['folder'], '/');

    if (strpos($folder, realpath($base_directory)) !== 0) {
        echo json_encode(['error' => 'Acceso no permitido']);
        exit;
    }

    if (!file_exists($folder)) {
        if (mkdir($folder, 0775, true)) {
            header("Location: /pagina_almacenamiento/index.html?message=Carpeta creada con éxito&type=success");
        } else {
            header("Location: /pagina_almacenamiento/index.html?message=No se pudo crear la carpeta&type=error");
        }
    } else {
        header("Location: /pagina_almacenamiento/index.html?message=La carpeta ya existe&type=error");
    }
}
?>