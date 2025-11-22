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
    $id_parte_cuerpo = (int)$_POST['id_parte_cuerpo']; // ⬅️ Recibimos el dato
    
    $precio_total = empty(trim($_POST['precio_total'])) ? 0 : (float)$_POST['precio_total'];

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conexion->begin_transaction();

        // 1. Actualizar Tatuaje (AGREGAMOS id_parte_cuerpo)
        $sql1 = "UPDATE tatuaje SET 
                    descripcion = ?, 
                    id_estilo = ?, 
                    id_parte_cuerpo = ?, 
                    precio_total = ? 
                 WHERE id = ?";
        
        $stmt1 = $conexion->prepare($sql1);
        
        // Tipos: s (string), i (int), i (int), d (double), i (int)
        $stmt1->bind_param("siidi", 
            $tatuaje_descripcion, 
            $id_estilo,
            $id_parte_cuerpo, // ⬅️ Lo guardamos aquí
            $precio_total,
            $id_tatuaje
        );
        
        $stmt1->execute();
        $stmt1->close();

        // 2. Validación de Pago
        if ($id_estado_cita == 4) { 
             if ($precio_total <= 0) {
                 throw new Exception("No puedes completar una cita que no tiene precio definido ($0).");
             }
             $sql_pagos = "SELECT IFNULL(SUM(monto), 0) as total_pagado FROM pago WHERE id_cita = ?";
             $stmt_pagos = $conexion->prepare($sql_pagos);
             $stmt_pagos->bind_param("i", $id_cita);
             $stmt_pagos->execute();
             $res_pagos = $stmt_pagos->get_result()->fetch_assoc();
             $total_pagado = (float)$res_pagos['total_pagado'];
             $stmt_pagos->close();

             if ($total_pagado < ($precio_total - 0.01)) {
                 $falta = $precio_total - $total_pagado;
                 throw new Exception("Deuda pendiente: El cliente ha pagado $$total_pagado de $$precio_total. Faltan $$falta.");
             }
        }
        
        // 3. Actualizar Cita
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