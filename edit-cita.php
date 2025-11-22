<?php
    include 'admin_header.php'; 
    include 'php/conexion.php';

    // Validar ID
    if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
        echo "<h1>Error: ID de cita no válido.</h1>";
        include 'admin_footer.php';
        exit;
    }
    $id_cita_a_editar = $_GET['id'];

    // Buscar datos de la cita
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
    
    // Cargar catálogos
    $estados = $conexion->query("SELECT id, nombre FROM estado_cita ORDER BY nombre");
    $estilos_tatuaje = $conexion->query("SELECT id, nombre FROM estilo_tatuaje ORDER BY nombre");
    $partes_cuerpo = $conexion->query("SELECT id, nombre FROM parte_cuerpo ORDER BY nombre");

    // Cargar Artistas (Activos + el actual asignado)
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
    padding: 2rem;
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

.form-group { margin-bottom: 1.5rem; }

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

/* Icono de calendario blanco */
input[type="datetime-local"]::-webkit-calendar-picker-indicator {
    filter: invert(1);
    cursor: pointer;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: #fff;
    box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.1);
}

.form-group input:disabled {
    background: #1a1a1a;
    color: #888;
    border-color: #333;
    cursor: not-allowed;
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
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
}

.btn-secondary:hover {
    background: #333;
    color: #fff;
}

.btn-disabled-payment {
    background: #222;
    color: #666;
    border: 1px solid #333;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    pointer-events: none;
    cursor: not-allowed;
}

.actions-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-top: 2rem;
}
</style>

<div class="admin-content">
    <div class="form-container">
        <form id="form-editar-cita" action="php/updateCita.php" method="POST">
            <h1>Editando Cita #<?php echo htmlspecialchars($cita['id_cita']); ?></h1>

            <?php if (isset($_GET['error'])): ?>
                <div style="background: rgba(239,68,68,0.1); border: 1px solid #EF4444; color: #EF4444; padding: 15px; border-radius: 6px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars(urldecode($_GET['error'])); ?></span>
                </div>
            <?php endif; ?>

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
                    <input type="datetime-local" id="fecha_hora" name="fecha_hora" value="<?php echo str_replace(' ', 'T', $cita['fecha_hora']); ?>" required onkeydown="return false">
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
                    <label for="id_parte_cuerpo">Zona del Cuerpo (Tamaño):</label>
                    <select id="id_parte_cuerpo" name="id_parte_cuerpo" required>
                        <?php while($row = $partes_cuerpo->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>" <?php if ($row['id'] == $cita['id_parte_cuerpo']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($row['nombre']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tatuaje_descripcion">Descripción:</label>
                    <textarea id="tatuaje_descripcion" name="tatuaje_descripcion" rows="3" required><?php echo htmlspecialchars($cita['tatuaje_descripcion']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Imagen de Referencia:</label>
                    <?php if (!empty($cita['imagen_referencia'])): ?>
                        <div style="margin-top: 10px;">
                            <img src="img/referencias/<?php echo htmlspecialchars($cita['imagen_referencia']); ?>" 
                                 alt="Referencia" 
                                 style="max-width: 100%; max-height: 300px; border-radius: 8px; border: 1px solid #333;">
                            <br>
                            <a href="img/referencias/<?php echo htmlspecialchars($cita['imagen_referencia']); ?>" target="_blank" style="color: #3B82F6; font-size: 0.9em;">Ver tamaño completo</a>
                        </div>
                    <?php else: ?>
                        <p style="color: #888; font-style: italic;">El cliente no subió imagen.</p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="precio_total" style="color: #22C55E;">Precio Total Acordado ($):</label>
                    <input type="number" id="precio_total" name="precio_total" step="50" min="0" 
                           value="<?php echo htmlspecialchars($cita['precio_total']); ?>" 
                           placeholder="Ej: 1500.00">
                    <small style="color: #888;">Define el costo total del tatuaje aquí.</small>
                </div>
            </div>

            <div class="actions-grid">
                <button type="submit" id="btn-guardar-cambios" class="btn">
                    <i class="ri-mail-send-line"></i> Confirmar / Cotizar
                </button>
                <?php if ($cita['precio_total'] > 0): ?>
                    <a href="pago-form.php?id_cita=<?php echo $cita['id_cita']; ?>&monto=<?php echo htmlspecialchars($cita['precio_total']); ?>" class="btn btn-secondary">
                        <i class="ri-money-dollar-circle-line"></i> Registrar Pago
                    </a>
                <?php else: ?>
                    <a href="#" class="btn-disabled-payment" title="Debes definir y guardar el precio primero">
                        <i class="ri-prohibited-line"></i> Definir Precio Primero
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-editar-cita');
    const btnGuardar = document.getElementById('btn-guardar-cambios');
    const inputPrecio = document.getElementById('precio_total');
    const emailCliente = "<?php echo $cita['cliente_email']; ?>"; // Traemos el email de PHP

    form.addEventListener('submit', (e) => {
        e.preventDefault(); // ¡Alto! Primero validamos.

        const precio = parseFloat(inputPrecio.value);

        // 1. Validación: ¿El precio es válido?
        if (isNaN(precio) || precio <= 0) {
            Swal.fire({
                icon: 'warning',
                title: '¡Falta el Precio!',
                text: 'No puedes cotizar o confirmar un tatuaje en $0. Por favor asigna un precio válido.',
                background: '#1a1a1a',
                color: '#fff',
                confirmButtonColor: '#F59E0B'
            });
            return; // Detenemos todo aquí
        }

        // 2. Si el precio está bien, mostramos la simulación del correo
        let mensaje = `Se enviará una notificación a: ${emailCliente}\nCon la cotización de: $${precio}`;

        Swal.fire({
            title: '¿Confirmar y Cotizar?',
            text: mensaje,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, enviar y guardar',
            cancelButtonText: 'Cancelar',
            background: '#1a1a1a',
            color: '#fff',
            confirmButtonColor: '#22C55E',
            cancelButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                // Si el usuario dice "Sí", enviamos el formulario de verdad
                form.submit();
            }
        });
    });
});
</script>
<?php
include 'admin_footer.php';
?>