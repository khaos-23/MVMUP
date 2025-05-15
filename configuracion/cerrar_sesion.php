<?php
session_start();

// Borrar variable sesion
$_SESSION = array();

// Destruir sesion
session_destroy();

echo "Sesion cerrada correctamente. Redirigiendo...";
?>