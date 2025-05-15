<?php
session_start();

header('Content-Type: application/json');

$id = $_SESSION['id'];
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['folder'])) {
    $base_directory = "/mvmup_stor/$id";
    $folder = realpath($base_directory) . '/' . trim($data['folder'], '/');

    if (strpos($folder, realpath($base_directory)) !== 0) {
        
        exit;
    }

    if (!file_exists($folder)) {
        if (mkdir($folder, 0775, true)) {
           
        } else {
            
        }
    } else {
        
    }
} else {
    
}
?>