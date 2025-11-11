<?php
session_start();
// Inicia o reanuda la sesión

// EL CHECKPOINT DE SEGURIDAD
// Si la 'pulsera' ( sesión ) no existe o no es verdadera...
if ( !isset( $_SESSION[ 'logueado' ] ) || $_SESSION[ 'logueado' ] !== true ) {

    // ...¡Pátalo de aquí! Lo mandas al login.
    header( 'Location: login.html' );
    exit;
    // Detiene la carga del resto de la página
}

// Si SÍ está logueado, saludamos.
$nombre_artista = htmlspecialchars( $_SESSION[ 'nombre_artista' ] );
?>

<!DOCTYPE html>
<html lang = 'es'>
<head>
<meta charset = 'UTF-8'>
<meta name = 'viewport' content = 'width=device-width, initial-scale=1.0'>
<title>Agenda de Citas - Admin</title>
<link rel = 'stylesheet' href = 'css/style.css'>

<style>
/* Estos estilos son los de la otra vez, para que se vea bien */
body {
    background-color: #f4f4f4;
    color: #333;
    padding: 20px;
}
.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 900px;
    margin: 0 auto;
}
.admin-header a {
    background: #dc3545;
    color: white;
    padding: 10px 15px;
    border-radius: 5px;
    text-decoration: none;
}
h1 {
    text-align: center;
}
#agenda-container {
    max-width: 900px;
    margin: 20px auto;
}
.cita-card {
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    box-shadow: 0 2px 4px rgba( 0, 0, 0, 0.1 );
}
.cita-card h3 {
    margin-top: 0;
    color: #0056b3;
}

.card-actions {
    margin-top: 15px;
    display: flex;
    /* Pone los botones uno al lado del otro */
    gap: 10px;
    /* Espacio entre botones */
}

.btn-editar, .btn-cancelar {
    padding: 8px 12px;
    border-radius: 5px;
    text-decoration: none;
    color: white;
    font-size: 14px;
    text-align: center;
}

.btn-editar {
    background-color: #007bff;
    /* Azul */
}
.btn-editar:hover {
    background-color: #0056b3;
}

.btn-cancelar {
    background-color: #dc3545;
    /* Rojo */
}
.btn-cancelar:hover {
    background-color: #c82333;
}

/* Estilo para botones deshabilitados */
.btn-disabled {
    background-color: #6c757d;
    /* Gris */
    pointer-events: none;
    /* No se puede hacer clic */
    opacity: 0.6;
}

/* Estilo para la tarjeta cancelada */
.cita-card.card-cancelada {
    background-color: #f8f9fa;
    opacity: 0.7;
}

/* Estilos para la etiqueta de estado */
.estado-cita {
    font-weight: bold;
    padding: 3px 8px;
    border-radius: 4px;
    color: white;
}
.estado-pendiente {
    background-color: #ffc107;
    color: #333;
}
.estado-confirmada {
    background-color: #28a745;
}
.estado-cancelada {
    background-color: #dc3545;
}
.estado-completada {
    background-color: #007bff;
}
.btn-dashboard { background-color: #17a2b8; /* Un color cian */ }
    .btn-dashboard:hover { background-color: #117a8b; }
</style>
</head>
<body>

<header class="admin-header">
    <h1>Agenda de <?php echo $nombre_artista; ?></h1>
    <div>
        <a href="dashboard.php" class="btn-dashboard">Ver Dashboard</a>
        <a href="formulario-cita.php" class="btn-nueva-cita">Crear Nueva Cita</a>
        <a href="php/logout.php" class="btn-logout">Cerrar Sesión</a>
    </div>
</header>

<main>
<div id = 'agenda-container'>
<p>Cargando citas...</p>
</div>
</main>

<script src = 'js/agenda.js'></script>

</body>
</html>