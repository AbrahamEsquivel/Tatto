<?php
    include 'admin_header.php'; // Incluye el menú y la seguridad
    include 'php/conexion.php'; 

    // 1. LÓGICA PARA DECIDIR "MODO" (CREAR o EDITAR)
    $modo_edicion = false;
    $titulo_pagina = "Dar de Alta Nuevo Artista";
    
    // Valores por defecto (para 'Crear')
    $artista = [
        'id_artista' => null,
        'id_persona' => null,
        'nombre_artistico' => '',
        'nombre' => '',
        'apellido' => '',
        'email' => '',
        'telefono' => '',
        'active' => 1 // Activo por defecto
    ];

    if (isset($_GET['action']) && $_GET['action'] == 'editar' && isset($_GET['id'])) {
        // --- MODO EDITAR ---
        $modo_edicion = true;
        $id_artista_editar = (int)$_GET['id'];
        $titulo_pagina = "Editar Artista #$id_artista_editar";
        
        // Buscamos los datos del artista (¡CON ID_PERSONA!)
        $stmt = $conexion->prepare("SELECT a.id AS id_artista, a.nombre_artistico, a.active, 
                                          p.nombre, p.apellido, p.email, p.telefono,
                                          p.id AS id_persona
                                   FROM artista AS a
                                   JOIN persona AS p ON a.id_persona = p.id
                                   WHERE a.id = ?");
        $stmt->bind_param("i", $id_artista_editar);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            $artista = $resultado->fetch_assoc();
        } else {
            echo "<h1>Error: Artista no encontrado.</h1>";
            include 'admin_footer.php';
            exit;
        }
        $stmt->close();
    }
    
    $conexion->close();
?>

<head>
    <title><?php echo $titulo_pagina; ?> - Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .form-container-dark { 
            max-width: 800px; 
            margin: 0 auto;
            background: #1a1a1a; 
            padding: 2rem 2.5rem; 
            border-radius: 12px; 
            border: 1px solid #333;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2); 
        }
        .form-container-dark h1 { 
            text-align: center; 
            margin-bottom: 2rem; 
            color: #fff;
            font-weight: 300;
        }
        .form-container-dark .form-group { margin-bottom: 1.25rem; }
        .form-container-dark .form-group label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: 500;
            color: #b0b0b0;
        }
        .form-container-dark .form-group input,
        .form-container-dark .form-group select { 
            width: 100%; 
            padding: 12px 15px; 
            border: 1px solid #333; 
            border-radius: 6px; 
            box-sizing: border-box; 
            background-color: #111;
            color: #fff;
            font-size: 1rem;
        }
        .form-container-dark .form-group input:focus,
        .form-container-dark .form-group select:focus {
            outline: none;
            border-color: #555;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.1);
        }
        .form-container-dark .btn { 
            width: 100%; 
            padding: 12px; 
            background: #22C55E; /* Verde */ 
            color: white; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer; 
            font-size: 1.1rem;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }
        .form-container-dark .btn:hover { background: #16A34A; }
        
        .password-note {
            font-size: 0.85rem;
            color: #888;
            margin-top: 5px;
        }
        
        /* ¡ESTILOS PARA LA ALERTA DE ERROR! */
        .form-alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            border: 1px solid;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.95rem;
        }
        .form-alert.error {
            background-color: rgba(239, 68, 68, 0.1); /* Rojo claro */
            border-color: #EF4444;
            color: #F87171;
        }
    </style>
</head>
<div class="form-container-dark">
    
    <form id="form-artista" action="php/guardarArtista.php" method="POST">

        <h1><i class="fas fa-palette"></i> <?php echo $titulo_pagina; ?></h1>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="form-alert error">
                <i class="fas fa-exclamation-triangle"></i>
                <?php 
                    // Decodificamos el mensaje de error de la URL
                    echo htmlspecialchars(urldecode($_GET['error'])); 
                ?>
            </div>
        <?php endif; ?>

        <input type="hidden" name="action" value="<?php echo $modo_edicion ? 'editar' : 'crear'; ?>">
        <?php if ($modo_edicion): ?>
            <input type="hidden" name="id_artista" value="<?php echo $artista['id_artista']; ?>">
            <input type="hidden" name="id_persona" value="<?php echo $artista['id_persona']; ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="nombre_artistico">Nombre Artístico:</label>
            <input type="text" id="nombre_artistico" name="nombre_artistico" value="<?php echo htmlspecialchars($artista['nombre_artistico']); ?>" required>
        </div>
        
        <hr style="border-color: #333; margin: 20px 0;">
        
        <div class="form-group">
            <label for="nombre">Nombre(s) Real:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($artista['nombre']); ?>" required>
        </div>
        <div class="form-group">
            <label for="apellido">Apellido(s):</label>
            <input type="text" id="apellido" name="apellido" value="<?php echo htmlspecialchars($artista['apellido']); ?>" required>
        </div>
        
        <hr style="border-color: #333; margin: 20px 0;">

        <h3>Datos de Acceso y Contacto</h3>
        <div class="form-group">
            <label for="email">Email (Login):</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($artista['email']); ?>" required>
        </div>
        <div class="form-group">
            <label for="telefono">Teléfono:</label>
            <input type="tel" id="telefono" name="telefono" value="<?php echo htmlspecialchars($artista['telefono']); ?>">
        </div>
        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" <?php if (!$modo_edicion) echo 'required'; ?>>
            <?php if ($modo_edicion): ?>
                <p class="password-note">Dejar en blanco para no cambiar la contraseña.</p>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="password_confirm">Confirmar Contraseña:</label>
            <input type="password" id="password_confirm" name="password_confirm" <?php if (!$modo_edicion) echo 'required'; ?>>
        </div>

        <hr style="border-color: #333; margin: 20px 0;">

        <div class="form-group">
            <label for="active">Estado:</label>
            <select id="active" name="active">
                <option value="1" <?php if ($artista['active'] == 1) echo 'selected'; ?>>Activo</option>
                <option value="0" <?php if ($artista['active'] == 0) echo 'selected'; ?>>Inactivo</option>
            </select>
        </div>

        <button type="submit" class="btn">
            <?php echo $modo_edicion ? 'Guardar Cambios' : 'Crear Artista'; ?>
        </button>
    </form>
</div>

<?php
    include 'admin_footer.php';
?>