<?php
session_start();

header('Content-Type: application/json');

$id = $_SESSION['id'];
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['folder'])) {
    $base_directory = "/mvmup_stor/$id";
    // Asegúrate de que el directorio base existe
    if (!file_exists($base_directory)) {
        if (!mkdir($base_directory, 0770, true)) {
            echo json_encode(["success" => false, "message" => "No se pudo crear el directorio base."]);
            exit;
        }
    }
    // Construye la ruta destino sin usar realpath (la carpeta puede no existir aún)
    $folder = $base_directory . '/' . trim($data['folder'], '/');
    // Normaliza la ruta para evitar path traversal
    $folder = preg_replace('#/+#','/', $folder);

    // Verifica que la ruta esté dentro del directorio base
    if (strpos(realpath(dirname($folder)), realpath($base_directory)) !== 0) {
        echo json_encode(["success" => false, "message" => "Acceso no permitido."]);
        exit;
    }

    if (!file_exists($folder)) {
        if (mkdir($folder, 0770, true)) {
            echo json_encode(["success" => true, "message" => "Carpeta creada con éxito."]);
        } else {
            echo json_encode(["success" => false, "message" => "No se pudo crear la carpeta."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "La carpeta ya existe."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Nombre de carpeta no especificado."]);
}
?>