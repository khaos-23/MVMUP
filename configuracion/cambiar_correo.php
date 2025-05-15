<?php
session_start();

require_once "../conexion.php";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_email = $_POST['current-email'];
    $new_email = $_POST['new-email'];

    // Obtener ID de usuario 
    $user_id = $_SESSION['user_id'];

    // Verificar correo 
    $sql = "SELECT email FROM usuarios WHERE id = $user_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stored_email = $row['email'];

        if ($current_email === $stored_email) {
            // Actualizar correo
            $update_sql = "UPDATE usuarios SET email = '$new_email' WHERE id = $user_id";
            if ($conn->query($update_sql) === TRUE) {
                echo "Correo actualizado correctamente.";
            } else {
                echo "Error al actualizar el correo: " . $conn->error;
            }
        } else {
            echo "Correo actual incorrecto.";
        }
    } else {
        echo "Usuario no encontrado.";
    }

    $conn->close();
}
?>