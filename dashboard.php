<?php
    include 'admin_header.php';
?>

<title>Dashboard - Admin</title>

<!-- Incluir el CSS específico del dashboard -->
<link rel="stylesheet" href="css/dashboard.css">

<!-- Cambia "padre" por "dashboard-grid" -->
<div class="dashboard-grid">
    
<section class="kpi-grid" id="datos1">
    <div class="kpi-card">
        <h3><i class="fas fa-money-bill-wave"></i> Ingresos Totales</h3>
        <div class="stat-number stat-revenue" id="stat-ingresos-totales">$1,150.00</div>
        <div class="stat-trend"><i class="fas fa-chart-line"></i> Citas completadas</div>
    </div>
    <div class="kpi-card">
        <h3><i class="fas fa-clock"></i> Citas Pendientes</h3>
        <div class="stat-number stat-pending" id="stat-citas-pendientes">3</div>
        <div class="stat-trend"><i class="fas fa-exclamation-circle"></i> Por atender</div>
    </div>
    <div class="kpi-card">
        <h3><i class="fas fa-tag"></i> Precio Promedio</h3>
        <div class="stat-number" id="stat-precio-promedio">$383.33</div>
        <div class="stat-trend"><i class="fas fa-calculator"></i> Por tatuaje</div>
    </div>
    <div class="kpi-card">
        <h3><i class="fas fa-calendar-check"></i> Total Citas</h3>
        <div class="stat-number" id="stat-total-citas">11</div>
        <div class="stat-trend"><i class="fas fa-database"></i> Registradas</div>
    </div>
</section>

<section class="report-section" id="datos2">
    <h2><i class="fas fa-chart-pie"></i> Ingresos por Artista</h2>
    <p class="section-subtitle">Citas completadas en el sistema</p>
    <div class="table-container">
        <table class="data-table" id="reporte-ingresos-artista">
            <thead>
                <tr>
                    <th><i class="fas fa-user"></i> Artista</th>
                    <th><i class="fas fa-dollar-sign"></i> Ingresos Generados</th>
                    <th><i class="fas fa-check-circle"></i> Citas Completadas</th>
                </tr>
            </thead>
            <tbody id="reporte-tabla-body">
                <tr>
                    <td>Ana Tattoo</td>
                    <td>$1,150.00</td>
                    <td>4</td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<section class="report-section" id="datos3">
    <h2><i class="fas fa-tools"></i> Herramientas Administrativas</h2>
    <p class="section-subtitle">Ejecuta tareas de mantenimiento en la base de datos</p>
    <div class="tools-grid">
        <div class="tool-card">
            <div class="tool-icon">
                <i class="fas fa-bell"></i>
            </div>
            <div class="tool-content">
                <h4>Enviar Recordatorios</h4>
                <p>Envía notificaciones automáticas a citas pendientes</p>
                <button id="btn-enviar-recordatorios" class="btn-tool">
                    <i class="fas fa-play"></i> Ejecutar Ahora
                </button>
            </div>
        </div>
    </div>
    <div id="task-status" class="status-message" style="display: none;"></div>
</section>

<section class="report-section" id="datos4">
    <h2><i class="fas fa-history"></i> Bitácora de Notificaciones</h2>
    <p class="section-subtitle">Últimos 10 eventos del sistema de notificaciones</p>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th><i class="fas fa-calendar"></i> Fecha de Envío</th>
                    <th><i class="fas fa-id-card"></i> ID Cita</th>
                    <th><i class="fas fa-envelope"></i> Mensaje</th>
                </tr>
            </thead>
            <tbody id="bitacora-tabla-body">
                <tr>
                    <td>2025-11-11 08:52:31</td>
                    <td>7</td>
                    <td>Simulación: Enviando recordatorio a asae@gmail.com</td>
                </tr>
                <tr>
                    <td>2025-11-11 08:29:36</td>
                    <td>7</td>
                    <td>Simulación: Enviando recordatorio a asae@gmail.com</td>
                </tr>
                <tr>
                    <td>2025-11-11 08:20:02</td>
                    <td>3</td>
                    <td>Simulación: Enviando recordatorio a abrahambarrientos@46@gmail.com</td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

</div> <!-- Cierra dashboard-grid -->

<script src="js/dashboard.js"></script>

<?php
    include 'admin_footer.php';
?>