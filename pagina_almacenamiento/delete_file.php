<?php
session_start();
require_once "../conexion.php";

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['file']) || empty($data['file'])) {
    header("Location: /pagina_almacenamiento/index.html?message=Archivo no especificado&type=error");
    exit;
}

$file = $data['file']; 
$id = $_SESSION['id'];

$base_directory = "/mvmup_stor/$id";
$full_path = realpath($base_directory . '/' . ltrim($file, '/'));

if (!$full_path || strpos($full_path, realpath($base_directory)) !== 0) {
    header("Location: /pagina_almacenamiento/index.html?message=Ruta inválida&type=error");
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
            if (unlink($folder)) {
                header("Location: /pagina_almacenamiento/index.html?message=Archivo eliminado con éxito&type=success");
            } else {
                header("Location: /pagina_almacenamiento/index.html?message=Error al eliminar el archivo&type=error");
            }
            return;
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
                if (!unlink($itemPath)) {
                    header("Location: /pagina_almacenamiento/index.html?message=Error al eliminar un archivo en la carpeta&type=error");
                    return;
                }
            }
        }

        $stmt = $conn->prepare("DELETE FROM shared_files WHERE file_path = ? AND (owner_id = ? OR shared_with_id = ?)");
        $stmt->bind_param("sii", $folder, $userId, $userId);
        $stmt->execute();

        if (rmdir($folder)) {
            header("Location: /pagina_almacenamiento/index.html?message=Carpeta eliminada con éxito&type=success");
        } else {
            header("Location: /pagina_almacenamiento/index.html?message=Error al eliminar la carpeta&type=error");
        }
    }

    if (deleteFolderRecursively($full_path, $conn, $id)) {
        // Mensaje ya manejado dentro de la función
    } else {
        header("Location: /pagina_almacenamiento/index.html?message=Error desconocido&type=error");
    }
} else {
    header("Location: /pagina_almacenamiento/index.html?message=Archivo o carpeta no encontrado&type=error");
}
?>