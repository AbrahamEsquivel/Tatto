<?php
session_start();
include 'conexion.php';
header('Content-Type: application/json');

// 1. BÚNKER DE SEGURIDAD
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit;
}

// 2. VALIDAR ID
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    echo json_encode(['success' => false, 'message' => 'ID de persona no válido.']);
    exit;
}
$id_persona = (int)$_GET['id'];

$response = [
    'success' => false,
    'kpis' => ['total_citas' => 0, 'gasto_total' => 0],
    'citas' => [],
    'pagos' => [],
    'es_artista' => false
];

try {
    // 3. VERIFICAR SI ES ARTISTA
    $sql_es_artista = "SELECT id FROM artista WHERE id_persona = ?";
    $stmt_check = $conexion->prepare($sql_es_artista);
    $stmt_check->bind_param("i", $id_persona);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();
    $is_artist = ($res_check->num_rows > 0);
    $stmt_check->close();
    
    $response['es_artista'] = $is_artist;

    // 4. CONSULTA DE CITAS
    if ($is_artist) {
        $sql_citas = "SELECT * FROM v_AgendaCompleta WHERE id_persona_artista = ? ORDER BY fecha_hora DESC";
    } else {
        $sql_citas = "SELECT * FROM v_AgendaCompleta WHERE id_persona_cliente = ? ORDER BY fecha_hora DESC";
    }
    
    $stmt_citas = $conexion->prepare($sql_citas);
    $stmt_citas->bind_param("i", $id_persona);
    $stmt_citas->execute();
    $res_citas = $stmt_citas->get_result();
    while ($fila = $res_citas->fetch_assoc()) {
        $response['citas'][] = $fila;
    }
    $stmt_citas->close();

    // 5. CONSULTA DE PAGOS
    if ($is_artist) {
        $sql_pagos = "SELECT 
                        p.id AS id_pago, p.fecha_pago, p.monto, 
                        tp.nombre AS tipo_pago, mp.nombre AS metodo_pago
                      FROM pago p
                      JOIN cita c ON p.id_cita = c.id
                      JOIN artista a ON c.id_artista = a.id
                      JOIN tipo_pago tp ON p.id_tipo_pago = tp.id
                      JOIN metodo_pago mp ON p.id_metodo_pago = mp.id
                      WHERE a.id_persona = ?
                      ORDER BY p.fecha_pago DESC";
    } else {
        $sql_pagos = "SELECT * FROM v_HistorialPagos WHERE id_persona = ? ORDER BY fecha_pago DESC";
    }

    $stmt_pagos = $conexion->prepare($sql_pagos);
    $stmt_pagos->bind_param("i", $id_persona);
    $stmt_pagos->execute();
    $res_pagos = $stmt_pagos->get_result();
    
    $total_dinero = 0;
    while ($fila = $res_pagos->fetch_assoc()) {
        $response['pagos'][] = $fila;
        $total_dinero += (float)$fila['monto'];
    }
    $stmt_pagos->close();

    // 6. KPIs
    $response['kpis']['total_citas'] = count($response['citas']);
    $response['kpis']['gasto_total'] = $total_dinero;
    
    $response['success'] = true;
    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conexion->close();
?>