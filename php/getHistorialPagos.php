<?php
session_start();
include 'conexion.php'; 
header('Content-Type: application/json');

// 1. BÚNKER DE SEGURIDAD
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit;
}

$response = [
    'success' => false,
    'pagos' => []
];

// 3. Hacemos la consulta a la VISTA
try {
    // Traemos TODOS los pagos, ordenados por el más reciente
    $sql = "SELECT * FROM v_HistorialPagos ORDER BY fecha_pago DESC";
    
    $resultado = $conexion->query($sql);

    if ($resultado) {
        while ($fila = $resultado->fetch_assoc()) {
            $response['pagos'][] = $fila;
        }
        $response['success'] = true;
    } else {
        throw new Exception("Error al consultar la vista de pagos: " . $conexion->error);
    }
    
    $conexion->close();

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// 4. Enviamos la respuesta JSON final
echo json_encode($response);
?>