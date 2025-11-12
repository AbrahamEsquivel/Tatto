<?php
session_start();
include 'conexion.php'; // ¡SOLO LA CONEXIÓN!

// 1. BÚNKER DE SEGURIDAD
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header('Location: ../login.html');
    exit;
}

// 2. VERIFICAR QUE SEA POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // 3. RECOGER DATOS BÁSICOS
    $action = $_POST['action']; // 'crear' o 'editar'
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $nombre_artistico = trim($_POST['nombre_artistico']);
    $active = (int)$_POST['active'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // 4. VALIDACIÓN DE CONTRASEÑA
    $password_hash = null; // Inicializar
    if ($action == 'crear' || ($action == 'editar' && !empty($password))) {
        if ($password !== $password_confirm) {
            $error = urlencode("Las contraseñas no coinciden.");
            $id_param = ($action == 'editar') ? '&id=' . $_POST['id_artista'] : '';
            header('Location: ../artista-form.php?action=' . $action . $id_param . '&error=' . $error);
            exit;
        }
        // ¡SEGURIDAD! Hashear la contraseña
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
    }

    // 5. INICIAR TRANSACCIÓN
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conexion->begin_transaction();

    try {
        if ($action == 'crear') {
            // --- MODO CREAR ---
            
            // 1. Insertar en 'persona'
            $sql_persona = "INSERT INTO persona (nombre, apellido, email, telefono) VALUES (?, ?, ?, ?)";
            $stmt_persona = $conexion->prepare($sql_persona);
            $stmt_persona->bind_param("ssss", $nombre, $apellido, $email, $telefono);
            $stmt_persona->execute();
            $id_persona = $conexion->insert_id; 
            $stmt_persona->close();

            // 2. Insertar en 'artista'
            $sql_artista = "INSERT INTO artista (id_persona, nombre_artistico, active, password) VALUES (?, ?, ?, ?)";
            $stmt_artista = $conexion->prepare($sql_artista);
            $stmt_artista->bind_param("isis", $id_persona, $nombre_artistico, $active, $password_hash);
            $stmt_artista->execute();
            $stmt_artista->close();

        } else if ($action == 'editar') {
            // --- MODO EDITAR ---
            $id_artista = (int)$_POST['id_artista'];
            $id_persona = (int)$_POST['id_persona'];

            // 1. Actualizar 'persona'
            $sql_persona = "UPDATE persona SET nombre = ?, apellido = ?, email = ?, telefono = ? WHERE id = ?";
            $stmt_persona = $conexion->prepare($sql_persona);
            $stmt_persona->bind_param("ssssi", $nombre, $apellido, $email, $telefono, $id_persona);
            $stmt_persona->execute();
            $stmt_persona->close();

            // 2. Actualizar 'artista' (con o sin contraseña)
            if (!empty($password_hash)) { // Solo si creamos un hash nuevo
                $sql_artista = "UPDATE artista SET nombre_artistico = ?, active = ?, password = ? WHERE id = ?";
                $stmt_artista = $conexion->prepare($sql_artista);
                $stmt_artista->bind_param("sisi", $nombre_artistico, $active, $password_hash, $id_artista);
            } else {
                $sql_artista = "UPDATE artista SET nombre_artistico = ?, active = ? WHERE id = ?";
                $stmt_artista = $conexion->prepare($sql_artista);
                $stmt_artista->bind_param("sii", $nombre_artistico, $active, $id_artista);
            }
            $stmt_artista->execute();
            $stmt_artista->close();
        }

        // 6. Si todo salió bien, confirmar
        $conexion->commit();
        header('Location: ../artistas.php?success=' . $action); // Redirigir a la lista

    } catch (mysqli_sql_exception $e) {
        // 7. Si algo falló, deshacer
        $conexion->rollback();
        $error_code = $e->getCode();
        
        if ($error_code == 1062) { // Error de "Email duplicado"
            $error = urlencode("El email '$email' ya está en uso.");
        } else {
            $error = urlencode("Error de BD: " . $e->getMessage());
        }
        
        $id_param = ($action == 'editar') ? '&id=' . $_POST['id_artista'] : '';
        header('Location: ../artista-form.php?action=' . $action . $id_param . '&error=' . $error);
    
    } finally {
        $conexion->close();
    }

} else {
    header('Location: ../dashboard.php');
    exit;
}
?>