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
    $id_estado_completada = 4; 

    try {
        // 1. Obtener el Precio Total Acordado
        $sql_precio = "SELECT t.precio_total 
                       FROM cita c 
                       JOIN tatuaje t ON c.id_tatuaje = t.id 
                       WHERE c.id = ?";
        $stmt_precio = $conexion->prepare($sql_precio);
        $stmt_precio->bind_param("i", $id_cita);
        $stmt_precio->execute();
        $res_precio = $stmt_precio->get_result()->fetch_assoc();
        $precio_total = (float)$res_precio['precio_total'];
        $stmt_precio->close();

        if ($precio_total <= 0) {
            echo json_encode(['success' => false, 'message' => 'No se puede completar: La cita no tiene un precio definido.']);
            exit;
        }

        // 2. Obtener el Total Pagado
        $sql_pagos = "SELECT IFNULL(SUM(monto), 0) as total_pagado FROM pago WHERE id_cita = ?";
        $stmt_pagos = $conexion->prepare($sql_pagos);
        $stmt_pagos->bind_param("i", $id_cita);
        $stmt_pagos->execute();
        $res_pagos = $stmt_pagos->get_result()->fetch_assoc();
        $total_pagado = (float)$res_pagos['total_pagado'];
        $stmt_pagos->close();

        // 3. Validar Deuda
        if ($total_pagado < $precio_total) {
            $falta = $precio_total - $total_pagado;
            echo json_encode([
                'success' => false, 
                'message' => "Falta pago. Se han cubierto $$total_pagado de $$precio_total. Restan $$falta."
            ]);
            exit;
        }

        // 4. Si todo cuadra, Completar
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