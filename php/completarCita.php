<?php
session_start();
include 'conexion.php'; // Usa la ruta directa

// Preparamos la respuesta JSON
header('Content-Type: application/json');

// 1. BÚNKER DE SEGURIDAD
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit;
}

// 2. VERIFICAR QUE SEA POST Y TENGA EL ID
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_cita'])) {
    
    $id_cita = (int)$_POST['id_cita'];
    // ID '4' es 'Completada' en tu base de datos
    $id_estado_completada = 4; 

    // 3. PREPARAR Y EJECUTAR EL UPDATE
    $sql = "UPDATE cita SET id_estado_cita = ? WHERE id = ?";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ii", $id_estado_completada, $id_cita);

    if ($stmt->execute()) {
        // ÉXITO
        echo json_encode(['success' => true]);
    } else {
        // ERROR
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la base de datos.']);
    }

    $stmt->close();
    $conexion->close();

} else {
    // Si no es POST o falta el ID
    echo json_encode(['success' => false, 'message' => 'Solicitud no válida.']);
}
?>