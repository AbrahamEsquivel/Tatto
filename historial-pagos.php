<?php
    session_start(); // Inicia o reanuda la sesión

    // 1. EL CHECKPOINT DE SEGURIDAD
    if ( !isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true ) {
        header('Location: login.html');
        exit; 
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Pagos - Admin</title>
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
        .monto-pago {
            font-weight: bold;
            color: #28a745; /* Verde */
        }
    </style>
</head>
<body>

    <header class="admin-header">
        <h1>Historial de Pagos</h1>
        <div>
            <a href="dashboard.php" style="background-color: #17a2b8;">Volver al Dashboard</a>
            <a href="php/logout.php" class="btn-logout">Cerrar Sesión</a>
        </div>
    </header>

    <main class="content-container">
        
        <section class="report-section">
            <p>Este reporte usa la <code>v_HistorialPagos</code> para mostrar un historial de todas las transacciones.</p>
            <table>
                <thead>
                    <tr>
                        <th>ID Pago</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Cita (Tatuaje)</th>
                        <th>Tipo de Pago</th>
                        <th>Método</th>
                        <th>Monto</th>
                    </tr>
                </thead>
                <tbody id="pagos-tabla-body">
                    <tr>
                        <td colspan="7">Cargando historial de pagos...</td>
                    </tr>
                </tbody>
            </table>
        </section>

    </main>
    
    <script src="js/historial-pagos.js"></script>
    <script src="js/dashboard.js"></script> 

</body>
</html>