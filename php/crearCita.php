<?php
include 'conexion.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Recoger datos
    $nombre = trim($_POST['cliente_nombre']);
    $apellido = trim($_POST['cliente_apellido']);
    $email = trim($_POST['cliente_email']);
    $telefono = trim($_POST['cliente_telefono']);
    $fecha_hora = $_POST['fecha_hora'];
    $tatuaje_descripcion = trim($_POST['tatuaje_descripcion']);
    $id_estilo = (int)$_POST['id_estilo'];
    $id_parte_cuerpo = (int)$_POST['id_parte_cuerpo'];
    $id_artista = (int)$_POST['id_artista'];

    // --- LÓGICA DE SUBIDA DE IMAGEN ---
    $nombre_imagen = null; // Por defecto null si no suben nada
    
    if (isset($_FILES['imagen_referencia']) && $_FILES['imagen_referencia']['error'] == 0) {
        $directorio = "../img/referencias/"; // Asegúrate de crear esta carpeta
        if (!is_dir($directorio)) {
            mkdir($directorio, 0777, true);
        }
        
        $extension = pathinfo($_FILES['imagen_referencia']['name'], PATHINFO_EXTENSION);
        // Generar nombre único para evitar colisiones
        $nombre_archivo = uniqid("ref_") . "." . $extension;
        $ruta_destino = $directorio . $nombre_archivo;
        
        // Mover el archivo
        if (move_uploaded_file($_FILES['imagen_referencia']['tmp_name'], $ruta_destino)) {
            $nombre_imagen = $nombre_archivo;
        }
    }
    // ----------------------------------

    // Llamar al SP (Ahora con 10 parámetros)
    $sql = "CALL sp_CrearCitaCliente(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    
    if ($stmt === false) {
        echo json_encode(['success' => false, 'message' => 'Error prepare: ' . $conexion->error]);
        exit;
    }

    // 'ssssssiiis' -> El último es string (la imagen)
    $stmt->bind_param("ssssssiiis", 
        $nombre, $apellido, $email, $telefono, 
        $fecha_hora, $tatuaje_descripcion, $id_estilo, $id_parte_cuerpo, 
        $id_artista, $nombre_imagen
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => '¡Cita registrada con éxito!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error BD: ' . $stmt->error]);
    }

    $stmt->close();
    $conexion->close();

} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
?>