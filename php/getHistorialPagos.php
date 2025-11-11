<?php
session_start();
// Importante: la ruta es directa porque este archivo
// está en la misma carpeta 'php' que conexion.php
include 'conexion.php'; 

// Avisamos que la respuesta será JSON
header('Content-Type: application/json');

// 1. BÚNKER DE SEGURIDAD
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit;
}

// 2. Preparamos el array de respuesta
$response = [
    'success' => false,
    'pagos' => [] // Aquí guardaremos los pagos
];

// 3. Hacemos la consulta a la VISTA
try {
    
    // La consulta es súper simple gracias a la vista
    $sql = "SELECT * FROM v_HistorialPagos ORDER BY fecha_pago DESC";
    
    $resultado = $conexion->query($sql);

    if ($resultado) {
        // Si la consulta fue exitosa, leemos las filas
        while ($fila = $resultado->fetch_assoc()) {
            $response['pagos'][] = $fila;
        }
        $response['success'] = true;
    } else {
        // Si la consulta falló
        throw new Exception("Error al consultar la vista de pagos: " . $conexion->error);
    }
    
    $conexion->close();

} catch (Exception $e) {
    // Si algo en el 'try' falla
    $response['message'] = $e->getMessage();
}

// 4. Enviamos la respuesta JSON final
echo json_encode($response);
?>