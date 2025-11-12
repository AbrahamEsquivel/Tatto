<?php
session_start();
include 'conexion.php'; // Ruta directa (ambos están en /php/)
header('Content-Type: application/json');

// 1. BÚNKER DE SEGURIDAD
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit;
}

// 2. OBTENER ID DE PERSONA
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    echo json_encode(['success' => false, 'message' => 'ID de persona no válido.']);
    exit;
}
$id_persona = $_GET['id'];

// 3. PREPARAMOS EL PAQUETE DE RESPUESTA
$response = [
    'success' => false,
    'kpis' => [
        'total_citas' => 0,
        'gasto_total' => 0
    ],
    'citas' => [],
    'pagos' => []
];

try {
    
    // --- CONSULTA 1: OBTENER TODAS LAS CITAS DE ESTA PERSONA ---
    // (Usamos la Vista que ya actualizamos, que tiene id_persona_cliente)
    $sql_citas = "SELECT * FROM v_AgendaCompleta WHERE id_persona_cliente = ? ORDER BY fecha_hora DESC";
    $stmt_citas = $conexion->prepare($sql_citas);
    $stmt_citas->bind_param("i", $id_persona);
    $stmt_citas->execute();
    $resultado_citas = $stmt_citas->get_result();
    
    while ($fila = $resultado_citas->fetch_assoc()) {
        $response['citas'][] = $fila;
    }
    $stmt_citas->close();

    // --- CONSULTA 2: OBTENER TODOS LOS PAGOS DE ESTA PERSONA ---
    // (Usamos la Vista que ya actualizamos, que tiene id_persona)
    $sql_pagos = "SELECT * FROM v_HistorialPagos WHERE id_persona = ? ORDER BY fecha_pago DESC";
    $stmt_pagos = $conexion->prepare($sql_pagos);
    $stmt_pagos->bind_param("i", $id_persona);
    $stmt_pagos->execute();
    $resultado_pagos = $stmt_pagos->get_result();
    
    $gasto_total = 0;
    while ($fila = $resultado_pagos->fetch_assoc()) {
        $response['pagos'][] = $fila;
        $gasto_total += (float)$fila['monto']; // Calculamos el gasto total
    }
    $stmt_pagos->close();

    // --- CÁLCULO DE KPIs ---
    $response['kpis']['total_citas'] = count($response['citas']);
    $response['kpis']['gasto_total'] = $gasto_total;
    
    // 4. ÉXITO
    $response['success'] = true;
    echo json_encode($response);
    
    $conexion->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>