<?php
session_start();
require_once "../conexion.php";

header('Content-Type: application/json');


$data = json_decode(file_get_contents('php://input'), true);


if (!isset($data['file']) || empty($data['file'])) {
    echo json_encode(['success' => false, 'error' => 'No se especificó el archivo o carpeta a eliminar.']);
    exit;
}

$file = $data['file']; 
$id = $_SESSION['id'];


$base_directory = "/mvmup_stor/$id";
$full_path = realpath($base_directory . '/' . ltrim($file, '/'));


if (!$full_path || strpos($full_path, realpath($base_directory)) !== 0) {
    echo json_encode(['success' => false, 'error' => 'El archivo especificado no es válido o no existe.']);
    exit;
}


$stmt = $conn->prepare("SELECT file_path, owner_id FROM shared_files WHERE (shared_with_id = ? OR owner_id = ?) AND file_path = ?");
$stmt->bind_param("iis", $id, $id, $full_path);
$stmt->execute();
$result = $stmt->get_result();

if (strpos($full_path, realpath($base_directory)) === 0 || $result->num_rows > 0) {
    
    function deleteFolderRecursively($folder, $conn, $userId) {
        if (!is_dir($folder)) {
           
            $stmt = $conn->prepare("DELETE FROM shared_files WHERE file_path = ? AND (owner_id = ? OR shared_with_id = ?)");
            $stmt->bind_param("sii", $folder, $userId, $userId);
            $stmt->execute();
            return unlink($folder);
        }

        $items = array_diff(scandir($folder), ['.', '..']);
        foreach ($items as $item) {
            $itemPath = $folder . DIRECTORY_SEPARATOR . $item;
            if (is_dir($itemPath)) {
                deleteFolderRecursively($itemPath, $conn, $userId);
            } else {
               
                $stmt = $conn->prepare("DELETE FROM shared_files WHERE file_path = ? AND (owner_id = ? OR shared_with_id = ?)");
                $stmt->bind_param("sii", $itemPath, $userId, $userId);
                $stmt->execute();
                unlink($itemPath);
            }
        }

       
        $stmt = $conn->prepare("DELETE FROM shared_files WHERE file_path = ? AND (owner_id = ? OR shared_with_id = ?)");
        $stmt->bind_param("sii", $folder, $userId, $userId);
        $stmt->execute();

        return rmdir($folder);
    }

    if (deleteFolderRecursively($full_path, $conn, $id)) {
        header("Location: /pagina_almacenamiento/index.html?message=Archivo o carpeta eliminados con éxito&type=success");
    } else {
        header("Location: /pagina_almacenamiento/index.html?message=No se pudo eliminar el archivo o carpeta&type=error");
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No tienes permiso para eliminar este archivo o carpeta.']);
}
?>