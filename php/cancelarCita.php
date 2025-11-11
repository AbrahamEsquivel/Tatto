<?php
session_start();
include 'conexion.php';

// Preparamos la respuesta JSON
header('Content-Type: application/json');

// 1. BÚNKER DE SEGURIDAD
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    // Si no está logueado, enviar error JSON
    echo json_encode(['success' => false, 'message' => 'No autorizado. Vuelva a iniciar sesión.']);
    exit;
}

// 2. VERIFICAR QUE SEA POST Y TENGA EL ID
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_cita'])) {
    
    $id_cita = (int)$_POST['id_cita'];
    // ID '3' es 'Cancelada' en tu base de datos
    $id_estado_cancelada = 3; 

    // 3. PREPARAR Y EJECUTAR EL UPDATE
    $sql = "UPDATE cita SET id_estado_cita = ? WHERE id = ?";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ii", $id_estado_cancelada, $id_cita);

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