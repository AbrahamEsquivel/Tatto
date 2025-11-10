<?php
// Conectamos a la base de datos
include 'conexion.php';

// Verificamos que los datos lleguen por POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Recoger todas las variables del formulario
    // (Añadimos saneamiento básico para seguridad)
    $nombre = trim($_POST['cliente_nombre']);
    $apellido = trim($_POST['cliente_apellido']);
    $email = trim($_POST['cliente_email']);
    $telefono = trim($_POST['cliente_telefono']);
    $fecha_hora = $_POST['fecha_hora'];
    $tatuaje_descripcion = trim($_POST['tatuaje_descripcion']);
    $id_estilo = (int)$_POST['id_estilo'];
    $id_parte_cuerpo = (int)$_POST['id_parte_cuerpo'];

    // 2. Preparar la llamada al Procedimiento Almacenado
    // (Usamos consultas preparadas para máxima seguridad contra Inyección SQL)
    $sql = "CALL sp_CrearCitaCliente(?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conexion->prepare($sql);
    
    if ($stmt === false) {
        // Error al preparar la consulta
        header('Location: ../agendar.php?error=sql_prepare');
        exit;
    }

    // 3. Vincular los parámetros
    // s = string, i = integer
    $stmt->bind_param("ssssssii", 
        $nombre, 
        $apellido, 
        $email, 
        $telefono, 
        $fecha_hora, 
        $tatuaje_descripcion, 
        $id_estilo, 
        $id_parte_cuerpo
    );

    // 4. Ejecutar la llamada
    if ($stmt->execute()) {
        // ¡ÉXITO!
        // La transacción (dentro del SP) se completó
        
        // Redirigimos a una página de "gracias"
        header('Location: ../gracias.html');
        exit;

    } else {
        // ¡FALLO!
        // El SP manejó el ROLLBACK, así que la BD está a salvo.
        // Redirigimos de vuelta al formulario con un error.
        header('Location: ../agendar.php?error=execute_fail');
        exit;
    }

    $stmt->close();
    $conexion->close();

} else {
    // Si no es POST, los regresamos
    header('Location: ../agendar.php');
    exit;
}
?>