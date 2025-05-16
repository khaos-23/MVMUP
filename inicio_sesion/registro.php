<?php
session_start();

require_once "../conexion.php";

function redirectWithMessage($success, $message) {
    header("Location: registro.html?register=" . ($success ? "success" : "error") . "&msg=" . urlencode($message));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['new-username'];
    if (empty($username)) {
        $username = $_POST['name'];
    }
    $nombre = $_POST['name'];
    $apellidos = $_POST['surname'];
    $email = $_POST['email'];
    $curso = $_POST['curso'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios (username, nombre, apellidos, email, curso, password, directory, activo)
            VALUES ('$username', '$nombre', '$apellidos', '$email', '$curso', '$password',2,1)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        redirectWithMessage(false, "Error en la preparación de la consulta: " . $conn->error);
    }

    if ($stmt->execute()) {
        redirectWithMessage(true, "Usuario registrado correctamente.");
    } else {
        redirectWithMessage(false, "Error al registrar el usuario: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
}
?>

