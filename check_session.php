<?php
session_start();

$response = [
    'loggedIn' => false,
    'username' => '',
    'redirect' => false 
];

if (isset($_SESSION['id'])) {
    $response['loggedIn'] = true;
    $response['username'] = $_SESSION['username'];
    
} else {
    $response['redirect'] = true; 
}

header('Content-Type: application/json'); 
echo json_encode($response);
?>