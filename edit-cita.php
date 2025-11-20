<?php
    include 'admin_header.php'; 
    include 'php/conexion.php';

    if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
        echo "<h1>Error: ID de cita no válido.</h1>";
        include 'admin_footer.php';
        exit;
    }
    $id_cita_a_editar = $_GET['id'];

    $sql = "SELECT * FROM v_AgendaCompleta WHERE id_cita = ? LIMIT 1";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_cita_a_editar);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 0) {
        echo "<h1>Error: Cita no encontrada.</h1>";
        include 'admin_footer.php';
        exit;
    }

    $cita = $resultado->fetch_assoc();
    $stmt->close();
    
    $estados = $conexion->query("SELECT id, nombre FROM estado_cita ORDER BY nombre");
    $estilos_tatuaje = $conexion->query("SELECT id, nombre FROM estilo_tatuaje ORDER BY nombre");

    $id_artista_asignado = $cita['id_artista'];
    $sql_artistas = "
        (SELECT id, nombre_artistico FROM artista WHERE active = 1)
        UNION
        (SELECT id, nombre_artistico FROM artista WHERE id = ?)
        ORDER BY nombre_artistico
    ";
    
    $stmt_artistas = $conexion->prepare($sql_artistas);
    $stmt_artistas->bind_param("i", $id_artista_asignado);
    $stmt_artistas->execute();
    $artistas = $stmt_artistas->get_result();
    $conexion->close();
?>

<title>Editar Cita ID: <?php echo $cita['id_cita']; ?></title>

<style>
.admin-content {
    padding: 30px;
    background-color: #0f0f0f;
    min-height: 100vh;
    margin-left: 200px;
}

.form-container {
    width: 1000px;
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
    font-size: 2rem;
    letter-spacing: 0.5px;
}

.form-section {
    margin-bottom: 2.5rem;
    padding: 4rem;
    background: #111;
    border-radius: 12px;
    border: 1px solid #333;
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

.form-section h3 i {
    color: #fff;
    opacity: 0.8;
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
.form-group textarea,
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
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: #fff;
    box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.1);
}

.form-group input:hover,
.form-group textarea:hover,
.form-group select:hover {
    border-color: #555;
}

.form-group input:disabled {
    background: #1a1a1a;
    color: #888;
    border-color: #333;
    cursor: not-allowed;
}

.form-divider {
    height: 1px;
    background: #333;
    margin: 2rem 0;
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
    margin-bottom: 1rem;
}

.btn:hover {
    background: #e0e0e0;
    transform: translateY(-1px);
}

.btn-secondary {
    background: transparent;
    color: #fff;
    border: 1px solid #333;
}

.btn-secondary:hover {
    background: #333;
    color: #fff;
}

.actions-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-top: 2rem;
}

@media (max-width: 768px) {
    .admin-content {
        padding: 20px 15px;
    }
    
    .form-container {
        padding: 2rem 1.5rem;
    }
    
    .form-section {
        padding: 1.5rem;
    }
    
    .actions-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .form-container {
        padding: 1.5rem 1rem;
    }
    
    .form-section {
        padding: 1rem;
    }
    
    .form-container h1 {
        font-size: 1.5rem;
    }
}
</style>

<div class="admin-content">
    <div class="form-container">
        <form id="form-editar-cita" action="php/updateCita.php" method="POST">
            <h1>Editando Cita #<?php echo htmlspecialchars($cita['id_cita']); ?></h1>

            <input type="hidden" name="id_cita" value="<?php echo $cita['id_cita']; ?>">
            <input type="hidden" name="id_tatuaje" value="<?php echo $cita['id_tatuaje']; ?>">

            <div class="form-section">
                <h3><i class="ri-user-line"></i> Datos del Cliente</h3>
                <div class="form-group">
                    <label>Nombre completo:</label>
                    <input type="text" value="<?php echo htmlspecialchars($cita['cliente_nombre'] . ' ' . $cita['cliente_apellido']); ?>" disabled>
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" value="<?php echo htmlspecialchars($cita['cliente_email']); ?>" disabled>
                </div>
            </div>

            <div class="form-section">
                <h3><i class="ri-calendar-line"></i> Detalles de la Cita</h3>
                <div class="form-group">
                    <label for="fecha_hora">Fecha y Hora:</label>
                    <input type="datetime-local" id="fecha_hora" name="fecha_hora" value="<?php echo str_replace(' ', 'T', $cita['fecha_hora']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="id_artista">Artista Asignado:</label>
                    <select id="id_artista" name="id_artista" required>
                        <?php while($row = $artistas->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>" <?php if ($row['id'] == $cita['id_artista']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($row['nombre_artistico']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="id_estado_cita">Estado:</label>
                    <select id="id_estado_cita" name="id_estado_cita" required>
                        <?php while($row = $estados->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>" <?php if ($row['id'] == $cita['id_estado_cita']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($row['nombre']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="form-section">
                <h3><i class="ri-palette-line"></i> Detalles del Tatuaje</h3>
                <div class="form-group">
                    <label for="id_estilo">Estilo:</label>
                    <select id="id_estilo" name="id_estilo" required>
                        <?php while($row = $estilos_tatuaje->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>" <?php if ($row['id'] == $cita['id_estilo']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($row['nombre']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="precio_total">Precio Total (MXN):</label>
                    <input type="number" id="precio_total" name="precio_total" step="50" min="0" value="<?php echo htmlspecialchars($cita['precio_total']); ?>" placeholder="0">
                </div>
            </div>

            <div class="actions-grid">
                <button type="submit" class="btn">
                    <i class="ri-save-line"></i> Guardar Cambios
                </button>
                
                <a href="pago-form.php?id_cita=<?php echo $cita['id_cita']; ?>&monto=<?php echo htmlspecialchars($cita['precio_total']); ?>" class="btn btn-secondary">
                    <i class="ri-money-dollar-circle-line"></i> Registrar Pago
                </a>
            </div>
        </form>
    </div>
</div>

<?php
include 'admin_footer.php';
?>