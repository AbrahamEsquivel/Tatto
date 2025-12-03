<?php
    include 'admin_header.php'; 
    include 'php/conexion.php'; 

    // 1. DEFINIR VARIABLES POR DEFECTO
    $id_pago = null;
    $monto = '';
    $is_readonly = '';
    $fecha_pago = date('Y-m-d');
    $id_tipo_pago_sel = null;
    $id_metodo_pago_sel = null;
    $titulo_pagina = "Registrar Nuevo Pago";
    $cita = null;
    $is_readonly = ''; 
    $is_disabled = ''; 
    $modo_edicion_pago = false;
    
    // Variables para el desglose
    $precio_total = 0;
    $total_pagado = 0;
    $restante = 0;

    // 2. BUSCAR DATOS DE LA CITA (MODO CREAR)
    if (isset($_GET['id_cita'])) {
        $id_cita = (int)$_GET['id_cita'];
        
        $stmt_cita = $conexion->prepare("SELECT * FROM v_AgendaCompleta WHERE id_cita = ?");
        $stmt_cita->bind_param("i", $id_cita);
        $stmt_cita->execute();
        $cita = $stmt_cita->get_result()->fetch_assoc();
        $stmt_cita->close();
        
        if ($cita) {
            $precio_total = (float)$cita['precio_total'];
            $total_pagado = (float)$cita['total_pagado'];
            
            // Calculamos lo que falta
            $restante = $precio_total - $total_pagado;
            
            // Por defecto, sugerimos pagar TODO lo que falta
            // (Si el restante es negativo o 0, sugerimos 0)
            $monto = $restante > 0 ? $restante : 0;
        }
        
    } 
    // 3. BUSCAR DATOS DEL PAGO (MODO EDITAR)
   else if (isset($_GET['id_pago'])) {
        $id_pago = (int)$_GET['id_pago'];
        $titulo_pagina = "Editar Pago #$id_pago";
        $modo_edicion_pago = true;
        
        // CONFIGURACIÓN DE BLOQUEO:
        $is_readonly = 'readonly';   // Monto: BLOQUEADO
        $is_disabled = 'disabled';

        $stmt_pago = $conexion->prepare("SELECT * FROM pago WHERE id = ?");
        $stmt_pago->bind_param("i", $id_pago);
        $stmt_pago->execute();
        $pago_existente = $stmt_pago->get_result()->fetch_assoc();
        $stmt_pago->close();

        $id_cita = $pago_existente['id_cita'];
        $monto = $pago_existente['monto']; // En edición, respetamos el monto original
        $fecha_pago = $pago_existente['fecha_pago'];
        $id_tipo_pago_sel = $pago_existente['id_tipo_pago'];
        $id_metodo_pago_sel = $pago_existente['id_metodo_pago'];

        // Traemos datos de la cita para mostrar info
        $stmt_cita = $conexion->prepare("SELECT * FROM v_AgendaCompleta WHERE id_cita = ?");
        $stmt_cita->bind_param("i", $id_cita);
        $stmt_cita->execute();
        $cita = $stmt_cita->get_result()->fetch_assoc();
        $stmt_cita->close();
        
        if ($cita) {
            $precio_total = (float)$cita['precio_total'];
            $total_pagado = (float)$cita['total_pagado'];
            // En edición, el 'restante' visual es el cálculo actual
            $restante = $precio_total - $total_pagado; 
        }

    } else {
        echo "<h1>Error: Faltan datos.</h1>";
        include 'admin_footer.php';
        exit;
    }

    if (!$cita) {
        echo "<h1>Error: Cita no encontrada.</h1>";
        include 'admin_footer.php';
        exit;
    }

    // 4. CARGAR CATÁLOGOS
    $tipos_pago = $conexion->query("SELECT * FROM tipo_pago");
    $metodos_pago = $conexion->query("SELECT * FROM metodo_pago");
    $conexion->close();
?>

<title><?php echo $titulo_pagina; ?> - Admin</title>

<style>
.admin-content { padding: 30px; background-color: #0f0f0f; min-height: 100vh; }
.form-container { max-width: 600px; margin: 0 auto; background: #1a1a1a; padding: 2.5rem; border-radius: 16px; border: 1px solid #333; box-shadow: 0 8px 32px rgba(0,0,0,0.2); }
.form-container h1 { text-align: center; margin-bottom: 2rem; color: #fff; font-weight: 300; font-size: 1.8rem; }

.cita-info-box { background: #111; border: 1px solid #333; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; }
.cita-info-box h3 { color: #fff; margin-top: 0; margin-bottom: 1rem; font-size: 1.2rem; font-weight: 400; display: flex; align-items: center; gap: 10px; }
.cita-info-box p { margin: 8px 0; color: #e0e0e0; font-size: 0.95rem; line-height: 1.5; word-wrap: break-word; }
.cita-info-box strong { color: #fff; }

/* Estilos para el desglose de dinero */
.money-row { display: flex; justify-content: space-between; border-top: 1px solid #333; margin-top: 10px; padding-top: 10px; }
.money-label { color: #888; }
.money-value { font-weight: bold; font-size: 1.1rem; }
.text-green { color: #22C55E; }
.text-blue { color: #3B82F6; }
.text-red { color: #EF4444; }

.form-section { margin-bottom: 2rem; }
.form-section h3 { color: #fff; margin-bottom: 1.5rem; font-size: 1.3rem; font-weight: 400; display: flex; align-items: center; gap: 10px; }
.form-group { margin-bottom: 1.5rem; }
.form-group label { display: block; margin-bottom: 8px; color: #fff; font-weight: 500; font-size: 0.95rem; }
.form-group input, .form-group select { width: 100%; padding: 12px 16px; background: #0f0f0f; border: 1px solid #333; border-radius: 8px; color: #e0e0e0; font-size: 1rem; transition: all 0.3s ease; }
input[type="date"]::-webkit-calendar-picker-indicator { filter: invert(1); cursor: pointer; }
.form-group input:focus, .form-group select:focus { outline: none; border-color: #fff; box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.1); }
.btn { width: 100%; padding: 14px; background: #fff; color: #000; border: none; border-radius: 8px; cursor: pointer; font-size: 1rem; font-weight: 500; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 8px; }
.btn:hover { background: #e0e0e0; transform: translateY(-1px); }
.form-divider { height: 1px; background: #333; margin: 2rem 0; }
.error-box { background: rgba(239,68,68,0.1); border: 1px solid #EF4444; color: #EF4444; padding: 15px; border-radius: 6px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-weight: 500; }

@media (max-width: 768px) { .admin-content { padding: 20px 15px; } .form-container { padding: 2rem 1.5rem; } }
</style>

<div class="admin-content">
    <div class="form-container">
        <form id="form-pago" action="php/guardarPago.php" method="POST">
            <h1><?php echo $titulo_pagina; ?></h1>

            <?php if (isset($_GET['error'])): ?>
                <div class="error-box">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars(urldecode($_GET['error'])); ?></span>
                </div>
            <?php endif; ?>

            <input type="hidden" name="id_cita" value="<?php echo $id_cita; ?>">
            <?php if ($id_pago): ?>
                <input type="hidden" name="id_pago" value="<?php echo $id_pago; ?>">
            <?php endif; ?>

            <div class="cita-info-box">
                <h3><i class="fas fa-calendar-alt"></i> Cita Asociada</h3>
                <p><strong>ID:</strong> <?php echo $cita['id_cita']; ?> | <strong>Cliente:</strong> <?php echo $cita['cliente_nombre'] . ' ' . $cita['cliente_apellido']; ?></p>
                <p>
                    <strong>Tatuaje:</strong> 
                    <?php 
                        $desc = $cita['tatuaje_descripcion'];
                        echo htmlspecialchars(strlen($desc) > 60 ? substr($desc, 0, 60) . '...' : $desc); 
                    ?>
                </p>

                <div class="money-row">
                    <span class="money-label">Precio Total:</span>
                    <span class="money-value">$<?php echo number_format($precio_total, 2); ?></span>
                </div>
                <div class="money-row">
                    <span class="money-label">Ya Abonado:</span>
                    <span class="money-value text-blue">$<?php echo number_format($total_pagado, 2); ?></span>
                </div>
                <div class="money-row" style="border-top: 1px dashed #444;">
                    <span class="money-label">Restante por Pagar:</span>
                    <span class="money-value text-green">$<?php echo number_format($restante, 2); ?></span>
                </div>
                </div>

            <div class="form-divider"></div>

            <div class="form-section">
                <h3><i class="fas fa-money-bill-wave"></i> Detalles del Pago</h3>
                
                <div class="form-group">
                    <label for="monto">Monto a Pagar (MXN):</label>
                    <input type="number" id="monto" name="monto" step="0.01" min="0" 
                           value="<?php echo $monto; ?>" required <?php echo $is_readonly; ?>>
                    <small style="color: #666;">Se ha sugerido el monto restante automáticamente.</small>
                </div>

                <div class="form-group">
                    <label for="fecha_pago">Fecha del Pago:</label>
                    <input type="date" id="fecha_pago" name="fecha_pago" 
                           value="<?php echo $fecha_pago; ?>" 
                           min="<?php echo date('Y-m-d'); ?>"
                           required onkeydown="return false">
                </div>

                <div class="form-group">
                    <label for="id_tipo_pago">Tipo de Pago:</label>
                    <select id="id_tipo_pago" name="id_tipo_pago" required <?php echo $is_disabled; ?>>
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
            
            <?php if ($modo_edicion_pago): ?>
                <input type="hidden" name="id_tipo_pago" value="<?php echo $id_tipo_pago_sel; ?>">
            <?php endif; ?>

            <button type="submit" class="btn">
                <i class="fas fa-save"></i> Guardar Pago
            </button>
        </form>
    </div>
</div>

<?php
    include 'admin_footer.php';
?>