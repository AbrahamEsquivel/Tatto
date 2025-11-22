<?php
session_start();
include 'conexion.php'; 

// 1. BÚNKER DE SEGURIDAD
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header('Location: ../login.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // 3. RECOGER DATOS
    $id_cita = (int)$_POST['id_cita'];
    $monto = (float)$_POST['monto'];
    $fecha_pago = $_POST['fecha_pago'];
    $id_tipo_pago = (int)$_POST['id_tipo_pago'];
    $id_metodo_pago = (int)$_POST['id_metodo_pago'];
    $id_pago = isset($_POST['id_pago']) ? (int)$_POST['id_pago'] : null;

    try {
        // --- ⬇️ VALIDACIÓN DE MONTO MÁXIMO ⬇️ ---
        
        // A. Obtener el Precio Total Acordado
        $stmt_precio = $conexion->prepare("SELECT t.precio_total FROM cita c JOIN tatuaje t ON c.id_tatuaje = t.id WHERE c.id = ?");
        $stmt_precio->bind_param("i", $id_cita);
        $stmt_precio->execute();
        $res_precio = $stmt_precio->get_result()->fetch_assoc();
        $precio_total = (float)$res_precio['precio_total'];
        $stmt_precio->close();

        if ($precio_total > 0) {
            // B. Calcular cuánto se ha pagado YA (excluyendo el pago actual si estamos editando)
            $sql_pagado = "SELECT IFNULL(SUM(monto), 0) as total FROM pago WHERE id_cita = ?";
            if ($id_pago) { 
                $sql_pagado .= " AND id != ?"; // Si edito, no sumo mi propio monto viejo
            }
            
            $stmt_pagado = $conexion->prepare($sql_pagado);
            if ($id_pago) {
                $stmt_pagado->bind_param("ii", $id_cita, $id_pago);
            } else {
                $stmt_pagado->bind_param("i", $id_cita);
            }
            $stmt_pagado->execute();
            $res_pagado = $stmt_pagado->get_result()->fetch_assoc();
            $pagado_previo = (float)$res_pagado['total'];
            $stmt_pagado->close();

            // C. Validar la suma
            $nuevo_total_acumulado = $pagado_previo + $monto;
            
            // Usamos una pequeña tolerancia de 0.01
            if ($nuevo_total_acumulado > ($precio_total + 0.01)) {
                $sobrante = $nuevo_total_acumulado - $precio_total;
                // Lanzamos el error para detener el guardado
                throw new Exception("Error: Este pago excede el precio total ($$precio_total). Estás cobrando $$sobrante de más.");
            }
        }
        // --- ⬆️ FIN VALIDACIÓN ⬆️ ---


        // 4. GUARDAR (Si pasó la validación)
        if ($id_pago) {
            // UPDATE
            $sql = "UPDATE pago SET monto=?, fecha_pago=?, id_tipo_pago=?, id_metodo_pago=? WHERE id=?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("dsiii", $monto, $fecha_pago, $id_tipo_pago, $id_metodo_pago, $id_pago);
        } else {
            // INSERT
            $sql = "INSERT INTO pago (id_cita, monto, fecha_pago, id_tipo_pago, id_metodo_pago) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("idsii", $id_cita, $monto, $fecha_pago, $id_tipo_pago, $id_metodo_pago);
        }

        $stmt->execute();
        $stmt->close();
        $conexion->close();

        // Éxito
        header('Location: ../historial-pagos.php?success=1');
        exit;

    } catch (Exception $e) {
        // 7. MANEJO DE ERRORES
        // Redirigimos AL FORMULARIO (no al historial) para que el usuario corrija
        $error_msg = urlencode($e->getMessage());
        
        // Armamos la URL de regreso correcta (Crear o Editar)
        if ($id_pago) {
            header('Location: ../pago-form.php?id_pago=' . $id_pago . '&error=' . $error_msg);
        } else {
            // Si falló al crear, necesitamos devolver el id_cita y el monto que intentó poner
            header('Location: ../pago-form.php?id_cita=' . $id_cita . '&monto=' . $monto . '&error=' . $error_msg);
        }
        exit;
    }

} else {
    header('Location: ../dashboard.php');
    exit;
}
?>