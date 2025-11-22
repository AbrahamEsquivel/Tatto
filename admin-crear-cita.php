<?php 
    include 'admin_header.php'; 
    include 'php/conexion.php';
    
    // 1. Cargar Artistas
    $artistas_lista = [];
    $sql_artistas = "SELECT id, nombre_artistico FROM artista WHERE active = 1 ORDER BY nombre_artistico";
    $r_artistas = $conexion->query($sql_artistas);
    if ($r_artistas) while($f = $r_artistas->fetch_assoc()) $artistas_lista[] = $f;

    // 2. Cargar Estilos
    $estilos_lista = [];
    $sql_estilos = "SELECT id, nombre FROM estilo_tatuaje ORDER BY nombre";
    $r_estilos = $conexion->query($sql_estilos);
    if ($r_estilos) while($f = $r_estilos->fetch_assoc()) $estilos_lista[] = $f;
    
    // 3. Cargar Partes del Cuerpo
    $partes_lista = [];
    $sql_partes = "SELECT id, nombre FROM parte_cuerpo ORDER BY nombre";
    $r_partes = $conexion->query($sql_partes);
    if ($r_partes) while($f = $r_partes->fetch_assoc()) $partes_lista[] = $f;

    // 4. BUSCAR EL ID DE "CONFIRMADA"
    // (En lugar de cargar todos, solo buscamos el ID que necesitamos)
    $id_confirmada = 2; // Valor por defecto (por si acaso)
    $sql_estado = "SELECT id FROM estado_cita WHERE nombre = 'Confirmada' LIMIT 1";
    $res_estado = $conexion->query($sql_estado);
    if ($res_estado && $row = $res_estado->fetch_assoc()) {
        $id_confirmada = $row['id'];
    }
    
    $conexion->close();
?>

<title>Registrar Cita (Presencial) - Admin</title>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .form-container-dark { 
        max-width: 800px; margin: 0 auto; background: #1a1a1a; 
        padding: 2rem 2.5rem; border-radius: 12px; border: 1px solid #333;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2); 
    }
    .form-container-dark h1 { text-align: center; margin-bottom: 2rem; color: #fff; font-weight: 300; }
    .form-container-dark h3 {
        color: #fff;
        font-weight: 500;
        border-bottom: 1px solid #333;
        padding-bottom: 10px;
        margin: 25px 0 15px 0;
    }
    .form-container-dark .form-group { margin-bottom: 1.25rem; }
    .form-container-dark .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #b0b0b0; }
    .form-container-dark .form-group input,
    .form-container-dark .form-group textarea,
    .form-container-dark .form-group select { 
        width: 100%; padding: 12px 15px; border: 1px solid #333; 
        border-radius: 6px; box-sizing: border-box; background-color: #111;
        color: #fff; font-size: 1rem;
    }
    
    /* Estilo para el icono del calendario en blanco */
    input[type="datetime-local"]::-webkit-calendar-picker-indicator {
        filter: invert(1);
        cursor: pointer;
    }
    
    /* Estilos de Error de Validación */
    .input-error-message {
        color: #EF4444; /* Rojo */
        font-size: 0.85rem;
        margin-top: 5px;
        display: none; 
    }
    .form-group.error input {
        border-color: #EF4444;
        box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2);
    }

    .form-container-dark .form-group small { color: #888; font-size: 0.8em; }
    .form-container-dark .btn { 
        width: 100%; padding: 12px; background: #3B82F6; /* Azul */ 
        color: white; border: none; border-radius: 6px; cursor: pointer; 
        font-size: 1.1rem; font-weight: 500; transition: background-color 0.3s ease;
    }
    .form-container-dark .btn:hover { background: #2563EB; }
    .form-container-dark .btn:disabled { background: #555; cursor: not-allowed; opacity: 0.7; }
    
    /* Estilo para mostrar el estado fijo */
    .estado-fijo {
        padding: 10px;
        background: rgba(34, 197, 94, 0.1);
        border: 1px solid #22C55E;
        color: #22C55E;
        border-radius: 6px;
        font-weight: bold;
        display: inline-block;
        width: 100%;
        box-sizing: border-box;
    }
</style>

<div class="form-container-dark">
    
    <form id="form-admin-cita" action="php/guardarAdminCita.php" method="POST">

        <h1><i class="fas fa-calendar-plus"></i> Registrar Cita (Presencial)</h1>
        
        <h3>Datos del Cliente</h3>
        <div class="form-group">
            <label for="cliente_nombre">Nombre(s):</label>
            <input type="text" id="cliente_nombre" name="cliente_nombre" required>
            <div class="input-error-message"></div>
        </div>
        <div class="form-group">
            <label for="cliente_apellido">Apellido(s):</label>
            <input type="text" id="cliente_apellido" name="cliente_apellido" required>
            <div class="input-error-message"></div>
        </div>
        <div class="form-group">
            <label for="cliente_email">Email:</label>
            <input type="email" id="cliente_email" name="cliente_email" required>
            <div class="input-error-message"></div>
            <small> (Si el email ya existe, se asociará al cliente existente)</small>
        </div>
         <div class="form-group">
            <label for="cliente_telefono">Teléfono:</label>
            <input type="tel" id="cliente_telefono" name="cliente_telefono">
            <div class="input-error-message"></div>
        </div>

        <hr style="border-color: #333; margin: 20px 0;">

        <h3>Detalles de la Cita y Tatuaje</h3>
        
        <div class="form-group">
            <label for="fecha_hora">Fecha y Hora de la Cita:</label>
            <input type="datetime-local" id="fecha_hora" name="fecha_hora" required onkeydown="return false">
        </div>
        
        <div class="form-group">
            <label for="id_artista">Artista Asignado:</label>
            <select id="id_artista" name="id_artista" required>
                <option value="">-- Selecciona un artista --</option>
                <?php foreach ($artistas_lista as $artista): ?>
                    <option value="<?php echo $artista['id']; ?>"><?php echo htmlspecialchars($artista['nombre_artistico']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="id_estilo">Estilo de Tatuaje:</label>
            <select id="id_estilo" name="id_estilo" required>
                <option value="">-- Selecciona un estilo --</option>
                <?php foreach ($estilos_lista as $estilo): ?>
                    <option value="<?php echo $estilo['id']; ?>"><?php echo htmlspecialchars($estilo['nombre']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="id_parte_cuerpo">Parte del Cuerpo:</label>
            <select id="id_parte_cuerpo" name="id_parte_cuerpo" required>
                <option value="">-- Selecciona una parte --</option>
                <?php foreach ($partes_lista as $parte): ?>
                    <option value="<?php echo $parte['id']; ?>"><?php echo htmlspecialchars($parte['nombre']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="tatuaje_descripcion">Descripción del Tatuaje:</label>
            <textarea id="tatuaje_descripcion" name="tatuaje_descripcion" rows="4" required></textarea>
        </div>

        <div class="form-group">
            <label for="precio_total">Precio Total Acordado (Opcional):</label>
            <input type="number" id="precio_total" name="precio_total" step="50" min="0">
        </div>

        <div class="form-group">
            <label>Estado Inicial:</label>
            <div class="estado-fijo">
                <i class="fas fa-check-circle"></i> Confirmada
            </div>
            <input type="hidden" name="id_estado_cita" value="<?php echo $id_confirmada; ?>">
        </div>
        <button type="submit" id="btn-registrar" class="btn">Registrar Cita</button>
    </form>
</div>

<script src="js/admin-crear-cita.js"></script>

<?php
    include 'admin_footer.php';
?>