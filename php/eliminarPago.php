<?php
session_start();
include 'conexion.php';
header('Content-Type: application/json');

// 1. Seguridad
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_pago'])) {
    
    $id_pago = (int)$_POST['id_pago'];

    try {
        // --- ⬇️ VALIDACIÓN NUEVA: ESTADO DE LA CITA ⬇️ ---
        
        // 1. Averiguamos a qué cita pertenece este pago y cuál es su estado
        $sql_check = "SELECT c.id_estado_cita 
                      FROM pago p 
                      JOIN cita c ON p.id_cita = c.id 
                      WHERE p.id = ?";
        
        $stmt_check = $conexion->prepare($sql_check);
        $stmt_check->bind_param("i", $id_pago);
        $stmt_check->execute();
        $resultado = $stmt_check->get_result();
        
        if ($resultado->num_rows === 0) {
            throw new Exception("El pago no existe.");
        }

        $fila = $resultado->fetch_assoc();
        $id_estado = (int)$fila['id_estado_cita'];
        $stmt_check->close();

        // 2. Si la cita está 'Completada' (ID 4) o 'Cancelada' (ID 3), BLOQUEAMOS
        if ($id_estado == 4) {
            throw new Exception("⛔ ACCIÓN DENEGADA: No puedes eliminar pagos de una cita que ya está COMPLETADA.\n\nPara hacer esto, primero cambia el estado de la cita a 'Confirmada' desde la Agenda.");
        }
        if ($id_estado == 3) {
            throw new Exception("No puedes modificar pagos de una cita Cancelada.");
        }
        // --- ⬆️ FIN DE VALIDACIÓN ⬆️ ---


        // 3. Ejecutar el borrado (Si pasó la validación)
        $sql = "DELETE FROM pago WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id_pago);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Pago eliminado correctamente.']);
        } else {
            throw new Exception("Error al eliminar el pago.");
        }
        
        $stmt->close();
        $conexion->close();

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Solicitud inválida.']);
}
?>