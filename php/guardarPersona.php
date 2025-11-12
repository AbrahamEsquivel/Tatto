<?php
session_start();
// Ruta directa, ya que ambos están en la carpeta /php/
include 'conexion.php'; 

// 1. BÚNKER DE SEGURIDAD
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header('Location: ../login.html'); // Regresa al login
    exit;
}

// 2. VERIFICAR QUE SEA UN ENVÍO POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // 3. RECOGER DATOS DEL FORMULARIO
    // Sanitizamos los datos
    $id_persona = (int)$_POST['id_persona'];
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']); // Puede estar vacío

    // 4. VALIDACIÓN BÁSICA
    if (empty($id_persona) || empty($nombre) || empty($apellido) || empty($email)) {
        // Si faltan datos clave, lo regresamos al formulario con un error
        header('Location: ../persona-form.php?id=' . $id_persona . '&error=empty');
        exit;
    }

    // 5. EJECUTAR EL UPDATE (con Consultas Preparadas)
    try {
        $sql = "UPDATE persona SET 
                    nombre = ?, 
                    apellido = ?, 
                    email = ?, 
                    telefono = ? 
                WHERE id = ?";
        
        $stmt = $conexion->prepare($sql);
        // 'ssssi' -> string, string, string, string, int
        $stmt->bind_param("ssssi", 
            $nombre, 
            $apellido, 
            $email, 
            $telefono, 
            $id_persona
        );
        
        $stmt->execute();
        $stmt->close();
        $conexion->close();

        // 6. REDIRIGIR AL DIRECTORIO (ÉXITO)
        // Lo mandamos de vuelta al directorio.
        header('Location: ../directorio.php?success=edit');
        exit;

    } catch (mysqli_sql_exception $e) {
        // 7. SI ALGO FALLA (ej. email duplicado)
        $error_code = $e->getCode();
        $error_msg = urlencode($e->getMessage());
        
        if ($error_code == 1062) { // 1062 = Error de "Duplicate entry" (email repetido)
            header('Location: ../persona-form.php?id=' . $id_persona . '&error=email_duplicate');
        } else {
            // Otro error de BD
            header('Location: ../persona-form.php?id=' . $id_persona . '&error=' . $error_msg);
        }
        exit;
    }

} else {
    // Si no es POST, lo mandamos al dashboard
    header('Location: ../dashboard.php');
    exit;
}
?>