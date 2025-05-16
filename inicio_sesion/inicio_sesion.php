<?php
session_start();

require_once "../conexion.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT id, username, directory, password FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['username'] = $row['username'];
            $_SESSION['id'] = $row['id'];
            $_SESSION['directory'] = $row['directory'];
            $directory = $row['directory'];
            $id = $row['id'];

            $user_folder = "/mvmup_stor/$id";

            if (!file_exists($user_folder)) {
                if (!mkdir($user_folder, 0777, true)) {
                    exit;
                } 
            }

            header('Location: /index.html');
        } else {
            echo "Contraseña incorrecta.";
        }
    } else {
        echo "Usuario no encontrado.";
    }
    $stmt->close();
}
$conn->close();
?>
