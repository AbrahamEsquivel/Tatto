<?php
    include 'admin_header.php';
?>
<title>Directorio General - Admin</title>

<!-- Incluir CSS específico para directorio -->
<link rel="stylesheet" href="css/directorio.css">

<div class="admin-content">
    <div class="page-header">
        <div class="header-content">
            <h1><i class="fas fa-address-book"></i> Directorio General</h1>
            <p>Gestiona y visualiza todos los clientes y artistas del sistema</p>
        </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon clients">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number" id="total-clientes">0</span>
                <span class="stat-label">Total Clientes</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon artists">
                <i class="fas fa-palette"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number" id="total-artistas">0</span>
                <span class="stat-label">Total Artistas</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon total">
                <i class="fas fa-database"></i>
            </div>
            <div class="stat-info">
                <span class="stat-number" id="total-registros">0</span>
                <span class="stat-label">Registros Totales</span>
            </div>
        </div>
    </div>

    <!-- Filtros y búsqueda -->
    <div class="filters-section">
        <div class="filter-group">
            <label for="filter-tipo"><i class="fas fa-filter"></i> Filtrar por Tipo:</label>
            <select id="filter-tipo" class="filter-select">
                <option value="todos">Todos los Registros</option>
                <option value="Cliente">Solo Clientes</option>
                <option value="Artista">Solo Artistas</option>
            </select>
        </div>
        
        <div class="search-group">
            <div class="search-input">
                <i class="fas fa-search"></i>
                <input type="text" id="search-directorio" placeholder="Buscar por nombre, email o teléfono...">
            </div>
        </div>
        
        <div class="actions-group">
            <button class="btn-export" id="btn-exportar">
                <i class="fas fa-file-export"></i> Exportar CSV
            </button>
        </div>
    </div>

    <!-- Contenedor del directorio -->
    <div class="directorio-container">
        <div class="directorio-header">
            <h3><i class="fas fa-list"></i> Lista del Directorio</h3>
            <span class="registros-count" id="registros-count">Cargando...</span>
        </div>
        
        <div class="table-container">
            <table class="data-table" id="tabla-directorio">
                <thead>
                    <tr>
                        <th class="sortable" data-sort="nombre">
                            <i class="fas fa-user"></i> Nombre
                            <i class="fas fa-sort"></i>
                        </th>
                        <th class="sortable" data-sort="apellido">
                            <i class="fas fa-user"></i> Apellido
                            <i class="fas fa-sort"></i>
                        </th>
                        <th class="sortable" data-sort="email">
                            <i class="fas fa-envelope"></i> Email
                            <i class="fas fa-sort"></i>
                        </th>
                        <th class="sortable" data-sort="telefono">
                            <i class="fas fa-phone"></i> Teléfono
                            <i class="fas fa-sort"></i>
                        </th>
                        <th class="sortable" data-sort="tipo">
                            <i class="fas fa-tag"></i> Tipo
                            <i class="fas fa-sort"></i>
                        </th>
                        <th class="acciones">
                            <i class="fas fa-cog"></i> Acciones
                        </th>
                    </tr>
                </thead>
                <tbody id="directorio-tabla-body">
                    <tr>
                        <td colspan="6" class="loading-text">
                            <i class="fas fa-spinner fa-spin"></i> Cargando directorio...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="js/directorio.js"></script>

<?php
    include 'admin_footer.php';
?>