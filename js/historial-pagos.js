// js/historial-pagos.js (Versión 3.0 - Con KPIs y Filtros)

// --- Variables Globales ---
let allPagos = []; // Nuestra "fuente de verdad"
let ordenActual = {
    campo: 'fecha', // Ordenar por fecha por defecto
    asc: false      // De más reciente a más antiguo
};

// --- FUNCIÓN DE ARRANQUE ---
document.addEventListener('DOMContentLoaded', () => {
    cargarHistorialPagos();
    configurarFiltros();
    configurarOrdenamiento();
});

/**
 * 1. Carga TODOS los registros desde el backend
 */
function cargarHistorialPagos() {
    const tablaBody = document.getElementById('pagos-tabla-body');
    tablaBody.innerHTML = `
        <tr>
            <td colspan="8" class="loading-text">
                <i class="fas fa-spinner fa-spin"></i> Cargando historial...
            </td>
        </tr>
    `;

    fetch('php/getHistorialPagos.php')
        .then(response => response.json())
        .then(data => {
            if (!data.success) throw new Error(data.message || 'Error API');
            
            allPagos = data.pagos; // Guardamos en la lista maestra
            
            // Mostramos los datos por primera vez (ordenados por defecto)
            aplicarFiltrosYOrdenamiento();
            
            // NOTA: Las estadísticas se actualizan DENTRO de aplicarFiltrosYOrdenamiento
            // para que los KPIs coincidan con la tabla filtrada.
        })
        .catch(error => {
            console.error('Error al cargar el historial:', error);
            tablaBody.innerHTML = `
                <tr>
                    <td colspan="8" class="empty-state">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h4>Error al cargar datos</h4>
                        <p>${error.message}</p>
                    </td>
                </tr>
            `;
            // Ponemos KPIs en 0 si falla la carga
            actualizarKPIs([]); 
        });
}

/**
 * 2. Configura los 'listeners' de los filtros
 */
function configurarFiltros() {
    document.getElementById('filter-fecha-desde').addEventListener('change', aplicarFiltrosYOrdenamiento);
    document.getElementById('filter-fecha-hasta').addEventListener('change', aplicarFiltrosYOrdenamiento);
    document.getElementById('filter-metodo').addEventListener('change', aplicarFiltrosYOrdenamiento);
    document.getElementById('search-pagos').addEventListener('input', aplicarFiltrosYOrdenamiento);
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
 */
function aplicarFiltrosYOrdenamiento() {
    // Obtenemos los valores de los filtros
    const fechaDesde = document.getElementById('filter-fecha-desde').value;
    const fechaHasta = document.getElementById('filter-fecha-hasta').value;
    const metodoFiltro = document.getElementById('filter-metodo').value;
    const busqueda = document.getElementById('search-pagos').value.toLowerCase();

    // --- A. FILTRAR ---
    const pagosFiltrados = allPagos.filter(pago => {
        // Filtro por Fecha Desde
        if (fechaDesde && pago.fecha_pago < fechaDesde) {
            return false;
        }
        // Filtro por Fecha Hasta
        if (fechaHasta && pago.fecha_pago > fechaHasta) {
            return false;
        }
        // Filtro por Método
        if (metodoFiltro !== 'todos' && pago.metodo_pago !== metodoFiltro) {
            return false;
        }
        // Filtro por búsqueda
        if (busqueda) {
            const cliente = `${pago.cliente_nombre} ${pago.cliente_apellido}`.toLowerCase();
            const descripcion = pago.tatuaje_descripcion.toLowerCase();
            const id = pago.id_pago.toString();
            
            if (!cliente.includes(busqueda) && !descripcion.includes(busqueda) && !id.includes(busqueda)) {
                return false;
            }
        }
        return true; // Pasa los filtros
    });

    // --- B. ACTUALIZAR KPIs ---
    // (Lo hacemos DESPUÉS de filtrar, para que los KPIs coincidan)
    actualizarKPIs(pagosFiltrados);

    // --- C. ORDENAR ---
    pagosFiltrados.sort((a, b) => {
        let valorA = a[ordenActual.campo] || '';
        let valorB = b[ordenActual.campo] || '';

        // Manejo especial para números (monto) y fechas
        if (ordenActual.campo === 'monto') {
            valorA = parseFloat(valorA);
            valorB = parseFloat(valorB);
        } else if (ordenActual.campo === 'fecha' || ordenActual.campo === 'id') {
             valorA = new Date(valorA).getTime() || 0; // Convertir fecha a número
             valorB = new Date(valorB).getTime() || 0;
             if(ordenActual.campo === 'id') { // IDs son números
                 valorA = parseInt(a['id_pago']);
                 valorB = parseInt(b['id_pago']);
             }
        } else if (typeof valorA === 'string') {
            valorA = valorA.toLowerCase();
            valorB = valorB.toLowerCase();
        }

        if (valorA < valorB) return ordenActual.asc ? -1 : 1;
        if (valorA > valorB) return ordenActual.asc ? 1 : -1;
        return 0;
    });

    // --- D. DIBUJAR ---
    renderTabla(pagosFiltrados);
}

/**
 * 5. "Dibuja" la tabla (La única función que toca el innerHTML)
 * @param {Array} pagos - La lista (ya filtrada y ordenada) a mostrar.
 */
function renderTabla(pagos) {
    const tbody = document.getElementById('pagos-tabla-body');
    
    if (pagos.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="empty-state">
                    <i class="fas fa-search"></i>
                    <h4>No se encontraron resultados</h4>
                    <p>Intenta con otros términos de búsqueda o filtros</p>
                </td>
            </tr>
        `;
    } else {
        let html = '';
        pagos.forEach(pago => {
            const tipoClase = mapearClaseTipo(pago.tipo_pago);
            const metodoClase = mapearClaseMetodo(pago.metodo_pago);
            
            html += `
                <tr>
                    <td>#${pago.id_pago}</td>
                    <td>${formatearFechaSimple(pago.fecha_pago)}</td>
                    <td>${escapeHtml(pago.cliente_nombre)} ${escapeHtml(pago.cliente_apellido)}</td>
                    <td>(ID: ${pago.id_cita}) ${escapeHtml(pago.tatuaje_descripcion.substring(0, 30))}...</td>
                    <td><span class="tipo-badge ${tipoClase}">${pago.tipo_pago}</span></td>
                    <td><span class="metodo-badge ${metodoClase}">${pago.metodo_pago}</span></td>
                    <td class="monto-pago">${formatCurrency(pago.monto)}</td>
                    <td class="acciones-cell">
                        <a href="pago-form.php?id_pago=${pago.id_pago}" class="btn-editar-pago">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }
    
    // Actualiza el contador de la tabla
    document.getElementById('pagos-count').textContent = `${pagos.length} registros encontrados`;
}

/**
 * 6. Actualiza las estadísticas (KPIs)
 * @param {Array} pagos - La lista (ya filtrada) sobre la cual calcular.
 */
function actualizarKPIs(pagos) {
    let totalIngresos = 0;
    let totalPagos = pagos.length;
    let promedioPago = 0;

    if (totalPagos > 0) {
        // Usamos reduce para sumar todos los montos de forma segura
        totalIngresos = pagos.reduce((sum, pago) => sum + parseFloat(pago.monto), 0);
        promedioPago = totalIngresos / totalPagos;
    }

    document.getElementById('total-ingresos').textContent = formatCurrency(totalIngresos);
    document.getElementById('total-pagos').textContent = totalPagos;
    document.getElementById('promedio-pago').textContent = formatCurrency(promedioPago);
}

// --- FUNCIONES DE AYUDA (Helpers) ---

function mapearClaseTipo(tipoDB) {
    // Mapea el nombre de la BD a la clase CSS que definiste
    switch (tipoDB) {
        case 'Anticipo':
            return 'tipo-adelanto';
        case 'Pago Completo':
            return 'tipo-completo';
        case 'Liquidacion':
            return 'tipo-parcial'; // Asumo que liquidación es un pago parcial
        default:
            return '';
    }
}

function mapearClaseMetodo(metodoDB) {
    // Mapea el nombre de la BD a la clase CSS
    switch (metodoDB) {
        case 'Efectivo':
            return 'metodo-efectivo';
        case 'Tarjeta de Credito': // Asumo que tu DB dice esto
            return 'metodo-tarjeta';
        case 'Transferencia SPEI': // Asumo que tu DB dice esto
            return 'metodo-transferencia';
        default:
            return '';
    }
}

function formatearFechaSimple(fecha) {
    if (!fecha) return 'N/A';
    const date = new Date(fecha);
    // Ajusta la fecha (los inputs de fecha no manejan bien la zona horaria)
    const userTimezoneOffset = date.getTimezoneOffset() * 60000;
    return new Date(date.getTime() + userTimezoneOffset).toLocaleDateString('es-ES', {
        year: 'numeric', month: 'short', day: 'numeric'
    });
}

function formatCurrency(value) {
    const numberValue = parseFloat(value) || 0;
    return '$' + numberValue.toLocaleString('es-MX', { 
        minimumFractionDigits: 2, 
        maximumFractionDigits: 2 
    });
}

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}