// js/historial-pagos.js (Versión Final - Con Eliminar)

let allPagos = []; 
let ordenActual = { campo: 'fecha', asc: false };

document.addEventListener('DOMContentLoaded', () => {
    cargarHistorialPagos();
    configurarFiltros();
    configurarOrdenamiento();
    configurarAcciones(); // <--- NUEVO
});

function cargarHistorialPagos() {
    const tablaBody = document.getElementById('pagos-tabla-body');
    tablaBody.innerHTML = `<tr><td colspan="8" class="loading-text"><i class="fas fa-spinner fa-spin"></i> Cargando historial...</td></tr>`;

    fetch('php/getHistorialPagos.php')
        .then(response => response.json())
        .then(data => {
            if (!data.success) throw new Error(data.message || 'Error API');
            allPagos = data.pagos;
            aplicarFiltrosYOrdenamiento();
        })
        .catch(error => {
            console.error('Error:', error);
            tablaBody.innerHTML = `<tr><td colspan="8" class="empty-state"><h4>Error al cargar datos</h4><p>${error.message}</p></td></tr>`;
            actualizarKPIs([]); 
        });
}

function configurarFiltros() {
    document.getElementById('filter-fecha-desde').addEventListener('change', aplicarFiltrosYOrdenamiento);
    document.getElementById('filter-fecha-hasta').addEventListener('change', aplicarFiltrosYOrdenamiento);
    document.getElementById('filter-metodo').addEventListener('change', aplicarFiltrosYOrdenamiento);
    document.getElementById('filter-tipo').addEventListener('change', aplicarFiltrosYOrdenamiento);
    document.getElementById('search-pagos').addEventListener('input', aplicarFiltrosYOrdenamiento);
}

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
            aplicarFiltrosYOrdenamiento();
        });
    });
}

function aplicarFiltrosYOrdenamiento() {
    const fechaDesde = document.getElementById('filter-fecha-desde').value;
    const fechaHasta = document.getElementById('filter-fecha-hasta').value;
    const metodoFiltro = document.getElementById('filter-metodo').value;
    const tipoFiltro = document.getElementById('filter-tipo').value;
    const busqueda = document.getElementById('search-pagos').value.toLowerCase();

    const pagosFiltrados = allPagos.filter(pago => {
        if (fechaDesde && pago.fecha_pago < fechaDesde) return false;
        if (fechaHasta && pago.fecha_pago > fechaHasta) return false;
        if (metodoFiltro !== 'todos' && pago.metodo_pago !== metodoFiltro) return false;
        if (tipoFiltro !== 'todos' && pago.tipo_pago !== tipoFiltro) return false;

        if (busqueda) {
            const cliente = `${pago.cliente_nombre} ${pago.cliente_apellido}`.toLowerCase();
            const descripcion = pago.tatuaje_descripcion.toLowerCase();
            const id = pago.id_pago.toString();
            if (!cliente.includes(busqueda) && !descripcion.includes(busqueda) && !id.includes(busqueda)) return false;
        }
        return true;
    });

    actualizarKPIs(pagosFiltrados);

    pagosFiltrados.sort((a, b) => {
        let valorA = a[ordenActual.campo] || '';
        let valorB = b[ordenActual.campo] || '';

        if (ordenActual.campo === 'monto') {
            valorA = parseFloat(valorA);
            valorB = parseFloat(valorB);
        } else if (ordenActual.campo === 'fecha' || ordenActual.campo === 'id') {
             valorA = new Date(valorA).getTime() || parseInt(valorA) || 0;
             valorB = new Date(valorB).getTime() || parseInt(valorB) || 0;
        } else if (typeof valorA === 'string') {
            valorA = valorA.toLowerCase();
            valorB = valorB.toLowerCase();
        }

        if (valorA < valorB) return ordenActual.asc ? -1 : 1;
        if (valorA > valorB) return ordenActual.asc ? 1 : -1;
        return 0;
    });

    renderTabla(pagosFiltrados);
}

function renderTabla(pagos) {
    const tbody = document.getElementById('pagos-tabla-body');
    
    if (pagos.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="empty-state"><h4>No se encontraron resultados</h4></td></tr>`;
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
                        <a href="pago-form.php?id_pago=${pago.id_pago}" class="btn-editar-pago" title="Ver/Editar Fecha">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button class="btn-eliminar-pago" data-id="${pago.id_pago}" title="Eliminar Pago">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }
    document.getElementById('pagos-count').textContent = `${pagos.length} registros encontrados`;
}

// --- LÓGICA DE ELIMINAR ---
function configurarAcciones() {
    document.getElementById('pagos-tabla-body').addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-eliminar-pago');
        if (btn) {
            const idPago = btn.dataset.id;
            Swal.fire({
                title: '¿Eliminar Pago?',
                text: "Esta acción restará el monto del total abonado de la cita. ¿Estás seguro?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                background: '#1a1a1a', color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    eliminarPago(idPago);
                }
            });
        }
    });
}

function eliminarPago(id) {
    const formData = new FormData();
    formData.append('id_pago', id);

    fetch('php/eliminarPago.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Eliminado', text: 'El pago ha sido eliminado y el saldo actualizado.', icon: 'success',
                background: '#1a1a1a', color: '#fff'
            });
            cargarHistorialPagos(); // Recarga la tabla
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    });
}

// --- HELPERS ---
// (Aquí añade tus funciones auxiliares normales: actualizarKPIs, mapearClase..., formatCurrency, etc.)
// Copia y pega las funciones auxiliares del código anterior si se borraron, 
// son las mismas (actualizarKPIs, mapearClaseTipo, mapearClaseMetodo, formatearFechaSimple, formatCurrency, escapeHtml).
// Te pongo las básicas aquí para que no falle:
function actualizarKPIs(pagos) {
    let totalIngresos = 0;
    let totalPagos = pagos.length;
    if (totalPagos > 0) {
        totalIngresos = pagos.reduce((sum, pago) => sum + parseFloat(pago.monto), 0);
    }
    let promedioPago = totalPagos > 0 ? totalIngresos / totalPagos : 0;
    document.getElementById('total-ingresos').textContent = formatCurrency(totalIngresos);
    document.getElementById('total-pagos').textContent = totalPagos;
    document.getElementById('promedio-pago').textContent = formatCurrency(promedioPago);
}
function mapearClaseTipo(t) { if(t==='Anticipo')return 'tipo-adelanto'; if(t==='Pago Completo')return 'tipo-completo'; if(t==='Liquidacion')return 'tipo-parcial'; return ''; }
function mapearClaseMetodo(m) { if(m==='Efectivo')return 'metodo-efectivo'; if(m==='Tarjeta de Credito')return 'metodo-tarjeta'; if(m==='Transferencia SPEI')return 'metodo-transferencia'; return ''; }
function formatearFechaSimple(f) { if(!f)return 'N/A'; const d=new Date(f); const offset=d.getTimezoneOffset()*60000; return new Date(d.getTime()+offset).toLocaleDateString('es-ES',{year:'numeric',month:'short',day:'numeric'}); }
function formatCurrency(v) { return '$'+(parseFloat(v)||0).toLocaleString('es-MX',{minimumFractionDigits:2}); }
function escapeHtml(t) { if(t==null)return ''; const d=document.createElement('div'); d.textContent=t; return d.innerHTML; }