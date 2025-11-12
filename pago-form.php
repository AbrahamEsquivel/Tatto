<?php
    include 'admin_header.php'; 
    include 'php/conexion.php'; 

    // 1. DEFINIR VARIABLES POR DEFECTO (MODO "CREAR")
    $id_pago = null;
    $monto = '';
    $is_readonly = '';
    $fecha_pago = date('Y-m-d'); // Fecha de hoy por defecto
    $id_tipo_pago_sel = null;
    $id_metodo_pago_sel = null;
    $titulo_pagina = "Registrar Nuevo Pago";

    // 2. BUSCAR DATOS DE LA CITA (viene de ?id_cita=...)
    if (isset($_GET['id_cita'])) {
        $id_cita = (int)$_GET['id_cita'];
        
        // Buscamos datos de la cita para mostrarlos
        $stmt_cita = $conexion->prepare("SELECT * FROM v_AgendaCompleta WHERE id_cita = ?");
        $stmt_cita->bind_param("i", $id_cita);
        $stmt_cita->execute();
        $cita = $stmt_cita->get_result()->fetch_assoc();
        $stmt_cita->close();
        if (isset($_GET['monto']) && !empty($_GET['monto'])) {
            $monto = (float)$_GET['monto'];
            $is_readonly = 'readonly';
        }
        
    } 
    // 3. BUSCAR DATOS DEL PAGO (MODO "EDITAR") (viene de ?id_pago=...)
    else if (isset($_GET['id_pago'])) {
        $id_pago = (int)$_GET['id_pago'];
        $titulo_pagina = "Editar Pago #$id_pago";

        // Buscamos el pago existente
        $stmt_pago = $conexion->prepare("SELECT * FROM pago WHERE id = ?");
        $stmt_pago->bind_param("i", $id_pago);
        $stmt_pago->execute();
        $pago_existente = $stmt_pago->get_result()->fetch_assoc();
        $stmt_pago->close();

        // Rellenamos las variables con los datos de la BD
        $id_cita = $pago_existente['id_cita'];
        $monto = $pago_existente['monto'];
        $fecha_pago = $pago_existente['fecha_pago'];
        $id_tipo_pago_sel = $pago_existente['id_tipo_pago'];
        $id_metodo_pago_sel = $pago_existente['id_metodo_pago'];

        // Buscamos los datos de la cita (igual que antes)
        $stmt_cita = $conexion->prepare("SELECT * FROM v_AgendaCompleta WHERE id_cita = ?");
        $stmt_cita->bind_param("i", $id_cita);
        $stmt_cita->execute();
        $cita = $stmt_cita->get_result()->fetch_assoc();
        $stmt_cita->close();

    } else {
        // Si no hay id_cita ni id_pago, no podemos continuar
        echo "<h1>Error: No se especificó una cita o un pago.</h1>";
        include 'admin_footer.php';
        exit;
    }

    // 4. CARGAR LOS CATÁLOGOS (Dropdowns)
    $tipos_pago = $conexion->query("SELECT * FROM tipo_pago");
    $metodos_pago = $conexion->query("SELECT * FROM metodo_pago");
    
    $conexion->close();
?>

<title><?php echo $titulo_pagina; ?> - Admin</title>

<style>
    .form-container { 
        max-width: 800px; 
        margin: 0 auto;
        background: #fff; 
        padding: 2rem; 
        border-radius: 10px; 
        box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
    }
    .form-container h1 { text-align: center; margin-bottom: 1.5rem; }
    .form-container .form-group { margin-bottom: 1rem; }
    .form-container .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
    .form-container .form-group input,
    .form-container .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
    .form-container .form-group input:disabled { background: #eee; }
    .form-container .btn { width: 100%; padding: 10px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
    .form-container .btn:hover { background: #218838; }

    /* Info de la cita (no editable) */
    .cita-info-box {
        background: #F9FAFB;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }
    .cita-info-box h3 { margin-top: 0; color: #1F2937; }
    .cita-info-box p { margin: 5px 0; color: #374151; }
    .form-container .form-group input[readonly] {
    background-color: #eee; /* Color gris */
    color: #555;
    cursor: not-allowed;
}
</style>

<div class="form-container">
    
    <form id="form-pago" action="php/guardarPago.php" method="POST">

        <h1><?php echo $titulo_pagina; ?></h1>

        <input type="hidden" name="id_cita" value="<?php echo $id_cita; ?>">
        <?php if ($id_pago): // Si estamos en modo EDITAR, enviamos el id_pago ?>
            <input type="hidden" name="id_pago" value="<?php echo $id_pago; ?>">
        <?php endif; ?>

        <div class="cita-info-box">
            <h3>Cita Asociada</h3>
            <p><strong>Cita ID:</strong> <?php echo $cita['id_cita']; ?></p>
            <p><strong>Cliente:</strong> <?php echo $cita['cliente_nombre'] . ' ' . $cita['cliente_apellido']; ?></p>
            <p><strong>Tatuaje:</strong> <?php echo htmlspecialchars($cita['tatuaje_descripcion']); ?></p>
            <p><strong>Precio Total:</strong> $<?php echo number_format($cita['precio_total'], 2); ?></p>
        </div>

        <hr style="margin: 20px 0;">

        <h3>Detalles del Pago</h3>
        <div class="form-group">
            <label for="monto">Monto (MXN):</label>
            <input type="number" id="monto" name="monto" step="0.01" min="0" value="<?php echo $monto; ?>" required <?php echo $is_readonly; ?>>
        <div class="form-group">
            <label for="fecha_pago">Fecha del Pago:</label>
            <input type="date" id="fecha_pago" name="fecha_pago" value="<?php echo $fecha_pago; ?>" required>
        </div>
        <div class="form-group">
            <label for="id_tipo_pago">Tipo de Pago:</label>
            <select id="id_tipo_pago" name="id_tipo_pago" required>
                <option value="">-- Selecciona un tipo --</option>
                <?php while($row = $tipos_pago->fetch_assoc()): ?>
                    <option value="<?php echo $row['id']; ?>" <?php if($row['id'] == $id_tipo_pago_sel) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($row['nombre']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="id_metodo_pago">Método de Pago:</label>
            <select id="id_metodo_pago" name="id_metodo_pago" required>
                <option value="">-- Selecciona un método --</option>
                <?php while($row = $metodos_pago->fetch_assoc()): ?>
                    <option value="<?php echo $row['id']; ?>" <?php if($row['id'] == $id_metodo_pago_sel) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($row['nombre']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <button type="submit" class="btn">Guardar Pago</button>
    </form>
</div>

<?php
    include 'admin_footer.php';
?>