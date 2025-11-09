<?php

// Datos de conexión a la base de datos (los de XAMPP)
$servidor = "localhost";  
$usuario = "root";        
$contrasena = "";         
$base_datos = "tatto"; 

// Crear la conexión
$conexion = new mysqli($servidor, $usuario, $contrasena, $base_datos);


if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}


$conexion->set_charset("utf8");

echo 'conexion exitosa'


?>