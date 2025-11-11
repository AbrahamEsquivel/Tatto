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
    <title>Dashboard - Admin</title>
    <link rel="stylesheet" href="css/style.css"> 
    
    <style>
        /* Re-usamos estilos de admin */
        body { font-family: Arial, sans-serif; background: #f4f4f4; color: #333; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 20px auto; padding: 0 20px; }
        .admin-header a { background: #007bff; color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none; margin-left: 10px; }
        .admin-header a.btn-logout { background-color: #dc3545; }

        .dashboard-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }

        /* Contenedor para las "Tarjetas" de estadísticas */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .kpi-card {
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .kpi-card h3 {
            margin-top: 0;
            font-size: 1.2rem;
            color: #555;
        }
        .kpi-card .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #007bff;
            margin-top: 10px;
        }
        /* Color especial para el dinero */
        .kpi-card .stat-revenue {
            color: #28a745;
        }
        .kpi-card .stat-pending {
            color: #ffc107;
        }

        /* Contenedor para los "Reportes" */
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
    </style>
</head>
<body>

    <header class="admin-header">
        <h1>Dashboard (¡Hola, <?php echo $nombre_artista; ?>!)</h1>
        <div>
            <a href="agenda.php">Ver Agenda Completa</a>
            <a href="php/logout.php" class="btn-logout">Cerrar Sesión</a>
        </div>
    </header>

    <main class="dashboard-container">
        
        <section class="kpi-grid">
            <div class="kpi-card">
                <h3>Ingresos Totales (Completadas)</h3>
                <div class="stat-number stat-revenue" id="stat-ingresos-totales">$...</div>
            </div>
            <div class="kpi-card">
                <h3>Citas Pendientes</h3>
                <div class="stat-number stat-pending" id="stat-citas-pendientes">...</div>
            </div>
            <div class="kpi-card">
                <h3>Precio Promedio por Tatuaje</h3>
                <div class="stat-number" id="stat-precio-promedio">$...</div>
            </div>
            <div class="kpi-card">
                <h3>Total Citas Registradas</h3>
                <div class="stat-number" id="stat-total-citas">...</div>
            </div>
        </section>

        <section class="report-section">
            <h2>Ingresos por Artista (Citas Completadas)</h2>
            <table id="reporte-ingresos-artista">
                <thead>
                    <tr>
                        <th>Artista</th>
                        <th>Ingresos Generados</th>
                        <th>Citas Completadas</th>
                    </tr>
                </thead>
                <tbody id="reporte-tabla-body">
                    <tr>
                        <td colspan="3">Cargando reporte...</td>
                    </tr>
                </tbody>
            </table>
        </section>

    </main>
    
    <script src="js/dashboard.js"></script>

</body>
</html>