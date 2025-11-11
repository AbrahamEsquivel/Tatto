<?php
    session_start(); // Inicia o reanuda la sesión

    // 1. EL CHECKPOINT DE SEGURIDAD
    if ( !isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true ) {
        header('Location: login.html');
        exit; 
    }
    
    $nombre_artista = htmlspecialchars($_SESSION['nombre_artista']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Directorio General - Admin</title>
    <link rel="stylesheet" href="css/style.css"> 
    
    <style>
        /* Re-usamos los mismos estilos del Dashboard */
        body { font-family: Arial, sans-serif; background: #f4f4f4; color: #333; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 20px auto; padding: 0 20px; }
        .admin-header a { background: #007bff; color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none; margin-left: 10px; }
        .admin-header a.btn-logout { background-color: #dc3545; }

        .content-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }

        /* Estilos de la tabla de reporte (igual que en dashboard) */
        .report-section {
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .report-section table {
            width: 100%;
            border-collapse: collapse;
        }
        .report-section th, .report-section td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        .report-section th { background-color: #f8f9fa; }

        /* Etiqueta para el tipo de persona */
        .tipo-cliente { 
            background-color: #28a745; 
            color: white; 
            padding: 3px 8px; 
            border-radius: 4px; 
            font-size: 0.9em;
        }
        .tipo-artista { 
            background-color: #007bff; 
            color: white; 
            padding: 3px 8px; 
            border-radius: 4px; 
            font-size: 0.9em;
        }
    </style>
</head>
<body>

    <header class="admin-header">
        <h1>Directorio General (Clientes y Artistas)</h1>
        <div>
            <a href="dashboard.php" style="background-color: #17a2b8;">Volver al Dashboard</a>
            <a href="php/logout.php" class="btn-logout">Cerrar Sesión</a>
        </div>
    </header>

    <main class="content-container">
        
        <section class="report-section">
            <p>Esta tabla combina a clientes y artistas en una sola lista usando <code>UNION</code> en la base de datos.</p>
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Tipo</th>
                    </tr>
                </thead>
                <tbody id="directorio-tabla-body">
                    <tr>
                        <td colspan="5">Cargando directorio...</td>
                    </tr>
                </tbody>
            </table>
        </section>

    </main>
    
    <script src="js/directorio.js"></script>

</body>
</html>