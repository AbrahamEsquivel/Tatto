<?php
// 1. INICIAMOS SESIÓN Y CONEXIÓN
session_start();
include 'conexion.php';

// 2. BÚNKER DE SEGURIDAD
// Nadie que no esté logueado puede ejecutar este script
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header('Location: ../login.html');
    exit;
}

// 3. VERIFICAR QUE SEA UN ENVÍO POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // 4. RECOGER TODOS LOS DATOS DEL FORMULARIO
    // (Usamos (int) para sanitizar los números)
    $id_cita = (int)$_POST['id_cita'];
    $id_tatuaje = (int)$_POST['id_tatuaje'];
    $id_estilo = (int)$_POST['id_estilo'];
    $fecha_hora = $_POST['fecha_hora'];
    $id_artista = (int)$_POST['id_artista'];
    $id_estado_cita = (int)$_POST['id_estado_cita'];
    
    $tatuaje_descripcion = trim($_POST['tatuaje_descripcion']);
    
    // Manejo especial para el precio: si está vacío, guardar NULL
    $precio_total = empty(trim($_POST['precio_total'])) ? NULL : (float)$_POST['precio_total'];

    
    // 5. ¡INICIA EL REQUISITO "MANEJO DE TRANSACCIONES"!
    
    // Decimos a MySQLi que reporte errores como excepciones
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        // Iniciar la transacción
        $conexion->begin_transaction();

        // ---- OPERACIÓN 1: ACTUALIZAR LA TABLA TATUAJE ----
        $sql1 = "UPDATE tatuaje SET 
            descripcion = ?, 
            precio_total = ?,
            id_estilo = ?  /* ⬅️ AÑADE ESTA LÍNEA */
         WHERE id = ?";

            $stmt1 = $conexion->prepare($sql1);
            // "sdi" cambia a "sdii"
            $stmt1->bind_param("sdii", // ⬅️ CAMBIA ESTO
                $tatuaje_descripcion, 
                $precio_total, 
                $id_estilo, // ⬅️ AÑADE ESTA LÍNEA
                $id_tatuaje
            );
        $stmt1->execute();
        $stmt1->close();

        // ---- OPERACIÓN 2: ACTUALIZAR LA TABLA CITA ----
        $sql2 = "UPDATE cita SET 
                    fecha_hora = ?, 
                    id_artista = ?, 
                    id_estado_cita = ? 
                 WHERE id = ?";
        
        $stmt2 = $conexion->prepare($sql2);
        // "siii" = string, integer, integer, integer
        $stmt2->bind_param("siii", 
            $fecha_hora, 
            $id_artista, 
            $id_estado_cita, 
            $id_cita
        );
        $stmt2->execute();
        $stmt2->close();

        // Si llegamos aquí, ambas operaciones fueron exitosas
        // ¡Confirmamos los cambios!
        $conexion->commit();

        // 6. REDIRIGIR A LA AGENDA CON MENSAJE DE ÉXITO
        header('Location: ../agenda.php?success=update');
        exit;

    } catch (mysqli_sql_exception $e) {
        
        // ¡HUBO UN ERROR! Deshacemos todos los cambios
        $conexion->rollback();

        // 7. REDIRIGIR DE VUELTA AL FORMULARIO CON MENSAJE DE ERROR
        // (Enviamos el ID de vuelta para que el formulario sepa qué cita era)
        error_log("Error en la transacción: " . $e->getMessage()); // Log para ti
        header('Location: ../edit-cita.php?id=' . $id_cita . '&error=transaction');
        exit;
    }

} else {
    // Si alguien intenta entrar a este archivo directo, lo regresamos
    header('Location: ../agenda.php');
    exit;
}
?>