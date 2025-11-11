<?php

include 'conexion.php';

// 1. DEFINIR LA CONSULTA USANDO LA VISTA
// ¡Mucho más limpio que antes!
$sql = "SELECT * FROM v_AgendaCompleta ORDER BY fecha_hora ASC";

// 2. Ejecutar la consulta
$resultado = $conexion->query($sql);

// 3. Preparar el array para guardar los datos
$citas = array();

// 4. Recorrer los resultados y guardarlos en el array
if ($resultado && $resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $citas[] = $fila;
    }
}

// 5. Cerrar la conexión
$conexion->close();

// 6. Convertir a JSON y mostrar
header('Content-Type: application/json');
echo json_encode($citas);

?>