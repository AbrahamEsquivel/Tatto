<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header('Location: ../login.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id_cita = (int)$_POST['id_cita'];
    $id_tatuaje = (int)$_POST['id_tatuaje'];
    
    $fecha_hora = $_POST['fecha_hora'];
    $id_artista = (int)$_POST['id_artista'];
    $id_estado_cita = (int)$_POST['id_estado_cita'];
    
    $tatuaje_descripcion = trim($_POST['tatuaje_descripcion']);
    $id_estilo = (int)$_POST['id_estilo'];
    
    // Recogemos el nuevo precio propuesto
    $precio_total = empty(trim($_POST['precio_total'])) ? 0 : (float)$_POST['precio_total'];

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conexion->begin_transaction();

        // --- ⬇️ NUEVA VALIDACIÓN: PRECIO vs PAGADO ⬇️ ---
        
        // 1. Averiguar cuánto ha pagado el cliente hasta hoy
        $sql_check_pagos = "SELECT IFNULL(SUM(monto), 0) as total_pagado FROM pago WHERE id_cita = ?";
        $stmt_check = $conexion->prepare($sql_check_pagos);
        $stmt_check->bind_param("i", $id_cita);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result()->fetch_assoc();
        $total_pagado_actualmente = (float)$res_check['total_pagado'];
        $stmt_check->close();

        // 2. Si intentas bajar el precio a MENOS de lo que ya pagaron... ¡ERROR!
        // (Usamos 0.01 de tolerancia para decimales)
        if ($precio_total < ($total_pagado_actualmente - 0.01)) {
            throw new Exception("Error Lógico: No puedes bajar el precio a $$precio_total porque el cliente ya ha pagado $$total_pagado_actualmente. El precio total no puede ser menor a lo abonado.");
        }
        // --- ⬆️ FIN DE LA VALIDACIÓN ⬆️ ---


        // 3. Actualizar Tatuaje (Si pasó la validación, guardamos)
        $sql1 = "UPDATE tatuaje SET descripcion = ?, id_estilo = ?, precio_total = ? WHERE id = ?";
        $stmt1 = $conexion->prepare($sql1);
        // 'sidi' -> string, int, double, int
        $stmt1->bind_param("sidi", $tatuaje_descripcion, $id_estilo, $precio_total, $id_tatuaje);
        $stmt1->execute();
        $stmt1->close();

        // 4. Validación de Estado "Completada" (La que ya teníamos)
        if ($id_estado_cita == 4) { 
             if ($precio_total <= 0) {
                 throw new Exception("No puedes completar una cita que no tiene precio definido ($0).");
             }
             
             // Usamos la variable que ya calculamos arriba ($total_pagado_actualmente)
             if ($total_pagado_actualmente < ($precio_total - 0.01)) {
                 $falta = $precio_total - $total_pagado_actualmente;
                 throw new Exception("Deuda pendiente: El cliente ha pagado $$total_pagado_actualmente de $$precio_total. Faltan $$falta.");
             }
        }

        if ($id_estado_cita == 3) { 
             // Verificamos si ya hay pagos
             $sql_pagos = "SELECT IFNULL(SUM(monto), 0) as total_pagado 
                           FROM pago WHERE id_cita = ?";
             $stmt_pagos = $conexion->prepare($sql_pagos);
             $stmt_pagos->bind_param("i", $id_cita);
             $stmt_pagos->execute();
             $res_pagos = $stmt_pagos->get_result()->fetch_assoc();
             $total_pagado = (float)$res_pagos['total_pagado'];
             $stmt_pagos->close();

             // Si ya pagó casi todo (o todo), NO dejamos cancelar
             // (Usamos tolerancia de 0.01 y validamos que el precio no sea 0)
             if ($precio_total > 0 && $total_pagado >= ($precio_total - 0.01)) {
                 throw new Exception("Error: No puedes CANCELAR una cita que ya está 100% pagada. Debes eliminar los pagos primero (devolución) antes de cancelar.");
             }
        }
        
        // 5. Actualizar Cita
        $sql2 = "UPDATE cita SET fecha_hora = ?, id_artista = ?, id_estado_cita = ? WHERE id = ?";
        $stmt2 = $conexion->prepare($sql2);
        $stmt2->bind_param("siii", $fecha_hora, $id_artista, $id_estado_cita, $id_cita);
        $stmt2->execute();
        $stmt2->close();

        $conexion->commit();
        header('Location: ../agenda.php?success=update');
        exit;

    } catch (Exception $e) { 
        $conexion->rollback();
        $error_msg = urlencode($e->getMessage());
        header('Location: ../edit-cita.php?id=' . $id_cita . '&error=' . $error_msg);
        exit;
    }

} else {
    header('Location: ../agenda.php');
    exit;
}
?>