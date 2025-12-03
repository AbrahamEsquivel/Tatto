<?php
session_start();
include 'conexion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_cita'])) {
    
    $id_cita = (int)$_POST['id_cita'];
    $id_estado_cancelada = 3; 

    try {
        // --- ⬇️ VALIDACIÓN FINANCIERA ⬇️ ---
        // Verificamos cuánto se ha pagado y cuál es el precio
        $sql_check = "SELECT t.precio_total, 
                             (SELECT IFNULL(SUM(monto), 0) FROM pago WHERE id_cita = c.id) as total_pagado 
                      FROM cita c 
                      JOIN tatuaje t ON c.id_tatuaje = t.id 
                      WHERE c.id = ?";
        
        $stmt_check = $conexion->prepare($sql_check);
        $stmt_check->bind_param("i", $id_cita);
        $stmt_check->execute();
        $res = $stmt_check->get_result()->fetch_assoc();
        $stmt_check->close();

        $precio = (float)$res['precio_total'];
        $pagado = (float)$res['total_pagado'];

        // REGLA: Si ya pagó el 100% (o más), NO se puede cancelar.
        // (Debe eliminar los pagos primero para poder cancelar la cita)
        if ($precio > 0 && $pagado >= ($precio - 0.01)) {
            echo json_encode([
                'success' => false, 
                'message' => "No se puede cancelar una cita TOTALMENTE PAGADA. \n\nPor favor, elimina los pagos registrados primero (realiza la devolución) antes de cancelar la cita."
            ]);
            exit;
        }
        // --- ⬆️ FIN VALIDACIÓN ⬆️ ---

        // Si debe dinero o es solo un anticipo, sí dejamos cancelar (asumiendo que el anticipo se pierde o se gestiona aparte)
        $sql = "UPDATE cita SET id_estado_cita = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ii", $id_estado_cancelada, $id_cita);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar la base de datos.']);
        }

        $stmt->close();
        $conexion->close();

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Solicitud no válida.']);
}
?>