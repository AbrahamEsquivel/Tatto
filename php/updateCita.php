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

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conexion->begin_transaction();

        // 1. Actualizar Tatuaje
        $sql1 = "UPDATE tatuaje SET descripcion = ?, id_estilo = ? WHERE id = ?";
        $stmt1 = $conexion->prepare($sql1);
        $stmt1->bind_param("sii", $tatuaje_descripcion, $id_estilo, $id_tatuaje);
        $stmt1->execute();
        $stmt1->close();

        // 2. Validación de Pago (Si intentan marcar como Completada)
        if ($id_estado_cita == 4) {
             $sql_check = "SELECT COUNT(*) as total FROM pago 
                           WHERE id_cita = ? 
                           AND id_tipo_pago IN (SELECT id FROM tipo_pago WHERE nombre IN ('Liquidacion', 'Pago Completo'))";
             $stmt_check = $conexion->prepare($sql_check);
             $stmt_check->bind_param("i", $id_cita);
             $stmt_check->execute();
             $res_check = $stmt_check->get_result()->fetch_assoc();
             $stmt_check->close();

             if ($res_check['total'] == 0) {
                 // Lanzamos el error voluntariamente
                 throw new Exception("No puedes marcar como Completada sin un Pago Completo o Liquidación registrado.");
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
        // ⬇️ CAMBIO AQUÍ: Usamos 'Exception' genérico para atrapar TODO
        $conexion->rollback();
        
        // Codificamos el mensaje para pasarlo por la URL
        $error_msg = urlencode($e->getMessage());
        
        // Redirigimos de vuelta al formulario con el error
        header('Location: ../edit-cita.php?id=' . $id_cita . '&error=' . $error_msg);
        exit;
    }

} else {
    header('Location: ../agenda.php');
    exit;
}
?>