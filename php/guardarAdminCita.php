<?php
session_start();
include 'conexion.php'; 

// 1. BÚNKER DE SEGURIDAD
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header('Location: ../login.html');
    exit;
}

// 2. VERIFICAR QUE SEA POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // 3. RECOGER TODOS los datos del formulario
    $nombre = trim($_POST['cliente_nombre']);
    $apellido = trim($_POST['cliente_apellido']);
    $email = trim($_POST['cliente_email']);
    $telefono = trim($_POST['cliente_telefono']);
    
    $fecha_hora = $_POST['fecha_hora'];
    $id_artista = (int)$_POST['id_artista'];
    $id_estilo = (int)$_POST['id_estilo'];
    $id_parte_cuerpo = (int)$_POST['id_parte_cuerpo'];
    $tatuaje_descripcion = trim($_POST['tatuaje_descripcion']);
    $id_estado_cita = (int)$_POST['id_estado_cita'];
    
    // El admin sí puede poner el precio desde el inicio
    $precio_total = empty(trim($_POST['precio_total'])) ? NULL : (float)$_POST['precio_total'];

    // 4. INICIAR TRANSACCIÓN (tocamos 4 tablas)
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conexion->begin_transaction();

    try {
        // --- Lógica de Cliente (Igual que en el SP) ---
        // 1. Buscar o crear la PERSONA
        $id_persona = null;
        $stmt_find_p = $conexion->prepare("SELECT id FROM persona WHERE email = ?");
        $stmt_find_p->bind_param("s", $email);
        $stmt_find_p->execute();
        $resultado_p = $stmt_find_p->get_result();
        
        if ($resultado_p->num_rows > 0) {
            $fila_p = $resultado_p->fetch_assoc();
            $id_persona = $fila_p['id'];
            // (Opcional) Actualizamos sus datos
            $stmt_update_p = $conexion->prepare("UPDATE persona SET nombre = ?, apellido = ?, telefono = ? WHERE id = ?");
            $stmt_update_p->bind_param("sssi", $nombre, $apellido, $telefono, $id_persona);
            $stmt_update_p->execute();
            $stmt_update_p->close();
        } else {
            $sql_persona = "INSERT INTO persona (nombre, apellido, email, telefono) VALUES (?, ?, ?, ?)";
            $stmt_persona = $conexion->prepare($sql_persona);
            $stmt_persona->bind_param("ssss", $nombre, $apellido, $email, $telefono);
            $stmt_persona->execute();
            $id_persona = $conexion->insert_id;
            $stmt_persona->close();
        }
        $stmt_find_p->close();

        // 2. Buscar o crear el CLIENTE
        $id_cliente = null;
        $stmt_find_c = $conexion->prepare("SELECT id FROM cliente WHERE id_persona = ?");
        $stmt_find_c->bind_param("i", $id_persona);
        $stmt_find_c->execute();
        $resultado_c = $stmt_find_c->get_result();
        
        if ($resultado_c->num_rows > 0) {
            $fila_c = $resultado_c->fetch_assoc();
            $id_cliente = $fila_c['id'];
        } else {
             $sql_cliente = "INSERT INTO cliente (id_persona) VALUES (?)";
             $stmt_cliente = $conexion->prepare($sql_cliente);
             $stmt_cliente->bind_param("i", $id_persona);
             $stmt_cliente->execute();
             $id_cliente = $conexion->insert_id;
             $stmt_cliente->close();
        }
        $stmt_find_c->close();

        // --- Lógica de Cita (Control del Admin) ---
        
        // 3. Insertar el TATUAJE (ahora con el precio)
        $sql_tatuaje = "INSERT INTO tatuaje (id_cliente, id_estilo, id_parte_cuerpo, descripcion, precio_total)
                        VALUES (?, ?, ?, ?, ?)";
        $stmt_tatuaje = $conexion->prepare($sql_tatuaje);
        $stmt_tatuaje->bind_param("iiisd", $id_cliente, $id_estilo, $id_parte_cuerpo, $tatuaje_descripcion, $precio_total);
        $stmt_tatuaje->execute();
        $id_tatuaje = $conexion->insert_id;
        $stmt_tatuaje->close();

        // 4. Insertar la CITA (ahora con el estado)
        $sql_cita = "INSERT INTO cita (fecha_hora, id_cliente, id_artista, id_tatuaje, id_estado_cita)
                     VALUES (?, ?, ?, ?, ?)";
        $stmt_cita = $conexion->prepare($sql_cita);
        $stmt_cita->bind_param("siiii", $fecha_hora, $id_cliente, $id_artista, $id_tatuaje, $id_estado_cita);
        $stmt_cita->execute();
        $stmt_cita->close();

        // 5. Si todo salió bien, confirmar
        $conexion->commit();
        header('Location: ../agenda.php?success=admin_create'); // Redirigir a la agenda

    } catch (mysqli_sql_exception $e) {
        // 6. Si algo falló, deshacer todo
        $conexion->rollback();
        $error = urlencode("Error de BD: " . $e->getMessage());
        header('Location: ../admin-crear-cita.php?error=' . $error); // Regresar al formulario con error
    
    } finally {
        $conexion->close();
    }

} else {
    header('Location: ../dashboard.php');
    exit;
}
?>