<?php
// Conectamos a la base de datos
include 'conexion.php';

// AHORA RESPONDEMOS CON JSON
header('Content-Type: application/json');

// Verificamos que los datos lleguen por POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Recoger todas las variables del formulario
    $nombre = trim($_POST['cliente_nombre']);
    $apellido = trim($_POST['cliente_apellido']);
    $email = trim($_POST['cliente_email']);
    $telefono = trim($_POST['cliente_telefono']);
    $fecha_hora = $_POST['fecha_hora'];
    $tatuaje_descripcion = trim($_POST['tatuaje_descripcion']);
    $id_estilo = (int)$_POST['id_estilo'];
    $id_parte_cuerpo = (int)$_POST['id_parte_cuerpo'];

    // 2. Preparar la llamada al Procedimiento Almacenado
    // (Este SP ya maneja la lógica de cliente nuevo/existente y la transacción)
    $sql = "CALL sp_CrearCitaCliente(?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conexion->prepare($sql);
    
    if ($stmt === false) {
        // Error al preparar la consulta
        echo json_encode(['success' => false, 'message' => 'Error del servidor (prepare).']);
        exit;
    }

    // 3. Vincular los parámetros
    $stmt->bind_param("ssssssii", 
        $nombre, $apellido, $email, $telefono, 
        $fecha_hora, $tatuaje_descripcion, $id_estilo, $id_parte_cuerpo
    );

    // 4. Ejecutar la llamada
    if ($stmt->execute()) {
        // ¡ÉXITO!
        // La transacción (dentro del SP) se completó
        echo json_encode(['success' => true, 'message' => '¡Cita registrada con éxito!']);

    } else {
        // ¡FALLO!
        echo json_encode(['success' => false, 'message' => 'Error al guardar la cita en la BD.']);
    }

    $stmt->close();
    $conexion->close();

} else {
    // Si no es POST
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}
?>