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

        .task-section {
    background: #fff;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}
.btn-admin-task {
    background-color: #ffc107; /* Amarillo */
    color: #212529; /* Texto oscuro */
    font-weight: bold;
    border: none;
    padding: 12px 18px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1em;
    transition: background-color 0.2s;
}
.btn-admin-task:hover {
    background-color: #e0a800;
}
.btn-admin-task:disabled {
    background-color: #6c757d;
    cursor: not-allowed;
    opacity: 0.7;
}
    </style>
</head>
<body>
<header class="admin-header">
    <h1>Dashboard (¡Hola, <?php echo $nombre_artista; ?>!)</h1>
    <div>
        <a href="directorio.php" style="background-color: #ffc107; color: #333;">Directorio General</a>
        
        <a href="historial-pagos.php" style="background-color: #6f42c1; color: white;">Historial de Pagos</a>
        
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
     </table>
        </section>

        <section class="task-section">
            <h2>Herramientas Administrativas</h2>
            <p>Ejecuta tareas de mantenimiento en la base de datos.</p>
            <button id="btn-enviar-recordatorios" class="btn-admin-task">
                Ejecutar: Enviar Recordatorios a Citas Pendientes
            </button>
            <span id="task-status" style="margin-left: 15px; font-weight: bold; display: none;"></span>
        </section>

        <section class="report-section">
            <h2>Bitácora de Notificaciones Recientes</h2>
            <p>Muestra los últimos 10 eventos de la tabla <code>log_notificaciones</code>.</p>
            <table>
                <thead>
                    <tr>
                        <th>Fecha de Envío</th>
                        <th>ID Cita</th>
                        <th>Mensaje</th>
                    </tr>
                </thead>
                <tbody id="bitacora-tabla-body">
                    <tr><td colspan="3">Cargando bitácora...</td></tr>
                </tbody>
            </table>
        </section>
    </main>

    
    <script src="js/dashboard.js"></script>

</body>
</html>