// js/directorio.js (Versión 3.0 - Con filtrado en memoria)

// --- Variables Globales ---
let allRegistros = []; // Nuestra "fuente de verdad"
let ordenActual = {
    campo: 'nombre',
    asc: true
};

// --- FUNCIÓN DE ARRANQUE ---
document.addEventListener('DOMContentLoaded', () => {
    // 1. Cargamos los datos
    cargarDirectorio();
    
    // 2. Activamos filtros y ordenamiento
    configurarFiltros();
    configurarOrdenamiento();
});

/**
 * 1. Carga TODOS los registros desde el backend
 */
function cargarDirectorio() {
    const tablaBody = document.getElementById('directorio-tabla-body');
    tablaBody.innerHTML = `
        <tr>
            <td colspan="6" class="loading-text">
                <i class="fas fa-spinner fa-spin"></i> Cargando directorio...
            </td>
        </tr>
    `;

    fetch('php/getDirectorio.php')
        .then(response => response.json())
        .then(data => {
            if (!data.success) throw new Error(data.message || 'Error API');
            
            allRegistros = data.directorio; // Guardamos en la lista maestra
            
            // Mostramos los datos por primera vez (ordenados por defecto)
            aplicarFiltrosYOrdenamiento();
            
            // Actualizamos las tarjetas de estadísticas (¡solo una vez!)
            actualizarEstadisticas(allRegistros);
        })
        .catch(error => {
            console.error('Error al cargar el directorio:', error);
            tablaBody.innerHTML = `
                <tr>
                    <td colspan="6" class="empty-state">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h4>Error al cargar datos</h4>
                        <p>${error.message}</p>
                    </td>
                </tr>
            `;
            // También ponemos 0 en las tarjetas si falla la carga
            actualizarEstadisticas([]); 
        });
}

/**
 * 2. Configura los 'listeners' de los filtros y botones
 * (Se conectan a 'aplicarFiltrosYOrdenamiento', no a 'aplicarFiltros')
 */
function configurarFiltros() {
    document.getElementById('filter-tipo').addEventListener('change', aplicarFiltrosYOrdenamiento);
    document.getElementById('search-directorio').addEventListener('input', aplicarFiltrosYOrdenamiento);
    document.getElementById('btn-exportar').addEventListener('click', exportarCSV);
}

/**
 * 3. Configura los 'listeners' de los cabezales de la tabla para ordenar
 */
function configurarOrdenamiento() {
    document.querySelectorAll('.sortable').forEach(header => {
        header.addEventListener('click', () => {
            const campo = header.dataset.sort;
            
            if (ordenActual.campo === campo) {
                ordenActual.asc = !ordenActual.asc;
            } else {
                ordenActual.campo = campo;
                ordenActual.asc = true;
            }
            
            document.querySelectorAll('.sortable').forEach(h => h.classList.remove('asc', 'desc'));
            header.classList.add(ordenActual.asc ? 'asc' : 'desc');
            
            aplicarFiltrosYOrdenamiento(); // Vuelve a dibujar con el nuevo orden
        });
    });
}

/**
 * 4. El "Cerebro" que aplica filtros Y ordenamiento
 * (Esta es la función que SÍ funciona)
 */
function aplicarFiltrosYOrdenamiento() {
    // Obtenemos los valores de los filtros
    const tipoFiltro = document.getElementById('filter-tipo').value;
    const busqueda = document.getElementById('search-directorio').value.toLowerCase();

    // --- A. FILTRAR ---
    const registrosFiltrados = allRegistros.filter(persona => {
        // Filtro por tipo
        if (tipoFiltro !== 'todos' && persona.tipo_persona !== tipoFiltro) {
            return false;
        }

        // Filtro por búsqueda
        if (busqueda) {
            const nombreCompleto = `${persona.nombre} ${persona.apellido}`.toLowerCase();
            const email = persona.email.toLowerCase();
            const telefono = (persona.telefono || '').toLowerCase();
            
            if (!nombreCompleto.includes(busqueda) && !email.includes(busqueda) && !telefono.includes(busqueda)) {
                return false;
            }
        }
        
        return true; // Pasa los filtros
    });

    // --- B. ORDENAR ---
    registrosFiltrados.sort((a, b) => {
        // Usamos la variable global 'ordenActual'
        let valorA = a[ordenActual.campo] || '';
        let valorB = b[ordenActual.campo] || '';
        
        if (typeof valorA === 'string') valorA = valorA.toLowerCase();
        if (typeof valorB === 'string') valorB = valorB.toLowerCase();

        if (valorA < valorB) return ordenActual.asc ? -1 : 1;
        if (valorA > valorB) return ordenActual.asc ? 1 : -1;
        return 0;
    });

    // --- C. DIBUJAR ---
    mostrarDirectorio(registrosFiltrados);
}

/**
 * 5. "Dibuja" la tabla (La única función que toca el innerHTML)
 * @param {Array} registros - La lista (ya filtrada y ordenada) a mostrar.
 */
function mostrarDirectorio(registros) {
    const tbody = document.getElementById('directorio-tabla-body');
    
    if (registros.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="empty-state">
                    <i class="fas fa-search"></i>
                    <h4>No se encontraron resultados</h4>
                    <p>Intenta con otros términos de búsqueda o filtros</p>
                </td>
            </tr>
        `;
    } else {
        let html = '';
        registros.forEach(persona => {
            const tipoClase = persona.tipo_persona === 'Cliente' ? 'tipo-cliente' : 'tipo-artista';
            
            html += `
                <tr data-tipo="${persona.tipo_persona.toLowerCase()}" data-nombre="${persona.nombre.toLowerCase()} ${persona.apellido.toLowerCase()}">
                    <td>${escapeHtml(persona.nombre)}</td>
                    <td>${escapeHtml(persona.apellido)}</td>
                    <td>${escapeHtml(persona.email)}</td>
                    <td>${escapeHtml(persona.telefono || 'N/A')}</td>
                    <td>
                        <span class="tipo-badge ${tipoClase}">${persona.tipo_persona}</span>
                    </td>
                    <td class="acciones-cell">
                        <button class="btn-action btn-view" onclick="verDetalle('${persona.tipo_persona}', ${persona.id_persona})">
                            <i class="fas fa-eye"></i> Ver
                        </button>
                        <button class="btn-action btn-edit" onclick="editarRegistro('${persona.tipo_persona}', ${persona.id_persona})">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }
    
    // Actualiza el contador de la tabla
    document.getElementById('registros-count').textContent = `${registros.length} registros encontrados`;
}

/**
 * 6. Actualiza las estadísticas (Usa la lista maestra 'allRegistros')
 */
function actualizarEstadisticas(registros) {
    const totalClientes = registros.filter(r => r.tipo_persona === 'Cliente').length;
    const totalArtistas = registros.filter(r => r.tipo_persona === 'Artista').length;
    
    document.getElementById('total-clientes').textContent = totalClientes;
    document.getElementById('total-artistas').textContent = totalArtistas;
    document.getElementById('total-registros').textContent = registros.length;
}

/**
 * 7. Exporta la vista ACTUAL (filtrada) a CSV
 */
function exportarCSV() {
    // Re-aplicamos los filtros para obtener la lista actual
    const tipoFiltro = document.getElementById('filter-tipo').value;
    const busqueda = document.getElementById('search-directorio').value.toLowerCase();
    
    const registrosFiltrados = allRegistros.filter(persona => {
        if (tipoFiltro !== 'todos' && persona.tipo_persona !== tipoFiltro) return false;
        if (busqueda) {
            const nombreCompleto = `${persona.nombre} ${persona.apellido}`.toLowerCase();
            const email = persona.email.toLowerCase();
            const telefono = (persona.telefono || '').toLowerCase();
            if (!nombreCompleto.includes(busqueda) && !email.includes(busqueda) && !telefono.includes(busqueda)) return false;
        }
        return true;
    });

    if (registrosFiltrados.length === 0) {
        alert('No hay datos para exportar');
        return;
    }
    
    let csv = 'Nombre,Apellido,Email,Telefono,Tipo\n';
    
    registrosFiltrados.forEach(persona => {
        const filaData = [
            `"${persona.nombre}"`,
            `"${persona.apellido}"`,
            `"${persona.email}"`,
            `"${persona.telefono || 'N/A'}"`,
            `"${persona.tipo_persona}"`
        ];
        csv += filaData.join(',') + '\n';
    });
    
    // Crear y descargar archivo
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', `directorio_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/**
 * 8. Función auxiliar para escapar HTML
 */
function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// --- 9. FUNCIONES DE ACCIÓN (Placeholders) ---
function verDetalle(tipo, id_persona) {
    console.log('Viendo perfil de:', tipo, id_persona);
    // Redirigimos a la nueva página de perfil
    window.location.href = `perfil-persona.php?id=${id_persona}`;
}

function editarRegistro(tipo, id_persona) {
    console.log('Editando:', tipo, id_persona);
    window.location.href = `persona-form.php?id=${id_persona}`;
}