<?php
session_start();
include 'conexion.php';

// 1. Preparamos nuestra respuesta JSON
header('Content-Type: application/json');

// 2. BÚNKER DE SEGURIDAD
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    // Si no está logueado, enviar error JSON
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit;
}

// 3. Preparamos el array que enviaremos
$dashboardData = [
    'kpis' => [],
    'reporte_artistas' => []
];

// 4. EJECUTAMOS LAS CONSULTAS
try {

    // ----- CONSULTA 1: TARJETAS (KPIs) -----
    // Usamos la VISTA que ya creamos (v_AgendaCompleta)
    // Usamos FUNCIONES DE AGREGACIÓN (SUM, COUNT, AVG)
    $sql_kpi = "SELECT
        SUM(CASE WHEN estado_cita = 'Completada' THEN precio_total ELSE 0 END) AS total_ingresos,
        COUNT(CASE WHEN estado_cita = 'Pendiente' THEN 1 ELSE NULL END) AS total_pendientes,
        AVG(CASE WHEN estado_cita = 'Completada' AND precio_total > 0 THEN precio_total ELSE NULL END) AS promedio_precio,
        COUNT(id_cita) AS total_citas
    FROM v_AgendaCompleta";

    $resultado_kpi = $conexion->query($sql_kpi);
    
    if ($resultado_kpi) {
        $data_kpi = $resultado_kpi->fetch_assoc();
        // Limpiamos los valores NULL para que JS no tenga problemas
        $dashboardData['kpis'] = [
            'ingresos_totales' => $data_kpi['total_ingresos'] ?? 0,
            'citas_pendientes' => $data_kpi['total_pendientes'] ?? 0,
            'precio_promedio' => $data_kpi['promedio_precio'] ?? 0,
            'total_citas' => $data_kpi['total_citas'] ?? 0
        ];
    }

    // ----- CONSULTA 2: REPORTE DE ARTISTAS -----
    // ¡AQUÍ CUMPLIMOS LOS REQUISITOS DE GROUP BY y HAVING!
    $sql_reporte = "SELECT
        artista_nombre,
        SUM(precio_total) AS ingresos_generados,
        COUNT(id_cita) AS citas_completadas
    FROM v_AgendaCompleta
    WHERE estado_cita = 'Completada'
    GROUP BY artista_nombre
    HAVING SUM(precio_total) > 0
    ORDER BY ingresos_generados DESC";

    $resultado_reporte = $conexion->query($sql_reporte);

    if ($resultado_reporte) {
        // Recogemos todos los resultados en el array
        while ($fila = $resultado_reporte->fetch_assoc()) {
            $dashboardData['reporte_artistas'][] = $fila;
        }
    }
    
    // 5. ENVIAR LA RESPUESTA FINAL
    $dashboardData['success'] = true;
    echo json_encode($dashboardData);

} catch (mysqli_sql_exception $e) {
    // Si algo falla, enviamos un error
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conexion->close();
?>