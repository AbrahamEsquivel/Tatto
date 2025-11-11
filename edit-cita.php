<?php
    session_start();
    include 'php/conexion.php'; // Incluimos la conexión

    // 1. BÚNKER DE SEGURIDAD
    if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
        header('Location: login.html');
        exit;
    }

    // 2. OBTENER EL ID DE LA CITA (DE LA URL)
    if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
        echo "Error: ID de cita no válido.";
        exit;
    }
    $id_cita_a_editar = $_GET['id'];

    // 3. BUSCAR LOS DATOS USANDO LA VISTA (¡MÁS LIMPIO!)
    $sql = "SELECT * FROM v_AgendaCompleta WHERE id_cita = ? LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_cita_a_editar);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 0) {
        echo "Error: Cita no encontrada.";
        exit;
    }

    $cita = $resultado->fetch_assoc(); // ¡Aquí están los datos!
    $stmt->close();
    
    // Cargar catálogos para los <select>
    $estados = $conexion->query("SELECT id, nombre FROM estado_cita ORDER BY nombre");
    $artistas = $conexion->query("SELECT id, nombre_artistico FROM artista ORDER BY nombre_artistico");

    $conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cita ID: <?php echo $cita['id_cita']; ?></title>
    <link rel="stylesheet" href="css/style.css"> 
    <style>
        body { font-family: Arial, sans-serif; background: #f0f2f5; }
        .form-container { max-width: 800px; margin: 20px auto; background: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .form-container h1 { text-align: center; margin-bottom: 1.5rem; }
        .form-container .form-group { margin-bottom: 1rem; }
        .form-container .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-container .form-group input,
        .form-container .form-group textarea,
        .form-container .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .form-container .form-group input:disabled { background: #eee; }
        .form-container .btn { width: 100%; padding: 10px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .form-container .btn:hover { background: #0056b3; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; max-width: 800px; margin: 20px auto; }
        .admin-header a { background: #6c757d; color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none; }
    </style>
</head>
<body>

    <header class="admin-header">
    <h1>Editando Cita #<?php echo htmlspecialchars($cita['id_cita']); ?></h1>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="agenda.php" style="margin-left: 10px;">Volver a la Agenda</a>
    </div>
</header>

    <div class="form-container">
        <form id="form-editar-cita" action="php/updateCita.php" method="POST">
            
            <input type="hidden" name="id_cita" value="<?php echo $cita['id_cita']; ?>">
            <input type="hidden" name="id_tatuaje" value="<?php echo $cita['id_tatuaje']; ?>">

            <h3>Datos del Cliente (Informativo)</h3>
            <div class="form-group">
                <label>Nombre:</label>
                <input type="text" value="<?php echo htmlspecialchars($cita['cliente_nombre'] . ' ' . $cita['cliente_apellido']); ?>" disabled>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" value="<?php echo htmlspecialchars($cita['cliente_email']); ?>" disabled>
            </div>

            <hr style="margin: 20px 0;">

            <h3>Datos de la Cita (Editables)</h3>
            <div class="form-group">
                <label for="fecha_hora">Fecha y Hora de la Cita:</label>
                <input type="datetime-local" id="fecha_hora" name="fecha_hora" value="<?php echo str_replace(' ', 'T', $cita['fecha_hora']); ?>" required>
            </div>
            <div class="form-group">
                <label for="id_artista">Artista Asignado:</label>
                <select id="id_artista" name="id_artista" required>
                    <?php while($row = $artistas->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>" <?php if($row['id'] == $cita['id_artista']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($row['nombre_artistico']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="id_estado_cita">Estado de la Cita:</label>
                <select id="id_estado_cita" name="id_estado_cita" required>
                    <?php while($row = $estados->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>" <?php if($row['id'] == $cita['id_estado_cita']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($row['nombre']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <hr style="margin: 20px 0;">

            <h3>Datos del Tatuaje (Editables)</h3>
            <div class="form-group">
                <label for="tatuaje_descripcion">Descripción del Tatuaje:</label>
                <textarea id="tatuaje_descripcion" name="tatuaje_descripcion" rows="4" required><?php echo htmlspecialchars($cita['tatuaje_descripcion']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="precio_total">Precio Total Acordado (MXN):</label>
                <input type="number" id="precio_total" name="precio_total" step="50" min="0" value="<?php echo htmlspecialchars($cita['precio_total']); ?>">
            </div>

            <button type="submit" class="btn">Guardar Cambios</button>
        </form>
    </div>

</body>
</html>