<?php
session_start();
include 'conexion.php'; 
header('Content-Type: application/json');

// 1. BÚNKER DE SEGURIDAD
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_cita'])) {
    
    $id_cita = (int)$_POST['id_cita'];
    $id_estado_completada = 4; 

    try {
        // --- ⬇️ VALIDACIÓN NUEVA: VERIFICAR PAGOS ⬇️ ---
        // Buscamos si existe algún pago para esta cita que sea 'Liquidación' o 'Pago Completo'
        // (Asumimos que 'Anticipo' NO cuenta para completar)
        
        // Primero, obtengamos los IDs de los tipos de pago válidos para completar
        // (Liquidación o Pago Completo)
        $sql_tipos = "SELECT id FROM tipo_pago WHERE nombre IN ('Liquidacion', 'Pago Completo')";
        $res_tipos = $conexion->query($sql_tipos);
        $ids_validos = [];
        while($row = $res_tipos->fetch_assoc()) {
            $ids_validos[] = $row['id'];
        }
        
        if (empty($ids_validos)) {
            // Si por alguna razón no encontró los tipos, usa lógica por defecto (ej. ID 2 y 3)
             $ids_validos = [2, 3]; 
        }
        $ids_string = implode(',', $ids_validos);

        // Ahora consultamos si hay pagos de esos tipos para esta cita
        $sql_check = "SELECT COUNT(*) as total FROM pago WHERE id_cita = ? AND id_tipo_pago IN ($ids_string)";
        $stmt_check = $conexion->prepare($sql_check);
        $stmt_check->bind_param("i", $id_cita);
        $stmt_check->execute();
        $resultado_check = $stmt_check->get_result()->fetch_assoc();
        $stmt_check->close();

        if ($resultado_check['total'] == 0) {
            // ¡ERROR! No hay pago completo.
            echo json_encode([
                'success' => false, 
                'message' => 'No se puede completar la cita. No se ha registrado un Pago Completo o Liquidación.'
            ]);
            exit;
        }
        // --- ⬆️ FIN DE VALIDACIÓN ⬆️ ---


        // 3. Si pasó la validación, ejecutamos el UPDATE
        $sql = "UPDATE cita SET id_estado_cita = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ii", $id_estado_completada, $id_cita);

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