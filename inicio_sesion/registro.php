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

    // Verificar si el correo ya existe
    $check_sql = "SELECT id FROM usuarios WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('s', $email);
    $check_stmt->execute();
    $check_stmt->store_result();
    if ($check_stmt->num_rows > 0) {
        redirectWithMessage(false, "El correo ya ha sido registrado.");
    }
    $check_stmt->close();

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

