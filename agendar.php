<?php 
    session_start();
    include 'php/conexion.php';
    
    // 1. Cargar Artistas
    $artistas_lista = [];
    $sql_artistas = "SELECT id, nombre_artistico FROM artista WHERE active = 1 ORDER BY nombre_artistico";
    $resultado_artistas = $conexion->query($sql_artistas);
    if ($resultado_artistas) {
        while($fila = $resultado_artistas->fetch_assoc()) {
            $artistas_lista[] = $fila;
        }
    }

    // 2. Cargar Estilos
    $estilos_lista = [];
    $sql_estilos = "SELECT id, nombre FROM estilo_tatuaje ORDER BY nombre";
    $resultado_estilos = $conexion->query($sql_estilos);
    if ($resultado_estilos) {
        while($fila = $resultado_estilos->fetch_assoc()) {
            $estilos_lista[] = $fila;
        }
    }
    
    // 3. Cargar Partes del Cuerpo
    $partes_lista = [];
    $sql_partes = "SELECT id, nombre FROM parte_cuerpo ORDER BY nombre";
    $resultado_partes = $conexion->query($sql_partes);
    if ($resultado_partes) {
        while($fila = $resultado_partes->fetch_assoc()) {
            $partes_lista[] = $fila;
        }
    }
    
    $conexion->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.2.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inknut+Antiqua:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <link rel="shortcut icon" href="img/LogoLetrasBlanco.png">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="css/style.css">
    <title>Agenda tu Cita - B-INK tattoo</title>
    <style>
        /* --- ESTILOS DE VALIDACIÓN (ROJOS) --- */
        .input-error-message {
            color: #ff6b6b;
            font-size: 0.85rem;
            margin-top: 5px;
            display: none; /* Oculto por defecto */
        }

        .form-group.error input,
        .form-group.error select,
        .form-group.error textarea {
            border-color: #ff6b6b;
            box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.1);
        }

        /* --- ESTILO DEL CALENDARIO (ICONO BLANCO) --- */
        input[type="date"]::-webkit-calendar-picker-indicator,
        input[type="datetime-local"]::-webkit-calendar-picker-indicator {
            filter: invert(1); 
            cursor: pointer;
        }

        /* --- ESTILOS DEL FORMULARIO MODERNO --- */
        .form-page-container { 
            padding-top: 100px; 
            padding-bottom: 50px; 
            background: linear-gradient(135deg, #0f0f0f 0%, #1a1a1a 50%, #2d2d2d 100%);
            min-height: 100vh;
        }
        
        .form-container { 
            max-width: 800px; 
            margin: 20px auto; 
            background: #1a1a1a; 
            padding: 3rem; 
            border-radius: 20px; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            border: 1px solid #333;
            color: #e0e0e0;
            position: relative;
            overflow: hidden;
        }
        
        .form-container::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4, #feca57);
            background-size: 400% 400%;
            animation: gradientShift 3s ease infinite;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .form-container h1 { 
            text-align: center; margin-bottom: 1.5rem; color: #fff; 
            font-size: 2.5rem; font-weight: 300; letter-spacing: 1px;
        }
        
        .form-container .intro-text {
            text-align: center; margin-bottom: 2.5rem; color: #b0b0b0;
            font-size: 1.1rem; line-height: 1.6;
        }
        
        .form-section {
            margin-bottom: 2.5rem; padding: 2rem; background: #111;
            border-radius: 12px; border: 1px solid #333;
        }
        
        .form-section h3 {
            color: #fff; margin-bottom: 1.5rem; font-size: 1.4rem;
            font-weight: 400; display: flex; align-items: center; gap: 10px;
        }
        
        .form-section h3 i { color: #4ecdc4; }
        
        .form-group { margin-bottom: 1.5rem; }
        
        .form-group label { 
            display: block; margin-bottom: 8px; font-weight: 500; 
            color: #fff; font-size: 0.95rem;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select { 
            width: 100%; padding: 12px 16px; background: #0f0f0f;
            border: 1px solid #333; border-radius: 8px; box-sizing: border-box; 
            color: #e0e0e0; font-size: 1rem; transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus { 
            outline: none; border-color: #4ecdc4;
            box-shadow: 0 0 0 3px rgba(78, 205, 196, 0.1);
        }
        
        .form-group input:hover,
        .form-group textarea:hover,
        .form-group select:hover { border-color: #555; }
        
        .form-group textarea { resize: vertical; min-height: 100px; font-family: inherit; }
        
        .form-divider {
            height: 1px; background: linear-gradient(90deg, transparent, #333, transparent);
            margin: 2rem 0;
        }
        
        .btn { 
            width: 100%; padding: 15px; 
            background: linear-gradient(135deg, #4ecdc4, #44a08d);
            color: white; border: none; border-radius: 8px; cursor: pointer; 
            font-size: 1.1rem; font-weight: 500; letter-spacing: 0.5px;
            transition: all 0.3s ease; position: relative; overflow: hidden;
        }
        
        .btn:hover { 
            background: linear-gradient(135deg, #44a08d, #4ecdc4);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(78, 205, 196, 0.3);
        }
        
        .btn:active { transform: translateY(0); }
        
        #btn-submit-cita:disabled { 
            background: #666; cursor: not-allowed; opacity: 0.7;
            transform: none; box-shadow: none;
        }
        
        .validator-message { 
            font-size: 0.9em; font-weight: bold; padding-top: 8px; 
            display: flex; align-items: center; gap: 8px;
        }
        .validator-message.success { color: #4ecdc4; }
        .validator-message.error { color: #ff6b6b; }
        .validator-message.loading { color: #feca57; }
        
        .select-wrapper { position: relative; }
        .select-wrapper::after {
            content: '▼'; position: absolute; right: 15px; top: 50%;
            transform: translateY(-50%); color: #888; pointer-events: none; font-size: 0.8rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .form-container { margin: 10px; padding: 2rem 1.5rem; }
            .form-section { padding: 1.5rem; }
            .form-container h1 { font-size: 2rem; }
        }
        @media (max-width: 480px) {
            .form-container { padding: 1.5rem 1rem; }
            .form-section { padding: 1rem; }
            .form-container h1 { font-size: 1.8rem; }
        }
    </style>
</head>
<body>
    
    <header class="header">
        <nav class="nav container">
            <div class="nav__info">
                <a href="#" class="nav__logo">
                    <img src="img/LogoLetrasBlanco.png" alt="Logo" width="80px" height="80px">
                </a>   
                <div class="nav_alterna" id="nav-toggle">
                    <i class="ri-menu-line nav__burger"></i>
                    <i class="ri-close-line nav__close"></i>
                </div>
            </div>
            <div class="nav__menu" id="nav-menu">
                <ul class="nav__list">
                    <li class="menu_desplegable__item">
                    <div class="nav__link">
                        Home <i class="ri-arrow-down-s-line menu_desplegable__arrow"></i>
                    </div>
                    <ul class="menu_desplegable__menu">
                        <li>
                            <a href="/index.html" class="menu_desplegable__link">
                                <i class="ri-home-line"></i></i>Inicio
                            </a>                          
                        </li>
                    </ul>
                </li>
                <li class="menu_desplegable__item">
                    <div class="nav__link">
                        Estilos de tatuajes <i class="ri-arrow-down-s-line menu_desplegable__arrow"></i>
                    </div>
                    <ul class="menu_desplegable__menu">
                        <li><a href="Neo-Tradicional.html" class="menu_desplegable__link">Neo Tradicional</a></li>
                        <li><a href="blackwork.html" class="menu_desplegable__link">Blackwork</a></li>   
                        <li><a href="tradicional.html" class="menu_desplegable__link">Tradicional</a></li> 
                        <li><a href="geometrico.html" class="menu_desplegable__link">Geométrico</a></li> 
                    </ul>
                </li>
                <li><a href="cuidados.html" class="nav__link">Cuidados</a></li>
                <li><a href="nosotros.html" class="nav__link">Conocenos</a></li>
                <?php
                if (isset($_SESSION['logueado']) && $_SESSION['logueado'] === true):
                ?>
                <li><a href="agenda.php" class="nav__link">Ver Agenda</a></li>
                <li><a href="php/logout.php" class="nav__link">Cerrar Sesión</a></li>
                <?php else: ?>
                <li><a href="login.html" class="nav__link">Admin Login</a></li>
                <?php endif; ?>
            </ul>
            </div>
        </nav>
    </header>

    <main class="form-page-container">
        <div class="form-container">
            <h1>Agenda tu Cita</h1>
            <p class="intro-text">
                Completa este formulario para enviarnos tu solicitud. Nos pondremos en contacto contigo a la brevedad para confirmar y afinar detalles.
            </p>

            <form id="form-crear-cita-cliente" action="php/crearCita.php" method="POST" enctype="multipart/form-data">
                
                <div class="form-section">
                    <h3><i class="ri-user-line"></i> Tus Datos de Contacto</h3>
                    
                    <div class="form-group">
                        <label for="cliente_nombre">Nombre(s):</label>
                        <input type="text" id="cliente_nombre" name="cliente_nombre" required placeholder="Ingresa tu nombre">
                        <div class="input-error-message">El nombre solo debe contener letras y espacios (min 2 caracteres).</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="cliente_apellido">Apellido(s):</label>
                        <input type="text" id="cliente_apellido" name="cliente_apellido" required placeholder="Ingresa tus apellidos">
                        <div class="input-error-message">El apellido solo debe contener letras y espacios (min 2 caracteres).</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="cliente_email">Email:</label>
                        <input type="email" id="cliente_email" name="cliente_email" required placeholder="tu@email.com">
                        <div class="input-error-message">Ingresa un correo electrónico válido.</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="cliente_telefono">Teléfono (Opcional, pero ayuda):</label>
                        <input type="tel" id="cliente_telefono" name="cliente_telefono" placeholder="+52 449 123 4567">
                        <div class="input-error-message">Ingresa un número de teléfono válido (10 dígitos).</div>
                    </div>
                </div>

                <div class="form-divider"></div>

                <div class="form-section">
                    <h3><i class="ri-palette-line"></i> Detalles de tu Idea</h3>
                    
                    <div class="form-group">
                        <label for="fecha_hora_preferida">Fecha y Hora Preferida:</label>
                        <input type="datetime-local" id="fecha_hora_preferida" name="fecha_hora" required onkeydown="return false">
                        <div id="time-validator-message" class="validator-message"></div>
                    </div>

                    <div class="form-group">
                        <label for="id_artista">Artista de Preferencia:</label>
                        <div class="select-wrapper">
                            <select id="id_artista" name="id_artista" required>
                                <option value="">-- Selecciona un artista --</option>
                                <?php foreach ($artistas_lista as $artista): ?>
                                    <option value="<?php echo $artista['id']; ?>">
                                        <?php echo htmlspecialchars($artista['nombre_artistico']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="tatuaje_descripcion">Descripción de tu Tatuaje:</label>
                        <textarea id="tatuaje_descripcion" name="tatuaje_descripcion" rows="4" placeholder="Ej: Un león realista en el brazo, tamaño 15cm, con detalles en negro y grises..." required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="imagen_referencia">Imagen de Referencia (Opcional):</label>
                        <input type="file" id="imagen_referencia" name="imagen_referencia" accept="image/*">
                        <small style="color: #888;">Formatos: JPG, PNG, JPEG. Máx 5MB.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="id_estilo">Estilo de Tatuaje (si lo conoces):</label>
                        <div class="select-wrapper">
                            <select id="id_estilo" name="id_estilo" required>
                                <option value="">-- Selecciona un estilo --</option>
                                <?php foreach ($estilos_lista as $estilo): ?>
                                    <option value="<?php echo $estilo['id']; ?>">
                                        <?php echo htmlspecialchars($estilo['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="id_parte_cuerpo">Parte del Cuerpo:</label>
                        <div class="select-wrapper">
                            <select id="id_parte_cuerpo" name="id_parte_cuerpo" required>
                                <option value="">-- Selecciona una parte --</option>
                                <?php foreach ($partes_lista as $parte): ?>
                                    <option value="<?php echo $parte['id']; ?>">
                                        <?php echo htmlspecialchars($parte['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <button type="submit" id="btn-submit-cita" class="btn">
                        <i class="ri-send-plane-line"></i> Enviar Solicitud de Cita
                    </button>
                </div>
            </form>
        </div>
    </main>

    <footer class="footer">
        <div class="descripcion">
        <h1>B - INK</h1>
            <p>
                Lorem ipsum dolor, sit amet consectetur adipisicing elit. Necessitatibus est dolorem similique officia, ea vel voluptate vero mollitia voluptatibus 
                recusandae ducimus vitae nihil, repellat, maiores non corrupti labore soluta fugiat.
            </p>
        </div>
        <div class="datos">
            <h1>DATOS DE CONTACTO</h1>
            <div class="direccion">
                <i class='bx bx-map-alt'>  Av. Fco. I. Madero #621 barrio de la Purísima <a class="link" href="https://maps.app.goo.gl/yDuZoXbkwQJFtzXH7" target="_blank"> ver mapa </a></i>
            </div>
            <div class="telefonos">
                <i class='bx bxs-phone'>  449-264-26-42</i>
            </div> 
        </div>
    <div class="Redes-sociales">
        <h1>SIGUENOS EN</h1>
        <div class="iconos">
        <a href="https://www.facebook.com/Binkstudioags" target="_blank"><i class='bx bxl-facebook' ></i></a>
        <a href="https://www.instagram.com/studiob.ink?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==" target="_blank"><i class='bx bxl-instagram' ></i></a>
    </div>
    </div>
    </footer>

    <script src="js/main.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script> AOS.init(); </script>
    
    <script src="js/agendar.js"></script>

</body>
</html>