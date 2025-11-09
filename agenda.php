<?php
    session_start(); // Inicia o reanuda la sesión

    // EL CHECKPOINT DE SEGURIDAD
    // Si la "pulsera" (sesión) no existe o no es verdadera...
    if ( !isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true ) {
        
        // ...¡Pátalo de aquí! Lo mandas al login.
        header('Location: login.html');
        exit; // Detiene la carga del resto de la página
    }

    // Si SÍ está logueado, saludamos.
    $nombre_artista = htmlspecialchars($_SESSION['nombre_artista']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda de Citas - Admin</title>
    <link rel="stylesheet" href="css/style.css"> 
    
    <style>
        /* Estos estilos son los de la otra vez, para que se vea bien */
        body { background-color: #f4f4f4; color: #333; padding: 20px; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; max-width: 900px; margin: 0 auto; }
        .admin-header a { background: #dc3545; color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none; }
        h1 { text-align: center; }
        #agenda-container { max-width: 900px; margin: 20px auto; }
        .cita-card { background-color: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 15px; margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .cita-card h3 { margin-top: 0; color: #0056b3; }
    </style>
</head>
<body>

    <header class="admin-header">
        <h1>Agenda de <?php echo $nombre_artista; ?></h1>
        <a href="php/logout.php">Cerrar Sesión</a>
    </header>

    <main>
        <div id="agenda-container">
            <p>Cargando citas...</p>
        </div>
    </main>
    
    <script src="js/agenda.js"></script>

</body>
</html>