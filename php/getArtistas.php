<?php
session_start();
include 'conexion.php'; 
header('Content-Type: application/json');

// 1. BÚNKER DE SEGURIDAD
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit;
}

$response = [
    'success' => false,
    'artistas' => []
];

// 2. LA CONSULTA SQL
// Unimos Artista con Persona para obtener todos los datos
try {
    $sql = "SELECT
                a.id AS id_artista,
                a.nombre_artistico,
                a.active,
                p.nombre,
                p.apellido,
                p.email,
                p.telefono,
                p.id AS id_persona
            FROM artista AS a
            JOIN persona AS p ON a.id_persona = p.id
            ORDER BY a.nombre_artistico ASC";
    
    $resultado = $conexion->query($sql);

    if ($resultado) {
        while ($fila = $resultado->fetch_assoc()) {
            $response['artistas'][] = $fila;
        }
        $response['success'] = true;
    } else {
        throw new Exception("Error al consultar la base de datos: " . $conexion->error);
    }
    
    $conexion->close();

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// 3. ENVIAMOS LA RESPUESTA
echo json_encode($response);
?>