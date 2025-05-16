<?php
session_start();
require_once "../conexion.php";

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['file']) || empty($data['file'])) {
    echo json_encode(["success" => false, "message" => "Archivo no especificado."]);
    exit;
}

$file = $data['file']; 
$id = $_SESSION['id'];

$base_directory = "/mvmup_stor/$id";
$full_path = realpath($base_directory . '/' . ltrim($file, '/'));

if (!$full_path || strpos($full_path, realpath($base_directory)) !== 0) {
    echo json_encode(["success" => false, "message" => "Ruta inválida."]);
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
                if (!deleteFolderRecursively($itemPath, $conn, $userId)) {
                    return false;
                }
            } else {
                $stmt = $conn->prepare("DELETE FROM shared_files WHERE file_path = ? AND (owner_id = ? OR shared_with_id = ?)");
                $stmt->bind_param("sii", $itemPath, $userId, $userId);
                $stmt->execute();
                if (!unlink($itemPath)) {
                    return false;
                }
            }
        }

        $stmt = $conn->prepare("DELETE FROM shared_files WHERE file_path = ? AND (owner_id = ? OR shared_with_id = ?)");
        $stmt->bind_param("sii", $folder, $userId, $userId);
        $stmt->execute();

        return rmdir($folder);
    }

    if (deleteFolderRecursively($full_path, $conn, $id)) {
        echo json_encode(["success" => true, "message" => "Archivo o carpeta eliminados con éxito."]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al eliminar el archivo o carpeta."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Archivo o carpeta no encontrado."]);
}
$conn->close();
?>