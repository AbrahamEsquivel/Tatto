<?php
    include 'admin_header.php'; // Incluye el menú y la seguridad
    include 'php/conexion.php'; 

    // 1. OBTENER EL ID DE LA PERSONA (DE LA URL)
    if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
        echo "<h1>Error: ID de persona no válido.</h1>";
        include 'admin_footer.php';
        exit;
    }
    $id_persona = $_GET['id'];

    // 2. BUSCAR LOS DATOS DE ESA PERSONA EN LA BD
    $stmt = $conexion->prepare("SELECT * FROM persona WHERE id = ?");
    $stmt->bind_param("i", $id_persona);
    $stmt->execute();
    $persona = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$persona) {
        echo "<h1>Error: Persona no encontrada.</h1>";
        include 'admin_footer.php';
        exit;
    }
    
    $conexion->close();
?>

<title>Editar Persona: <?php echo htmlspecialchars($persona['nombre']); ?></title>

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
    .form-container .form-group input { 
        width: 100%; padding: 10px; border: 1px solid #ddd; 
        border-radius: 5px; box-sizing: border-box; 
    }
    .form-container .btn { 
        width: 100%; padding: 10px; background: #3B82F6; color: white; 
        border: none; border-radius: 5px; cursor: pointer; font-size: 16px; 
    }
    .form-container .btn:hover { background: #2563EB; }
</style>

<div class="form-container">
    
    <form id="form-persona" action="php/guardarPersona.php" method="POST">

        <h1>Editar Registro de Persona</h1>
        <p style="text-align: center; color: #6B7280; margin-top: -15px; margin-bottom: 20px;">
            (ID Persona: <?php echo $persona['id']; ?>)
        </p>

        <input type="hidden" name="id_persona" value="<?php echo $persona['id']; ?>">

        <div class="form-group">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($persona['nombre']); ?>" required>
        </div>
        <div class="form-group">
            <label for="apellido">Apellido:</label>
            <input type="text" id="apellido" name="apellido" value="<?php echo htmlspecialchars($persona['apellido']); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($persona['email']); ?>" required>
        </div>
        <div class="form-group">
            <label for="telefono">Teléfono:</label>
            <input type="tel" id="telefono" name="telefono" value="<?php echo htmlspecialchars($persona['telefono']); ?>">
        </div>

        <button type="submit" class="btn">Guardar Cambios</button>
    </form>
</div>

<?php
    include 'admin_footer.php';
?>