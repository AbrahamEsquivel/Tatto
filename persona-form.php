<?php
    include 'admin_header.php';
    include 'php/conexion.php';

    if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
        echo "<h1>Error: ID de persona no válido.</h1>";
        include 'admin_footer.php';
        exit;
    }
    $id_persona = $_GET['id'];

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
    margin-bottom: 1rem;
    color: #fff;
    font-weight: 300;
    font-size: 1.8rem;
    letter-spacing: 0.5px;
}

.form-subtitle {
    text-align: center;
    color: #888;
    margin-bottom: 2rem;
    font-size: 0.95rem;
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

.form-group input {
    width: 100%;
    padding: 12px 16px;
    background: #0f0f0f;
    border: 1px solid #333;
    border-radius: 8px;
    color: #e0e0e0;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-group input:focus {
    outline: none;
    border-color: #fff;
    box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.1);
}

.form-group input:hover {
    border-color: #555;
}

.form-group input::placeholder {
    color: #666;
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
    margin-top: 1rem;
}

.btn:hover {
    background: #e0e0e0;
    transform: translateY(-1px);
}

.btn:active {
    transform: translateY(0);
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
    
    .form-container h1 {
        font-size: 1.5rem;
    }
}

@media (max-width: 480px) {
    .form-container {
        padding: 1.5rem 1rem;
    }
    
    .form-group input {
        padding: 10px 14px;
    }
    
    .btn {
        padding: 12px;
    }
}
</style>

<div class="admin-content">
    <div class="form-container">
        <form id="form-persona" action="php/guardarPersona.php" method="POST">
            <h1>Editar Registro de Persona</h1>
            <p class="form-subtitle">
                ID Persona: <?php echo $persona['id']; ?>
            </p>

            <input type="hidden" name="id_persona" value="<?php echo $persona['id']; ?>">

            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($persona['nombre']); ?>" required placeholder="Ingresa el nombre">
            </div>

            <div class="form-group">
                <label for="apellido">Apellido:</label>
                <input type="text" id="apellido" name="apellido" value="<?php echo htmlspecialchars($persona['apellido']); ?>" required placeholder="Ingresa el apellido">
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($persona['email']); ?>" required placeholder="ejemplo@email.com">
            </div>

            <div class="form-group">
                <label for="telefono">Teléfono:</label>
                <input type="tel" id="telefono" name="telefono" value="<?php echo htmlspecialchars($persona['telefono']); ?>" placeholder="+52 449 123 4567">
            </div>

            <button type="submit" class="btn">
                Guardar Cambios
            </button>
        </form>
    </div>
</div>

<?php
    include 'admin_footer.php';
?>