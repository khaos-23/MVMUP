<?php
session_start();

require_once "../conexion.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current-password'];
    $new_password = $_POST['new-password'];
    $confirm_new_password = $_POST['confirm-new-password'];

    // Verificar contraseñas 
    if ($new_password !== $confirm_new_password) {
        echo "Las nuevas contraseñas no coinciden.";
        exit();
    }

    // Obtener ID usuario
    $id = $_SESSION['id'];

    // Obtener contraseña actual usuario
    $sql = "SELECT password FROM usuarios WHERE id = $user_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stored_password = $row['password'];

        // Verificar contraseña actual
        if (password_verify($current_password, $stored_password)) {
            // Encriptar contraseña
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Actualizar contraseña
            $update_sql = "UPDATE usuarios SET password = '$hashed_new_password' WHERE id = $user_id";
            if ($conn->query($update_sql) === TRUE) {
                echo "Contraseña actualizada correctamente.";
            } else {
                echo "Error al actualizar la contraseña: " . $conn->error;
            }
        } else {
            echo "Contraseña actual incorrecta.";
        }
    } else {
        echo "Usuario no encontrado.";
    }

    $conn->close();
}
?>