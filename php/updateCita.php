<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header('Location: ../login.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Recoger datos
    $id_cita = (int)$_POST['id_cita'];
    $id_tatuaje = (int)$_POST['id_tatuaje'];
    
    $fecha_hora = $_POST['fecha_hora'];
    $id_artista = (int)$_POST['id_artista'];
    $id_estado_cita = (int)$_POST['id_estado_cita'];
    
    $tatuaje_descripcion = trim($_POST['tatuaje_descripcion']);
    $id_estilo = (int)$_POST['id_estilo'];

    // NOTA: Ya no recogemos precio_total porque se eliminó del formulario.

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conexion->begin_transaction();

        // ---- OPERACIÓN 1: ACTUALIZAR TATUAJE (SIN PRECIO) ----
        $sql1 = "UPDATE tatuaje SET 
                    descripcion = ?, 
                    id_estilo = ? 
                 WHERE id = ?";
        
        $stmt1 = $conexion->prepare($sql1);
        // 'sii' -> string, int, int
        $stmt1->bind_param("sii", 
            $tatuaje_descripcion, 
            $id_estilo, 
            $id_tatuaje
        );
        $stmt1->execute();
        $stmt1->close();

        // ---- OPERACIÓN 2: ACTUALIZAR CITA (IGUAL QUE ANTES) ----
        $sql2 = "UPDATE cita SET 
                    fecha_hora = ?, 
                    id_artista = ?, 
                    id_estado_cita = ? 
                 WHERE id = ?";
        
        $stmt2 = $conexion->prepare($sql2);
        $stmt2->bind_param("siii", 
            $fecha_hora, 
            $id_artista, 
            $id_estado_cita, 
            $id_cita
        );
        $stmt2->execute();
        $stmt2->close();

        $conexion->commit();
        header('Location: ../agenda.php?success=update');
        exit;

    } catch (mysqli_sql_exception $e) {
        $conexion->rollback();
        error_log("Error en updateCita: " . $e->getMessage());
        header('Location: ../edit-cita.php?id=' . $id_cita . '&error=transaction');
        exit;
    }

} else {
    header('Location: ../agenda.php');
    exit;
}
?>