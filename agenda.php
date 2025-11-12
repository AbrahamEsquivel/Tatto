<?php
    include 'admin_header.php';
?>

<title>Agenda de Citas - Admin</title>

<!-- Incluir CSS específico para agenda -->
<link rel="stylesheet" href="css/agenda.css">

<div class="admin-content">
    <div class="page-header">
        <div class="header-content">
            <h1><i class="fas fa-calendar-alt"></i> Agenda de Citas</h1>
            <p>Gestiona y organiza todas las citas programadas</p>
        </div>
    </div>

    <!-- Layout principal con sidebar -->
    <div class="agenda-layout">
        
        <!-- Sidebar izquierdo - Filtros y estadísticas -->
        <div class="agenda-sidebar">
            
            <!-- Filtros y búsqueda -->
            <div class="filters-section">
                <div class="filter-group">
                    <label for="filter-estado"><i class="fas fa-filter"></i> Filtro por Estado:</label>
                    <select id="filter-estado" class="filter-select">
                        <option value="todas">Todas las Citas</option>
                        <option value="pendiente">Pendientes</option>
                        <option value="confirmada">Confirmadas</option>
                        <option value="completada">Completadas</option>
                        <option value="cancelada">Canceladas</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="filter-fecha"><i class="fas fa-calendar"></i> Filtro por Fecha:</label>
                    <input type="date" id="filter-fecha" class="filter-select">
                </div>
                
                <div class="filter-group">
                    <label for="search-citas"><i class="fas fa-search"></i> Buscar:</label>
                    <div class="search-input">
                        <i class="fas fa-search"></i>
                        <input type="text" id="search-citas" placeholder="Cliente, artista, servicio...">
                    </div>
                </div>
            </div>

            <!-- Estadísticas rápidas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-number" id="count-pendientes">0</span>
                        <span class="stat-label">Pendientes</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon confirmed">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-number" id="count-confirmadas">0</span>
                        <span class="stat-label">Confirmadas</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon completed">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-number" id="count-completadas">0</span>
                        <span class="stat-label">Completadas</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon cancelled">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-number" id="count-canceladas">0</span>
                        <span class="stat-label">Canceladas</span>
                    </div>
                </div>
            </div>
            
        </div>

        <!-- Contenedor principal de citas -->
        <div class="agenda-main">
            <div class="citas-container">
                <div class="citas-header">
                    <h3><i class="fas fa-list"></i> Lista de Citas</h3>
                    <span class="citas-count" id="citas-count">Cargando...</span>
                </div>
                
                <div id="agenda-container" class="citas-grid">
                    <div class="loading-state">
                        <i class="fas fa-spinner fa-spin"></i>
                        <p>Cargando citas...</p>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>

<script src="js/agenda.js"></script>

<?php
    include 'admin_footer.php';
?>