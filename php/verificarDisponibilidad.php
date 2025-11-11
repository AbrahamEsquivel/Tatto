<?php
include 'conexion.php';
header('Content-Type: application/json');

// 1. Verificamos que nos hayan enviado la fecha por POST
if (!isset($_POST['fecha_hora'])) {
    echo json_encode(['disponible' => false, 'message' => 'No se recibió fecha.']);
    exit;
}

$fecha_seleccionada = $_POST['fecha_hora'];

// 2. Definimos el "intervalo de bloqueo" (1 hora antes y 1 hora después)
// Usaremos 59 minutos para no chocar con la hora en punto
$intervalo_minutos = 59;

try {
    // 3. CONSULTA CLAVE:
    // Contamos cuántas citas ACTIVAS ('Pendiente' o 'Confirmada')
    // existen dentro de la ventana de tiempo (aprox. 2 horas).
    $sql = "SELECT COUNT(*) AS conflictos
            FROM v_AgendaCompleta
            WHERE 
                estado_cita IN ('Pendiente', 'Confirmada')
                AND fecha_hora > DATE_SUB(?, INTERVAL ? MINUTE)
                AND fecha_hora < DATE_ADD(?, INTERVAL ? MINUTE)";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param(
        "sisi", // s: string (fecha), i: int (minutos), s: string, i: int
        $fecha_seleccionada,
        $intervalo_minutos,
        $fecha_seleccionada,
        $intervalo_minutos
    );

    $stmt->execute();
    $resultado = $stmt->get_result();
    $fila = $resultado->fetch_assoc();
    
    $conflictos = (int)$fila['conflictos'];

    // 4. Devolvemos la respuesta
    if ($conflictos == 0) {
        // ¡No hay conflictos! La hora está disponible.
        echo json_encode(['disponible' => true]);
    } else {
        // ¡Conflicto! La hora no está disponible.
        echo json_encode(['disponible' => false, 'message' => 'Este horario no está disponible.']);
    }

    $stmt->close();
    $conexion->close();

} catch (Exception $e) {
    echo json_encode(['disponible' => false, 'message' => $e->getMessage()]);
}
?>