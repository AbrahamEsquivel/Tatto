<!-- admin_header.html -->
<?php
    session_start();

    if ( !isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true ) {
        header('Location: login.html');
        exit; 
    }
    
    $nombre_artista = htmlspecialchars($_SESSION['nombre_artista'] ?? 'Admin');
    $pagina_actual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador</title>
    
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin_layout.css">
</head>
<body>

<div class="admin-layout">

    <aside class="admin-sidebar">
    
        <div class="logo-area">
            <h2><i class="fas fa-palette"></i> B-INK Admin</h2>
            <p style="color: #888; margin: 8px 0 0;">
                <i class="fas fa-user"></i> Hola, <?php echo $nombre_artista; ?>
            </p>
        </div>
        
        <nav>
            <a href="dashboard.php" 
               class="<?php if($pagina_actual == 'dashboard.php') echo 'active'; ?>">
               <i class="fas fa-chart-line"></i>
               <span>Dashboard</span>
            </a>
            
            <a href="agenda.php" 
               class="<?php if($pagina_actual == 'agenda.php') echo 'active'; ?>">
               <i class="fas fa-calendar-alt"></i>
               <span>Agenda de Citas</span>
            </a>
            
            <a href="directorio.php" 
               class="<?php if($pagina_actual == 'directorio.php') echo 'active'; ?>">
               <i class="fas fa-address-book"></i>
               <span>Directorio General</span>
            </a>

            <a href="historial-pagos.php" 
               class="<?php if($pagina_actual == 'historial-pagos.php') echo 'active'; ?>">
               <i class="fas fa-money-bill-wave"></i>
               <span>Historial de Pagos</span>
            </a>

             <a href="artistas.php" 
               class="<?php if($pagina_actual == 'artistas.php' || $pagina_actual == 'artista-form.php') echo 'active'; ?>">
               Gestión de Artistas
            </a>
        </nav>

       

            
            
        </nav>
        
        <div class="footer-link">
            <a href="php/logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Cerrar Sesión</span>
            </a>
        </div>
        
    </aside>
    
    <main class="admin-content"></main>