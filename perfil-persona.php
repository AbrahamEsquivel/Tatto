<?php
    include 'admin_header.php';
    include 'php/conexion.php';

    if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
        echo "<h1>Error: ID de persona no válido.</h1>";
        include 'admin_footer.php';
        exit;
    }
    $id_persona = $_GET['id'];

    $stmt = $conexion->prepare("SELECT * FROM persona WHERE id = ?");
    $stmt->bind_param("i", $id_persona);
    $stmt->execute();
    $persona = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conexion->close();

    if (!$persona) {
        echo "<h1>Error: Persona no encontrada.</h1>";
        include 'admin_footer.php';
        exit;
    }
?>

<title>Perfil: <?php echo htmlspecialchars($persona['nombre'] . ' ' . $persona['apellido']); ?></title>

<link rel="stylesheet" href="css/perfil.css">

<div class="admin-content">
    <div class="perfil-layout">

        <aside class="perfil-sidebar">
            
            <div class="perfil-card">
                <h2><?php echo htmlspecialchars($persona['nombre'] . ' ' . $persona['apellido']); ?></h2>
                <div class="perfil-info-item">
                    <i class="fas fa-envelope"></i>
                    <span><?php echo htmlspecialchars($persona['email']); ?></span>
                </div>
                <div class="perfil-info-item">
                    <i class="fas fa-phone"></i>
                    <span><?php echo htmlspecialchars($persona['telefono'] ?? 'No especificado'); ?></span>
                </div>
            </div>

            <div class="perfil-kpis">
                <div class="kpi-card">
                    <h3>Citas Totales</h3>
                    <div class="stat-number" id="stat-citas-totales">...</div>
                </div>
                <div class="kpi-card">
                    <h3>Gasto Total</h3>
                    <div class="stat-number stat-revenue" id="stat-gasto-total">$...</div>
                </div>
            </div>

        </aside>
        
        <div class="perfil-main">

            <section class="report-section">
                <h2><i class="fas fa-calendar-alt"></i> Historial de Citas</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID Cita</th>
                                <th>Fecha</th>
                                <th>Descripción</th>
                                <th>Artista</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-citas-persona">
                            <tr>
                                <td colspan="5" class="loading-text">
                                    <i class="fas fa-spinner fa-spin"></i> Cargando citas...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="report-section">
                <h2><i class="fas fa-money-bill-wave"></i> Historial de Pagos</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID Pago</th>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Método</th>
                                <th>Monto</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-pagos-persona">
                            <tr>
                                <td colspan="5" class="loading-text">
                                    <i class="fas fa-spinner fa-spin"></i> Cargando pagos...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
            
        </div>
    </div>
</div>

<script src="js/perfil.js"></script>

<?php
    include 'admin_footer.php';
?>