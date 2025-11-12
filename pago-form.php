<?php
    include 'admin_header.php'; 
    include 'php/conexion.php'; 

    // 1. DEFINIR VARIABLES POR DEFECTO (MODO "CREAR")
    $id_pago = null;
    $monto = '';
    $is_readonly = '';
    $fecha_pago = date('Y-m-d');
    $id_tipo_pago_sel = null;
    $id_metodo_pago_sel = null;
    $titulo_pagina = "Registrar Nuevo Pago";

    // 2. BUSCAR DATOS DE LA CITA
    if (isset($_GET['id_cita'])) {
        $id_cita = (int)$_GET['id_cita'];
        
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
    // 3. BUSCAR DATOS DEL PAGO (MODO "EDITAR")
    else if (isset($_GET['id_pago'])) {
        $id_pago = (int)$_GET['id_pago'];
        $titulo_pagina = "Editar Pago #$id_pago";

        $stmt_pago = $conexion->prepare("SELECT * FROM pago WHERE id = ?");
        $stmt_pago->bind_param("i", $id_pago);
        $stmt_pago->execute();
        $pago_existente = $stmt_pago->get_result()->fetch_assoc();
        $stmt_pago->close();

        $id_cita = $pago_existente['id_cita'];
        $monto = $pago_existente['monto'];
        $fecha_pago = $pago_existente['fecha_pago'];
        $id_tipo_pago_sel = $pago_existente['id_tipo_pago'];
        $id_metodo_pago_sel = $pago_existente['id_metodo_pago'];

        $stmt_cita = $conexion->prepare("SELECT * FROM v_AgendaCompleta WHERE id_cita = ?");
        $stmt_cita->bind_param("i", $id_cita);
        $stmt_cita->execute();
        $cita = $stmt_cita->get_result()->fetch_assoc();
        $stmt_cita->close();

    } else {
        echo "<h1>Error: No se especificó una cita o un pago.</h1>";
        include 'admin_footer.php';
        exit;
    }

    // 4. CARGAR LOS CATÁLOGOS
    $tipos_pago = $conexion->query("SELECT * FROM tipo_pago");
    $metodos_pago = $conexion->query("SELECT * FROM metodo_pago");
    
    $conexion->close();
?>

<title><?php echo $titulo_pagina; ?> - Admin</title>

<style>
.admin-content {
    padding: 30px;
    background-color: #0f0f0f;
    min-height: 100vh;
}

.form-container {
    max-width: 600px;
    margin: 0 auto;
    background: #1a1a1a;
    padding: 2.5rem;
    border-radius: 16px;
    border: 1px solid #333;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
}

.form-container h1 {
    text-align: center;
    margin-bottom: 2rem;
    color: #fff;
    font-weight: 300;
    font-size: 1.8rem;
    letter-spacing: 0.5px;
}

.cita-info-box {
    background: #111;
    border: 1px solid #333;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.cita-info-box h3 {
    color: #fff;
    margin-top: 0;
    margin-bottom: 1rem;
    font-size: 1.2rem;
    font-weight: 400;
    display: flex;
    align-items: center;
    gap: 10px;
}

.cita-info-box p {
    margin: 8px 0;
    color: #e0e0e0;
    font-size: 0.95rem;
}

.cita-info-box strong {
    color: #fff;
}

.form-section {
    margin-bottom: 2rem;
}

.form-section h3 {
    color: #fff;
    margin-bottom: 1.5rem;
    font-size: 1.3rem;
    font-weight: 400;
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #fff;
    font-weight: 500;
    font-size: 0.95rem;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 12px 16px;
    background: #0f0f0f;
    border: 1px solid #333;
    border-radius: 8px;
    color: #e0e0e0;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #fff;
    box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.1);
}

.form-group input:hover,
.form-group select:hover {
    border-color: #555;
}

.form-group input:disabled {
    background: #1a1a1a;
    color: #888;
    border-color: #333;
    cursor: not-allowed;
}

.form-group input[readonly] {
    background: #1a1a1a;
    color: #888;
    border-color: #333;
}

.btn {
    width: 100%;
    padding: 14px;
    background: #fff;
    color: #000;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 500;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn:hover {
    background: #e0e0e0;
    transform: translateY(-1px);
}

.form-divider {
    height: 1px;
    background: #333;
    margin: 2rem 0;
}

@media (max-width: 768px) {
    .admin-content {
        padding: 20px 15px;
    }
    
    .form-container {
        padding: 2rem 1.5rem;
    }
    
    .cita-info-box {
        padding: 1.25rem;
    }
}

@media (max-width: 480px) {
    .form-container {
        padding: 1.5rem 1rem;
    }
    
    .form-container h1 {
        font-size: 1.5rem;
    }
    
    .cita-info-box {
        padding: 1rem;
    }
}
</style>

<div class="admin-content">
    <div class="form-container">
        <form id="form-pago" action="php/guardarPago.php" method="POST">
            <h1><?php echo $titulo_pagina; ?></h1>

            <input type="hidden" name="id_cita" value="<?php echo $id_cita; ?>">
            <?php if ($id_pago): ?>
                <input type="hidden" name="id_pago" value="<?php echo $id_pago; ?>">
            <?php endif; ?>

            <div class="cita-info-box">
                <h3><i class="ri-calendar-line"></i> Cita Asociada</h3>
                <p><strong>Cita ID:</strong> <?php echo $cita['id_cita']; ?></p>
                <p><strong>Cliente:</strong> <?php echo $cita['cliente_nombre'] . ' ' . $cita['cliente_apellido']; ?></p>
                <p><strong>Tatuaje:</strong> <?php echo htmlspecialchars($cita['tatuaje_descripcion']); ?></p>
                <p><strong>Precio Total:</strong> $<?php echo number_format($cita['precio_total'], 2); ?></p>
            </div>

            <div class="form-divider"></div>

            <div class="form-section">
                <h3><i class="ri-money-dollar-circle-line"></i> Detalles del Pago</h3>
                
                <div class="form-group">
                    <label for="monto">Monto (MXN):</label>
                    <input type="number" id="monto" name="monto" step="0.01" min="0" value="<?php echo $monto; ?>" required <?php echo $is_readonly; ?> placeholder="0.00">
                </div>

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
            </div>

            <button type="submit" class="btn">
                <i class="ri-save-line"></i> Guardar Pago
            </button>
        </form>
    </div>
</div>

<?php
    include 'admin_footer.php';
?>