// js/artistas.js (El "Cerebro" para tu CRUD de Artistas)

// --- Variable Global ---
let allArtistas = []; // Aquí guardaremos la lista maestra

// --- FUNCIÓN DE ARRANQUE ---
document.addEventListener('DOMContentLoaded', () => {
    cargarArtistas();
    configurarFiltros();
});

/**
 * 1. Carga TODOS los artistas desde el backend
 */
function cargarArtistas() {
    const tablaBody = document.getElementById('artistas-tabla-body');
    tablaBody.innerHTML = `
        <tr>
            <td colspan="6" class="loading-text">
                <i class="fas fa-spinner fa-spin"></i> Cargando artistas...
            </td>
        </tr>
    `;

    fetch('php/getArtistas.php')
        .then(response => response.json())
        .then(data => {
            if (!data.success) throw new Error(data.message || 'Error API');
            
            allArtistas = data.artistas; // Guardamos en la lista maestra
            renderArtistas(allArtistas); // Pintamos la tabla
        })
        .catch(error => {
            console.error('Error al cargar artistas:', error);
            tablaBody.innerHTML = `
                <tr>
                    <td colspan="6" class="empty-state">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h4>Error al cargar datos</h4>
                        <p>${error.message}</p>
                    </td>
                </tr>
            `;
        });
}

/**
 * 2. Configura el 'listener' de la barra de búsqueda
 */
function configurarFiltros() {
    document.getElementById('search-artistas').addEventListener('input', aplicarFiltros);
}

/**
 * 3. El "Cerebro" de los filtros
 */
function aplicarFiltros() {
    const busqueda = document.getElementById('search-artistas').value.toLowerCase();

    const artistasFiltrados = allArtistas.filter(artista => {
        if (busqueda) {
            const nombreCompleto = `${artista.nombre} ${artista.apellido}`.toLowerCase();
            const nombreArtistico = artista.nombre_artistico.toLowerCase();
            const email = artista.email.toLowerCase();
            
            if (!nombreCompleto.includes(busqueda) && !nombreArtistico.includes(busqueda) && !email.includes(busqueda)) {
                return false;
            }
        }
        return true;
    });

    renderArtistas(artistasFiltrados);
}

/**
 * 4. "Dibuja" la tabla con la lista de artistas
 * @param {Array} artistas - La lista (ya filtrada) a mostrar.
 */
function renderArtistas(artistas) {
    const tbody = document.getElementById('artistas-tabla-body');
    
    if (artistas.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="empty-state">
                    <i class="fas fa-search"></i>
                    <h4>No se encontraron artistas</h4>
                    <p>Intenta con otros términos de búsqueda</p>
                </td>
            </tr>
        `;
    } else {
        let html = '';
        artistas.forEach(artista => {
            const estadoClase = artista.active == 1 ? 'status-active' : 'status-inactive';
            const estadoTexto = artista.active == 1 ? 'Activo' : 'Inactivo';

            html += `
                <tr>
                    <td><strong>${escapeHtml(artista.nombre_artistico)}</strong></td>
                    <td>${escapeHtml(artista.nombre)} ${escapeHtml(artista.apellido)}</td>
                    <td>${escapeHtml(artista.email)}</td>
                    <td>${escapeHtml(artista.telefono || 'N/A')}</td>
                    <td><span class="status-badge ${estadoClase}">${estadoTexto}</span></td>
                    <td class="acciones-cell">
                        <a href="artista-form.php?action=editar&id=${artista.id_artista}" class="btn-action btn-editar">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }
}

/**
 * 5. Función auxiliar para escapar HTML
 */
function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}