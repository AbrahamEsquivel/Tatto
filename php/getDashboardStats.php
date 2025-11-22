<?php
session_start();
// PASO 1: ASEGÚRATE DE QUE ESTA RUTA ES CORRECTA.
include 'conexion.php';

// PASO 2: Header JSON
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
    // Ahora calculamos todo basándonos en la realidad de la tabla PAGO
    $sql_kpi = "SELECT
        -- 1. Ingresos Totales: Suma real de la tabla 'pago'
        (SELECT IFNULL(SUM(monto), 0) FROM pago) AS total_ingresos,
        
        -- 2. Citas Pendientes (Desde la vista)
        (SELECT COUNT(*) FROM v_AgendaCompleta WHERE estado_cita = 'Pendiente') AS total_pendientes,
        
        -- 3. Total Citas Registradas
        (SELECT COUNT(*) FROM v_AgendaCompleta) AS total_citas,

        -- 4. Precio Promedio Real: (Total Ingresos / Total Citas Completadas)
        (
            SELECT 
                IFNULL(SUM(monto), 0) / NULLIF((SELECT COUNT(*) FROM v_AgendaCompleta WHERE estado_cita = 'Completada'), 0)
            FROM pago
            -- Solo sumamos pagos de citas que ya se completaron para el promedio sea real
            WHERE id_cita IN (SELECT id_cita FROM v_AgendaCompleta WHERE estado_cita = 'Completada')
        ) AS promedio_precio";

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

    // ----- CONSULTA 2: REPORTE DE ARTISTAS (SÚPER MEJORADA) -----
    // Ahora unimos la vista con la tabla de PAGO para sumar dinero real
    $sql_reporte = "
        SELECT * FROM (
            SELECT
                v.artista_nombre,
                
                -- Detectar fila de Total
                CASE 
                    WHEN v.artista_nombre IS NULL THEN 1
                    ELSE 0 
                END AS es_total_general,
                
                -- Sumar los montos de la tabla PAGO
                IFNULL(SUM(p.monto), 0) AS ingresos_generados,
                
                -- Contar citas únicas (DISTINCT es clave porque una cita puede tener 2 pagos)
                COUNT(DISTINCT v.id_cita) AS citas_completadas
            
            FROM v_AgendaCompleta v
            -- Unimos con pagos (LEFT JOIN para contar citas aunque no tengan pago aun)
            LEFT JOIN pago p ON v.id_cita = p.id_cita
            
            WHERE v.estado_cita = 'Completada'
            
            GROUP BY v.artista_nombre WITH ROLLUP
            
            -- Filtro HAVING: Solo mostrar si hay dinero o citas
            HAVING ingresos_generados > 0 OR citas_completadas > 0
            
        ) AS reporte_con_rollup
        ORDER BY es_total_general ASC, ingresos_generados DESC";

    $resultado_reporte = $conexion->query($sql_reporte);
    if ($resultado_reporte) {
        while ($fila = $resultado_reporte->fetch_assoc()) {
            $dashboardData['reporte_artistas'][] = $fila;
        }
    } else {
        throw new Exception("Error en consulta REPORTE: " . $conexion->error);
    }

    // ----- CONSULTA 3: BITÁCORA -----
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

} catch (Exception $e) { 
    echo json_encode(['success' => false, 'message' => 'Error de SQL: ' . $e->getMessage()]);
}

$conexion->close();
?>