<?php
session_start();
include 'conexion.php';
header('Content-Type: application/json');

// 1. BÚNKER DE SEGURIDAD
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit;
}

// 2. EJECUTAR EL PROCEDIMIENTO ALMACENADO (EL CURSOR)
// Este script tiene UNA sola misión: llamar al procedimiento que ya creamos.
try {
    
    $sql = "CALL sp_EnviarRecordatoriosPendientes()";
    
    // Usamos query() porque CALL no devuelve un set de resultados estándar
    if ($conexion->query($sql)) {
        
        // ¡Éxito! El cursor corrió.
        // (Nota: Necesitamos limpiar cualquier set de resultados que el SP
        // pudiera haber dejado abierto antes de hacer la siguiente consulta)
        while($conexion->more_results() && $conexion->next_result()) {
            $conexion->use_result();
        }

        echo json_encode(['success' => true, 'message' => 'Proceso de recordatorios ejecutado.']);

    } else {
        throw new Exception('Error al ejecutar el procedimiento almacenado.');
    }
    
    $conexion->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>