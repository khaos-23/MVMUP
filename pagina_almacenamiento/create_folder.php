<?php
session_start();

header('Content-Type: application/json');

$id = $_SESSION['id'];
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['folder'])) {
    $base_directory = "/mvmup_stor/$id";
    $folder = realpath($base_directory) . '/' . trim($data['folder'], '/');

    if (strpos($folder, realpath($base_directory)) !== 0) {
        echo json_encode(['success' => false, 'message' => 'Acceso no permitido.']);
        exit;
    }

    if (!file_exists($folder)) {
        if (mkdir($folder, 0775, true)) {
            echo json_encode(['success' => true, 'message' => 'Carpeta creada con éxito.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo crear la carpeta.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'La carpeta ya existe.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Nombre de carpeta no especificado.']);
}
?>