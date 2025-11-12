<?php
    include 'admin_header.php'; // Incluye el menú y la seguridad
?>
<head>
    <title>Gestión de Artistas - Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="css/artistas.css">
</head>

<div class="admin-content">
    
    <div class="page-header">
        <div class="header-content">
            <h1><i class="fas fa-palette"></i> Gestión de Artistas</h1>
            <p>Da de alta, edita y gestiona las cuentas de los tatuadores</p>
        </div>
    </div>

    <div class="admin-actions-bar">
        <div class="search-input">
            <i class="fas fa-search"></i>
            <input type="text" id="search-artistas" placeholder="Buscar por nombre o email...">
        </div>
        
        <a href="artista-form.php?action=crear" class="btn-crear">
            <i class="fas fa-plus"></i> Dar de Alta Artista
        </a>
    </div>

    <div class="table-container">
        <table class="data-table" id="tabla-artistas">
            <thead>
                <tr>
                    <th><i class="fas fa-user"></i> Nombre Artístico</th>
                    <th><i class="fas fa-user-circle"></i> Nombre Real</th>
                    <th><i class="fas fa-envelope"></i> Email</th>
                    <th><i class="fas fa-phone"></i> Teléfono</th>
                    <th><i class="fas fa-toggle-on"></i> Estado</th>
                    <th><i class="fas fa-cog"></i> Acciones</th>
                </tr>
            </thead>
            <tbody id="artistas-tabla-body">
                <tr>
                    <td colspan="6" class="loading-text">
                        <i class="fas fa-spinner fa-spin"></i> Cargando artistas...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script src="js/artistas.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Buscamos si la URL tiene un parámetro 'success'
    const urlParams = new URLSearchParams(window.location.search);
    const exito = urlParams.get('success');

    if (exito === 'crear') {
        Swal.fire({
            icon: 'success',
            title: '¡Artista Creado!',
            text: 'El nuevo artista ha sido registrado exitosamente.',
            background: '#1a1a1a', // Fondo oscuro
            color: '#fff', // Texto blanco
            timer: 2500,
            timerProgressBar: true,
            showConfirmButton: false
        });
    } else if (exito === 'editar') {
        Swal.fire({
            icon: 'success',
            title: '¡Artista Actualizado!',
            text: 'Los cambios han sido guardados.',
            background: '#1a1a1a',
            color: '#fff',
            timer: 2500,
            timerProgressBar: true,
            showConfirmButton: false
        });
    }
    
    // Limpiamos la URL para que el pop-up no salga si recargas la página
    if (exito) {
        window.history.replaceState(null, null, window.location.pathname);
    }
});
</script>

<?php
    include 'admin_footer.php';
?>