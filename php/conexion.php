<?php

// Datos de conexión a la base de datos (los de XAMPP)
$servidor = "localhost";
$usuario = "root";
$contrasena = "";
$base_datos = "tatto"; // <-- Asegúrate que aquí esté el nombre de tu BD

// Crear la conexión
$conexion = new mysqli($servidor, $usuario, $contrasena, $base_datos);

// Verificar si la conexión falló
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Opcional: Asegurar que la conexión use UTF-8 (para acentos y ñ)
$conexion->set_charset("utf8");

// NADA DE "ECHO" AQUÍ ABAJO
?>