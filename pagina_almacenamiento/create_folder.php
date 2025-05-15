<?php
session_start();

header('Content-Type: application/json');

$id = $_SESSION['id'];
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['folder'])) {
    $base_directory = "/mvmup_stor/$id";
    $folder = realpath($base_directory) . '/' . trim($data['folder'], '/');

    if (strpos($folder, realpath($base_directory)) !== 0) {
        header("Location: /pagina_almacenamiento/index.html?message=Acceso no permitido.&type=error");
        exit;
    }

    if (!file_exists($folder)) {
        mkdir ($folder, 0777, true);
        if (true) {
            header("Location: /pagina_almacenamiento/index.html?message=Carpeta creada con éxito.&type=success");  
           
        } else {
            header("Location: /pagina_almacenamiento/index.html?message=No se pudo crear la carpeta&type=error");
        }
    } else {
        header("Location: /pagina_almacenamiento/index.html?message=La carpeta ya existe.&type=error");
    }
} else {
    header("Location: /pagina_almacenamiento/index.html?message=Nombre de carpeta no especificado.&type=error");
}
?>