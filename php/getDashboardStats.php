<?php
session_start();
include 'conexion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit;
}

$dashboardData = [
    'kpis' => [],
    'reporte_artistas' => [],
    'log_notificaciones' => []
];

try {

    // ----- CONSULTA 1: TARJETAS (KPIs) -----
    // (Esta consulta está bien, no cambia)
    $sql_kpi = "SELECT
        SUM(CASE WHEN estado_cita = 'Completada' THEN precio_total ELSE 0 END) AS total_ingresos,
        COUNT(CASE WHEN estado_cita = 'Pendiente' THEN 1 ELSE NULL END) AS total_pendientes,
        AVG(CASE WHEN estado_cita = 'Completada' AND precio_total > 0 THEN precio_total ELSE NULL END) AS promedio_precio,
        COUNT(id_cita) AS total_citas
    FROM v_AgendaCompleta";

    $resultado_kpi = $conexion->query($sql_kpi);
    if ($resultado_kpi) {
        $data_kpi = $resultado_kpi->fetch_assoc();
        $dashboardData['kpis'] = [
            'ingresos_totales' => $data_kpi['total_ingresos'] ?? 0,
            'citas_pendientes' => $data_kpi['total_pendientes'] ?? 0,
            'precio_promedio' => $data_kpi['promedio_precio'] ?? 0,
            'total_citas' => $data_kpi['total_citas'] ?? 0
        ];
    } else {
        throw new Exception("Error en consulta KPI: " . $conexion->error);
    }

    // ----- CONSULTA 2: REPORTE DE ARTISTAS (CORREGIDA SIN GROUPING) -----
    // ⬇️ ESTA ES LA CONSULTA CORREGIDA (PLAN C) ⬇️
    $sql_reporte = "
        SELECT * FROM (
            -- Consulta Interna: Hace el ROLLUP y el HAVING
            SELECT
                artista_nombre,
                
                -- Plan C: Creamos 'es_total_general' manualmente
                -- Si el nombre es NULL, es la fila total (ponemos 1), si no, es 0.
                CASE 
                    WHEN artista_nombre IS NULL THEN 1
                    ELSE 0 
                END AS es_total_general,
                
                SUM(precio_total) AS ingresos_generados,
                COUNT(id_cita) AS citas_completadas
            FROM v_AgendaCompleta
            WHERE estado_cita = 'Completada'
            GROUP BY artista_nombre WITH ROLLUP
            HAVING SUM(precio_total) > 0
        ) AS reporte_con_rollup
        -- Consulta Externa: Ordena por nuestra columna 'es_total_general'
        ORDER BY es_total_general ASC, ingresos_generados DESC";
    // ⬆️ FIN DE LA CONSULTA CORREGIDA ⬆️

    $resultado_reporte = $conexion->query($sql_reporte);
    if ($resultado_reporte) {
        while ($fila = $resultado_reporte->fetch_assoc()) {
            $dashboardData['reporte_artistas'][] = $fila;
        }
    } else {
        throw new Exception("Error en consulta REPORTE: " . $conexion->error);
    }

    // ----- CONSULTA 3: BITÁCORA -----
    // (Esta consulta está bien, no cambia)
    $sql_log = "SELECT id_cita, mensaje, fecha_envio 
                FROM log_notificaciones 
                ORDER BY fecha_envio DESC 
                LIMIT 10";
    
    $resultado_log = $conexion->query($sql_log);
    if ($resultado_log) {
        while ($fila = $resultado_log->fetch_assoc()) {
            $dashboardData['log_notificaciones'][] = $fila;
        }
    } else {
        throw new Exception("Error en consulta LOG: " . $conexion->error);
    }
    
    $dashboardData['success'] = true;
    echo json_encode($dashboardData);

} catch (Exception $e) { // Atrapa el primer error que ocurra
    echo json_encode(['success' => false, 'message' => 'Error de SQL: ' . $e->getMessage()]);
}

$conexion->close();
?>