<?php
    include 'admin_header.php';
?>
<title>Historial de Pagos - Admin</title>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="css/historial-pagos.css">

<div class="admin-content">
    <div class="page-header">
        <div class="header-content">
            <h1><i class="fas fa-money-bill-wave"></i> Historial de Pagos</h1>
            <p>Gestiona y visualiza todas las transacciones del sistema</p>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon total"><i class="fas fa-chart-line"></i></div>
            <div class="stat-info">
                <span class="stat-number" id="total-ingresos">$0.00</span>
                <span class="stat-label">Ingresos Totales</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon count"><i class="fas fa-receipt"></i></div>
            <div class="stat-info">
                <span class="stat-number" id="total-pagos">0</span>
                <span class="stat-label">Total de Pagos</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon promedio"><i class="fas fa-calculator"></i></div>
            <div class="stat-info">
                <span class="stat-number" id="promedio-pago">$0.00</span>
                <span class="stat-label">Promedio por Pago</span>
            </div>
        </div>
    </div>

    <div class="filters-section">
        
        <div class="filter-group">
            <label for="filter-fecha"><i class="fas fa-calendar"></i> Rango de Fechas:</label>
            <div class="date-inputs">
                <input type="date" id="filter-fecha-desde" class="filter-select">
                <span>a</span>
                <input type="date" id="filter-fecha-hasta" class="filter-select">
            </div>
        </div>
        
        <div class="filter-group">
            <label for="filter-tipo"><i class="fas fa-tag"></i> Tipo de Pago:</label>
            <select id="filter-tipo" class="filter-select">
                <option value="todos">Todos los Tipos</option>
                <option value="Anticipo">Anticipo</option>
                <option value="Liquidacion">Liquidación</option>
                <option value="Pago Completo">Pago Completo</option>
            </select>
        </div>
        
        <div class="filter-group">
            <label for="filter-metodo"><i class="fas fa-credit-card"></i> Método de Pago:</label>
            <select id="filter-metodo" class="filter-select">
                <option value="todos">Todos los Métodos</option>
                <option value="Efectivo">Efectivo</option>
                <option value="Tarjeta de Credito">Tarjeta de Crédito</option>
                <option value="Transferencia SPEI">Transferencia</option>
                <option value="PayPal">PayPal</option>
            </select>
        </div>
        
        <div class="search-group">
            <div class="search-input">
                <i class="fas fa-search"></i>
                <input type="text" id="search-pagos" placeholder="Buscar por cliente, cita o ID...">
            </div>
        </div>
    </div>
    <div class="pagos-container">
        <div class="pagos-header">
            <h3><i class="fas fa-history"></i> Registro de Transacciones</h3>
            <span class="pagos-count" id="pagos-count">Cargando...</span>
        </div>
        
        <div class="table-container">
            <table class="data-table" id="tabla-pagos">
                <thead>
                    <tr>
                        <th class="sortable" data-sort="id"><i class="fas fa-hashtag"></i> ID <i class="fas fa-sort"></i></th>
                        <th class="sortable" data-sort="fecha"><i class="fas fa-calendar"></i> Fecha <i class="fas fa-sort"></i></th>
                        <th class="sortable" data-sort="cliente"><i class="fas fa-user"></i> Cliente <i class="fas fa-sort"></i></th>
                        <th class="sortable" data-sort="cita"><i class="fas fa-palette"></i> Cita/Descripción <i class="fas fa-sort"></i></th>
                        <th class="sortable" data-sort="tipo"><i class="fas fa-tag"></i> Tipo <i class="fas fa-sort"></i></th>
                        <th class="sortable" data-sort="metodo"><i class="fas fa-credit-card"></i> Método <i class="fas fa-sort"></i></th>
                        <th class="sortable" data-sort="monto"><i class="fas fa-dollar-sign"></i> Monto <i class="fas fa-sort"></i></th>
                        <th class="acciones"><i class="fas fa-cog"></i> Acciones</th>
                    </tr>
                </thead>
                <tbody id="pagos-tabla-body">
                    <tr>
                        <td colspan="8" class="loading-text">
                            <i class="fas fa-spinner fa-spin"></i> Cargando historial de pagos...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="js/historial-pagos.js"></script>

<?php
    include 'admin_footer.php';
?>