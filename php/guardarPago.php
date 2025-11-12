<?php
session_start();
include 'conexion.php'; // Conexión a la BD (ruta directa)

// 1. BÚNKER DE SEGURIDAD
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header('Location: ../login.html');
    exit;
}

// 2. VERIFICAR QUE SEA UN ENVÍO POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // 3. RECOGER TODOS LOS DATOS DEL FORMULARIO
    // Sanitizamos los datos
    $id_cita = (int)$_POST['id_cita'];
    $monto = (float)$_POST['monto'];
    $fecha_pago = $_POST['fecha_pago'];
    $id_tipo_pago = (int)$_POST['id_tipo_pago'];
    $id_metodo_pago = (int)$_POST['id_metodo_pago'];

    // 4. LA LÓGICA "INTELIGENTE": ¿ES CREAR O EDITAR?
    // Verificamos si nos enviaron un id_pago.
    $id_pago = isset($_POST['id_pago']) ? (int)$_POST['id_pago'] : null;

    try {
        if ($id_pago) {
            // --- MODO UPDATE (EDITAR) ---
            // Si $id_pago tiene un valor, actualizamos ese registro
            
            $sql = "UPDATE pago SET 
                        monto = ?, 
                        fecha_pago = ?, 
                        id_tipo_pago = ?, 
                        id_metodo_pago = ? 
                    WHERE id = ?";
            
            $stmt = $conexion->prepare($sql);
            // 'dsiii' -> double, string, int, int, int
            $stmt->bind_param("dsiii", 
                $monto, 
                $fecha_pago, 
                $id_tipo_pago, 
                $id_metodo_pago, 
                $id_pago
            );

        } else {
            // --- MODO INSERT (CREAR) ---
            // Si $id_pago es null, creamos un registro nuevo
            
            $sql = "INSERT INTO pago (id_cita, monto, fecha_pago, id_tipo_pago, id_metodo_pago) 
                    VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $conexion->prepare($sql);
            // 'idsii' -> int, double, string, int, int
            $stmt->bind_param("idsii", 
                $id_cita, 
                $monto, 
                $fecha_pago, 
                $id_tipo_pago, 
                $id_metodo_pago
            );
        }

        // 5. EJECUTAR LA CONSULTA (sea INSERT o UPDATE)
        $stmt->execute();
        $stmt->close();
        $conexion->close();

        // 6. REDIRIGIR DE VUELTA AL HISTORIAL (con un mensaje de éxito)
        header('Location: ../historial-pagos.php?success=1');
        exit;

    } catch (Exception $e) {
        // 7. SI ALGO FALLA (Error de BD)
        // (En un futuro, podemos guardar $e->getMessage() en una sesión para mostrarlo)
        $error_query_param = urlencode($e->getMessage());
        header('Location: ../historial-pagos.php?error=' . $error_query_param);
        exit;
    }

} else {
    // Si no es POST, los regresamos
    header('Location: ../dashboard.php');
    exit;
}
?>