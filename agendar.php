<?php
session_start();
// Inicia o reanuda la sesión

// --- NUEVO BLOQUE DE CARGA DE DATOS ---
// ( Aquí cargamos todo lo que necesitarán los dropdowns )
include 'php/conexion.php';

// 1. Cargar Artistas
$artistas_lista = [];
$sql_artistas = 'SELECT id, nombre_artistico FROM artista WHERE active = 1 ORDER BY nombre_artistico';
$resultado_artistas = $conexion->query( $sql_artistas );
if ( $resultado_artistas ) {
    while( $fila = $resultado_artistas->fetch_assoc() ) {
        $artistas_lista[] = $fila;
    }
}

// 2. Cargar Estilos
$estilos_lista = [];
$sql_estilos = 'SELECT id, nombre FROM estilo_tatuaje ORDER BY nombre';
$resultado_estilos = $conexion->query( $sql_estilos );
if ( $resultado_estilos ) {
    while( $fila = $resultado_estilos->fetch_assoc() ) {
        $estilos_lista[] = $fila;
    }
}

// 3. Cargar Partes del Cuerpo
$partes_lista = [];
$sql_partes = 'SELECT id, nombre FROM parte_cuerpo ORDER BY nombre';
$resultado_partes = $conexion->query( $sql_partes );
if ( $resultado_partes ) {
    while( $fila = $resultado_partes->fetch_assoc() ) {
        $partes_lista[] = $fila;
    }
}

$conexion->close();
// Cerramos la conexión, ya tenemos todos los datos
?>
<!DOCTYPE html>
<html lang = 'en'>
<head>
<meta charset = 'UTF-8'>
<meta name = 'viewport' content = 'width=device-width, initial-scale=1.0'>
<link href = 'https://cdn.jsdelivr.net/npm/remixicon@3.2.0/fonts/remixicon.css' rel = 'stylesheet'>
<link href = 'https://fonts.googleapis.com/css2?family=Inknut+Antiqua:wght@400;700&display=swap' rel = 'stylesheet'>
<link rel = 'stylesheet' href = 'https://unpkg.com/boxicons@latest/css/boxicons.min.css'>
<link rel = 'shortcut icon' href = 'img/LogoLetrasBlanco.png'>
<link rel = 'stylesheet' href = 'https://unpkg.com/aos@next/dist/aos.css' />
<script src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
<link rel = 'stylesheet' href = 'css/style.css'>
<title>Agenda tu Cita - B-INK tattoo</title>
<style>
/* ( Todos tus estilos <style> van aquí, no cambian ) */
.form-page-container {
    padding-top: 100px;
    padding-bottom: 50px;
    background: #f0f2f5;
}
.form-container {

    max-width: 800px;
    margin: 20px auto;
    background: #fff;

    padding: 2rem;
    border-radius: 10px;

    box-shadow: 0 4px 12px rgba( 0, 0, 0, 0.1 );

    color: #333;
}
.form-container h1 {
    text-align: center;
    margin-bottom: 1.5rem;
    color: #000;
}
.form-container .form-group {
    margin-bottom: 1rem;
}
.form-container .form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}
.form-container .form-group input,
.form-container .form-group textarea,
.form-container .form-group select {

    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;

    border-radius: 5px;
    box-sizing: border-box;

}
.form-container .btn {

    width: 100%;
    padding: 10px;
    background: #007bff;
    color: white;

    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;

}
.form-container .btn:hover {
    background: #0056b3;
}
.validator-message {
    font-size: 0.9em;
    font-weight: bold;
    padding-top: 8px;
}
.validator-message.success {
    color: #28a745;
}
.validator-message.error {
    color: #dc3545;
}
.validator-message.loading {
    color: #6c757d;
}
#btn-submit-cita:disabled {
    background-color: #6c757d;
    cursor: not-allowed;
    opacity: 0.7;
}
</style>
</head>
<body>

<header class = 'header'>
<nav class = 'nav container'>
<div class = 'nav__info'>
<a href = '#' class = 'nav__logo'>
<img src = 'img/LogoLetrasBlanco.png' alt = 'Logo' width = '80px' height = '80px'>
</a>
<div class = 'nav_alterna' id = 'nav-toggle'>
<i class = 'ri-menu-line nav__burger'></i>
<i class = 'ri-close-line nav__close'></i>
</div>
</div>
<div class = 'nav__menu' id = 'nav-menu'>
<ul class = 'nav__list'>
<li class = 'menu_desplegable__item'>
<div class = 'nav__link'>
Home <i class = 'ri-arrow-down-s-line menu_desplegable__arrow'></i>
</div>
<ul class = 'menu_desplegable__menu'>
<li>
<a href = '/index.html' class = 'menu_desplegable__link'>
<i class = 'ri-home-line'></i></i>Inicio
</a>
</li>
</ul>
</li>
<li class = 'menu_desplegable__item'>
<div class = 'nav__link'>
Estilos de tatuajes <i class = 'ri-arrow-down-s-line menu_desplegable__arrow'></i>
</div>
<ul class = 'menu_desplegable__menu'>
<li>
<a href = 'Neo-Tradicional.html' class = 'menu_desplegable__link'>
Neo Tradicional
</a>
</li>
<li>
<a href = 'blackwork.html' class = 'menu_desplegable__link'>
Blackwork
</a>
</li>
<li>
<a href = 'tradicional.html' class = 'menu_desplegable__link'>
_           Tradicional
</a>
</li>
<li>
<a href = 'geometrico.html' class = 'menu_desplegable__link'>
Geométrico
</a>
</li>
</ul>
</li>
<li><a href = 'cuidados.html' class = 'nav__link'>Cuidados</a></li>
<li><a href = 'nosotros.html' class = 'nav__link'>Conocenos</a></li>
<?php
// ( Este bloque PHP para el menú dinámico está perfecto )
if ( isset( $_SESSION[ 'logueado' ] ) && $_SESSION[ 'logueado' ] === true ):
?>
<li><a href = 'agenda.php' class = 'nav__link'>Ver Agenda</a></li>
<li><a href = 'php/logout.php' class = 'nav__link'>Cerrar Sesión</a></li>
<?php
else:
?>
<li><a href = 'login.html' class = 'nav__link'>Admin Login</a></li>
<?php
endif;

?>
</ul>
</div>
</nav>
</header>

<main class = 'form-page-container'>
<div class = 'form-container'>
<h1>Agenda tu Cita</h1>
<p style = 'text-align: center; margin-bottom: 20px;'>
Completa este formulario para enviarnos tu solicitud. Nos pondremos en contacto contigo a la brevedad para confirmar y afinar detalles.
</p>

<form id = 'form-crear-cita-cliente' action = 'php/crearCita.php' method = 'POST'>

<h3>Tus Datos de Contacto</h3>
<div class = 'form-group'>
<label for = 'cliente_nombre'>Nombre( s ):</label>
<input type = 'text' id = 'cliente_nombre' name = 'cliente_nombre' required>
</div>
<div class = 'form-group'>
<label for = 'cliente_apellido'>Apellido( s ):</label>
<input type = 'text' id = 'cliente_apellido' name = 'cliente_apellido' required>
</div>
<div class = 'form-group'>
<label for = 'cliente_email'>Email:</label>
<input type = 'email' id = 'cliente_email' name = 'cliente_email' required>
</div>
<div class = 'form-group'>
<label for = 'cliente_telefono'>Teléfono ( Opcional, pero ayuda ):</label>
<input type = 'tel' id = 'cliente_telefono' name = 'cliente_telefono'>
</div>

<hr style = 'margin: 20px 0;'>

<h3>Detalles de tu Idea</h3>
<div class = 'form-group'>
<label for = 'fecha_hora_preferida'>Fecha y Hora Preferida:</label>
<input type = 'datetime-local' id = 'fecha_hora_preferida' name = 'fecha_hora' required>
<div id = 'time-validator-message' class = 'validator-message'></div>
</div>

<div class = 'form-group'>
<label for = 'id_artista'>Artista de Preferencia:</label>
<select id = 'id_artista' name = 'id_artista' required>
<option value = ''>-- Selecciona un artista --</option>
<?php foreach ( $artistas_lista as $artista ): ?>
<option value = "<?php echo $artista['id']; ?>">
<?php echo htmlspecialchars( $artista[ 'nombre_artistico' ] );
?>
</option>
<?php endforeach;
?>
</select>
</div>

<div class = 'form-group'>
<label for = 'tatuaje_descripcion'>Descripción de tu Tatuaje:</label>
<textarea id = 'tatuaje_descripcion' name = 'tatuaje_descripcion' rows = '4' placeholder = 'Ej: Un león realista en el brazo, tamaño 15cm...' required></textarea>
</div>

<div class = 'form-group'>
<label for = 'id_estilo'>Estilo de Tatuaje ( si lo conoces ):</label>
<select id = 'id_estilo' name = 'id_estilo' required>
<option value = ''>-- Selecciona un estilo --</option>
<?php foreach ( $estilos_lista as $estilo ): ?>
<option value = "<?php echo $estilo['id']; ?>">
<?php echo htmlspecialchars( $estilo[ 'nombre' ] );
?>
</option>
<?php endforeach;
?>
</select>
</div>

<div class = 'form-group'>
<label for = 'id_parte_cuerpo'>Parte del Cuerpo:</label>
<select id = 'id_parte_cuerpo' name = 'id_parte_cuerpo' required>
<option value = ''>-- Selecciona una parte --</option>
<?php foreach ( $partes_lista as $parte ): ?>
<option value = "<?php echo $parte['id']; ?>">
<?php echo htmlspecialchars( $parte[ 'nombre' ] );
?>
</option>
<?php endforeach;
?>
</select>
</div>

<button type = 'submit' id = 'btn-submit-cita' class = 'btn'>Enviar Solicitud de Cita</button>
</form>
</div>
</main>

<footer class = 'footer'>
<div class = 'descripcion'>
<h1>B - INK</h1>
<p>
Lorem ipsum dolor, sit amet consectetur adipisicing elit. Necessitatibus est dolorem similique officia, ea vel voluptate vero mollitia voluptatibus
recusandae ducimus vitae nihil, repellat, maiores non corrupti labore soluta fugiat.
</p>
</div>
<div class = 'datos'>
<h1>DATOS DE CONTACTO</h1>
<div class = 'direccion'>
<i class = 'bx bx-map-alt'>  Av. Fco. I. Madero #621 barrio de la Purísima <a class = 'link' href = 'https://maps.app.goo.gl/yDuZoXbkwQJFtzXH7' target = '_blank'> ver mapa </a></i>
</div>
<div class = 'telefonos'>
<i class = 'bx bxs-phone'>  449-264-26-42</i>
</div>
</div>
<div class = 'Redes-sociales'>
<h1>SIGUENOS EN</h1>
<div class = 'iconos'>
<a href = 'https://www.facebook.com/Binkstudioags' target = '_blank'><i class = 'bx bxl-facebook' ></i></a>
<a href = 'https://www.instagram.com/studiob.ink?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==' target = '_blank'><i class = 'bx bxl-instagram' ></i></a>
</div>
</div>
</footer>

<script src = 'js/main.js'></script>
<script src = 'https://unpkg.com/aos@next/dist/aos.js'></script>
<script> AOS.init();
</script>

<script src = 'js/agendar.js'></script>

</body>
</html>