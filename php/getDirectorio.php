<?php
session_start();
include 'conexion.php';

// Preparamos nuestra respuesta JSON
header('Content-Type: application/json');

// 1. BÚNKER DE SEGURIDAD
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit;
}

// 2. Preparamos el array que enviaremos
$response = [
    'success' => false,
    'directorio' => []
];

// 3. EJECUTAMOS LA CONSULTA (Súper simple gracias a la VISTA)
try {
    
    // Aquí es donde usamos la VISTA que creamos con UNION
    $sql = "SELECT * FROM v_DirectorioGeneral ORDER BY tipo_persona, nombre, apellido";
    
    $resultado = $conexion->query($sql);

    if ($resultado) {
        while ($fila = $resultado->fetch_assoc()) {
            $response['directorio'][] = $fila;
        }
        $response['success'] = true;
    } else {
        $response['message'] = "Error al ejecutar la consulta a la vista.";
    }

    $conexion->close();

} catch (mysqli_sql_exception $e) {
    // Si algo falla, enviamos un error
    $response['message'] = $e->getMessage();
}

// 4. ENVIAR LA RESPUESTA FINAL
echo json_encode($response);
?>