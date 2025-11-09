<?php
session_start(); // Inicia la sesión para poderla destruir

// Destruye todas las variables de sesión
session_unset();

// Destruye la sesión
session_destroy();

// Redirige al inicio (o al login, como prefieras)
header('Location: ../index.php');
exit;
?>