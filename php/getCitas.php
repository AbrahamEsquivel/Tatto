<?php

include 'conexion.php';

// Definir la consulta 
$sql = "SELECT
            c.id AS id_cita,
            c.fecha_hora,
            p_cliente.nombre AS cliente_nombre,
            p_cliente.apellido AS cliente_apellido,
            a.nombre_artistico AS artista_nombre,
            t.descripcion AS tatuaje_descripcion,
            ec.nombre AS estado_cita
        FROM CITA AS c
        JOIN CLIENTE AS cl ON c.id_cliente = cl.id
        JOIN PERSONA AS p_cliente ON cl.id_persona = p_cliente.id
        JOIN ARTISTA AS a ON c.id_artista = a.id
        JOIN TATUAJE AS t ON c.id_tatuaje = t.id
        JOIN ESTADO_CITA AS ec ON c.id_estado_cita = ec.id
        ORDER BY c.fecha_hora ASC";

// Ejecutar la consulta
$resultado = $conexion->query($sql);

// Preparar el array para guardar los datos
$citas = array();

// Recorrer los resultados y guardarlos en el array
if ($resultado->num_rows > 0) {
    // num_rows > 0 significa "si hay al menos una fila"
    while ($fila = $resultado->fetch_assoc()) {
        // fetch_assoc() nos da cada fila como un array asociativo
        $citas[] = $fila;
    }
}


$conexion->close();

//Convertir el array de citas a formato JSON y mostrarlo

header('Content-Type: application/json');
echo json_encode($citas);

?>