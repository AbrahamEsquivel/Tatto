<?php
    include 'admin_header.php'; // Incluye el menú y la seguridad
    include 'php/conexion.php'; 

    // 1. OBTENER EL ID DE LA PERSONA (DE LA URL)
    if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
        echo "<h1>Error: ID de persona no válido.</h1>";
        include 'admin_footer.php';
        exit;
    }
    $id_persona = $_GET['id'];

    // 2. BUSCAR LOS DATOS BÁSICOS (para el título y la tarjeta)
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
                <span><?php echo htmlspecialchars($persona['telefono'] ?? 'N/A'); ?></span>
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
            <h2>Historial de Citas</h2>
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
                        <tr><td colspan="5">Cargando...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="report-section">
            <h2>Historial de Pagos</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID Pago</th>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Monto</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-pagos-persona">
                        <tr><td colspan-="4">Cargando...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
        
    </div>
</div>

<script src="js/perfil.js"></script>

<?php
    include 'admin_footer.php';
?>