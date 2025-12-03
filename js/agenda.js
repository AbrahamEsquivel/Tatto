// js/agenda.js (Versión 3.0 - Lógica de filtrado en memoria)

// --- Variable Global ---
// Guardaremos todas las citas aquí. Esta es nuestra "única fuente de verdad".
let allCitas = [];

// --- Función de Arranque ---
document.addEventListener('DOMContentLoaded', () => {
    // 1. Cargamos las citas por primera vez
    cargarCitas();
    
    // 2. Activamos los filtros
    configurarFiltros();

    // 3. Activamos los botones de Cancelar/Completar
    configurarListenersDeAcciones();
});

/**
 * 1. Carga TODAS las citas desde el servidor y las guarda en 'allCitas'
 */
function cargarCitas() {
    const container = document.getElementById('agenda-container');
    container.innerHTML = `
        <div class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Cargando citas...</p>
        </div>`;

    fetch('php/getCitas.php')
        .then(response => {
            if (!response.ok) throw new Error('Error de red');
            return response.json();
        })
        .then(citas => {
            allCitas = citas; // Guardamos la lista maestra
            actualizarEstadisticas(allCitas); // Calculamos los contadores
            aplicarFiltros(); // Mostramos las citas (con filtros por defecto)
        })
        .catch(error => {
            console.error('Error al cargar las citas:', error);
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h4>Error al cargar las citas</h4>
                    <p>Intenta más tarde</p>
                </div>`;
        });
}

/**
 * 2. Configura los 'listeners' para los filtros
 */
function configurarFiltros() {
    document.getElementById('filter-estado').addEventListener('change', aplicarFiltros);
    document.getElementById('filter-fecha').addEventListener('change', aplicarFiltros);
    document.getElementById('search-citas').addEventListener('input', aplicarFiltros);
}

/**
 * 3. Configura los 'listeners' para los botones de las tarjetas
 * (Usamos delegación de eventos para que funcione con tarjetas nuevas)
 */
function configurarListenersDeAcciones() {
    const container = document.getElementById('agenda-container');
    
    container.addEventListener('click', (event) => {
        
        // --- Lógica para Botón CANCELAR ---
        const botonCancelar = event.target.closest('.btn-cancelar');
        if (botonCancelar) {
            event.preventDefault();
            if (botonCancelar.classList.contains('btn-disabled')) return;
            
            const idCita = botonCancelar.dataset.id;
            if (confirm(`¿Estás seguro de que quieres CANCELAR la cita ID: ${idCita}?`)) {
                cancelarCita(idCita);
            }
        }

        // --- Lógica para Botón COMPLETAR ---
        const botonCompletar = event.target.closest('.btn-completar');
        if (botonCompletar) {
            event.preventDefault();
            if (botonCompletar.classList.contains('btn-disabled')) return;

            const idCita = botonCompletar.dataset.id;
            if (confirm(`¿Estás seguro de que quieres marcar como COMPLETADA la cita ID: ${idCita}?`)) {
                completarCita(idCita);
            }
        }
    });
}

/**
 * 4. El "Cerebro" de los filtros.
 * Filtra el array 'allCitas', NO el DOM.
 */
function aplicarFiltros() {
    // Obtenemos los valores de los filtros
    const estadoFiltro = document.getElementById('filter-estado').value;
    const fechaFiltro = document.getElementById('filter-fecha').value; // '2025-11-11'
    const busqueda = document.getElementById('search-citas').value.toLowerCase();

    // Filtramos el array 'allCitas'
    const citasFiltradas = allCitas.filter(cita => {
        // Filtro por estado
        if (estadoFiltro !== 'todas' && cita.estado_cita.toLowerCase() !== estadoFiltro) {
            return false;
        }

        // Filtro por fecha
        const fechaCita = cita.fecha_hora.split(' ')[0]; // '2025-11-11'
        if (fechaFiltro && fechaCita !== fechaFiltro) {
            return false;
        }

        // Filtro por búsqueda
        if (busqueda) {
            const cliente = `${cita.cliente_nombre} ${cita.cliente_apellido}`.toLowerCase();
            const artista = cita.artista_nombre.toLowerCase();
            const descripcion = cita.tatuaje_descripcion.toLowerCase();

            if (!cliente.includes(busqueda) && !artista.includes(busqueda) && !descripcion.includes(busqueda)) {
                return false;
            }
        }

        // Si pasó todos los filtros, la mostramos
        return true;
    });

    // Enviamos la lista (ya filtrada) a "dibujar"
    renderCitas(citasFiltradas);
}

/**
 * 5. "Dibuja" las tarjetas en el HTML.
 * @param {Array} citasAMostrar - La lista (ya filtrada) que se debe mostrar.
 */
function renderCitas(citasAMostrar) {
    const container = document.getElementById('agenda-container');
    container.innerHTML = ''; // Limpiamos el contenedor

    if (citasAMostrar.length === 0) {
        // Si no hay citas, mostramos el mensaje de "vacío"
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <h4>No hay citas programadas</h4>
                <p>No se encontraron citas con los filtros aplicados</p>
            </div>
        `;
    } else {
        // Si hay citas, creamos el HTML
        let html = '';
        citasAMostrar.forEach(cita => {
            html += generarTarjetaCita(cita);
        });
        container.innerHTML = html;
    }
    
    // Actualizamos el contador
    document.getElementById('citas-count').textContent = `${citasAMostrar.length} citas encontradas`;
}

/**
 * 6. Genera el HTML de una sola tarjeta (Tu función de diseño)
 */
function generarTarjetaCita(cita) {
    const isCancelada = cita.estado_cita === 'Cancelada';
    const isCompletada = cita.estado_cita === 'Completada';
    const isFinalizada = isCancelada || isCompletada;
    
    const estadoClass = getEstadoClass(cita.estado_cita);
    const estadoText = cita.estado_cita;
    const fechaRaw = cita.fecha_hora.split(' ')[0];

    // --- ⬇️ LÓGICA FINANCIERA ⬇️ ---
    const precio = parseFloat(cita.precio_total) || 0;
    const pagado = parseFloat(cita.total_pagado) || 0; // Ahora sí vendrá de la BD
    const restante = precio - pagado;

    // Color del texto de "Abonado"
    let colorAbonado = '#9CA3AF'; // Gris
    if (pagado >= precio && precio > 0) colorAbonado = '#22C55E'; // Verde (Pagado)
    else if (pagado > 0) colorAbonado = '#EAB308'; // Amarillo (Abono parcial)
    // --- ⬆️ FIN LÓGICA ⬆️ ---
    
    return `
        <div class="cita-card ${estadoClass}" 
             data-id="${cita.id_cita}" 
             data-estado="${cita.estado_cita.toLowerCase()}"
             data-fecha="${fechaRaw}" 
        >
            <div class="cita-id">#${cita.id_cita}</div>
            <div class="cita-content">
                <div class="cita-header">
                    <h3 class="cita-cliente">${cita.cliente_nombre} ${cita.cliente_apellido}</h3>
                    <div class="cita-fecha">
                        <i class="fas fa-calendar"></i> ${formatearFecha(cita.fecha_hora)}
                    </div>
                </div>

                <div class="cita-details">
                    <div class="detail-item">
                        <span class="detail-label">Artista</span>
                        <span class="detail-value cita-artista"><i class="fas fa-user"></i> ${cita.artista_nombre}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Servicio</span>
                        <span class="detail-value cita-descripcion">${cita.tatuaje_descripcion}</span>
                    </div>
                    
                    <div style="display: flex; gap: 15px; margin-top: 10px; background: #1a1a1a; padding: 8px; border-radius: 6px;">
                        <div class="detail-item" style="flex: 1;">
                            <span class="detail-label">Precio</span>
                            <span class="detail-value" style="font-weight: bold; color: #fff;">
                                ${precio > 0 ? `$${precio.toFixed(2)}` : 'N/A'}
                            </span>
                        </div>
                        <div class="detail-item" style="flex: 1;">
                            <span class="detail-label">Abonado</span>
                            <span class="detail-value" style="font-weight: bold; color: ${colorAbonado};">
                                $${pagado.toFixed(2)}
                            </span>
                        </div>
                    </div>
                    
                    ${(precio > 0 && restante > 0.01 && !isCancelada) ? `
                    <div style="margin-top: 5px; font-size: 0.85em; color: #EF4444; text-align: right;">
                        <i class="fas fa-exclamation-circle"></i> Restan: <strong>$${restante.toFixed(2)}</strong>
                    </div>
                    ` : ''}
                </div>

                <div class="estado-cita ${estadoClass}">${estadoText}</div>
                
                <div class="card-actions">
                    <a href="edit-cita.php?id=${cita.id_cita}" 
                        class="btn-action btn-editar ${isCancelada ? 'btn-disabled' : ''}">
                            <i class="fas fa-edit"></i> Editar
                    </a>
                    ${!isFinalizada ? `
                    <button class="btn-action btn-completar" data-id="${cita.id_cita}">
                        <i class="fas fa-check"></i> Completar
                    </button>` : ''}
                    <a href="#" class="btn-action btn-cancelar ${isFinalizada ? 'btn-disabled' : ''}" data-id="${cita.id_cita}">
                       <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </div>
        </div>
    `;
}

// --- FUNCIONES DE AYUDA (Helpers) ---

function getEstadoClass(estado) {
    const estados = {
        'Pendiente': 'pendiente',
        'Confirmada': 'confirmada', 
        'Completada': 'completada',
        'Cancelada': 'cancelada'
    };
    return estados[estado] || 'pendiente';
}

function formatearFecha(fecha) {
    if (!fecha) return 'Fecha no especificada';
    const date = new Date(fecha);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
}

function actualizarEstadisticas(citas) {
    const counts = { pendiente: 0, confirmada: 0, completada: 0, cancelada: 0 };
    citas.forEach(cita => {
        const estado = cita.estado_cita.toLowerCase();
        if (counts.hasOwnProperty(estado)) {
            counts[estado]++;
        }
    });
    document.getElementById('count-pendientes').textContent = counts.pendiente;
    document.getElementById('count-confirmadas').textContent = counts.confirmada;
    document.getElementById('count-completadas').textContent = counts.completada;
    document.getElementById('count-canceladas').textContent = counts.cancelada;
}

// --- FUNCIONES DE ACCIÓN (Llamadas al Backend) ---

function cancelarCita(idCita) {
    console.log("Cancelando cita:", idCita);
    fetch('php/cancelarCita.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_cita=${idCita}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('¡Cita cancelada correctamente!');
            cargarCitas(); // Recarga la lista maestra
        } else {
            alert('Error al cancelar la cita: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error en fetch (cancelarCita):', error);
        alert('Error de conexión.');
    });
}

function completarCita(idCita) {
    // Usamos SweetAlert para confirmar
    Swal.fire({
        title: '¿Completar Cita?',
        text: "Esto marcará el trabajo como finalizado. Asegúrate de haber registrado el pago.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, completar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            procesarCompletado(idCita);
        }
    });
}

function procesarCompletado(idCita) {
    fetch('php/completarCita.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id_cita=${idCita}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire(
                '¡Completada!',
                'La cita ha sido marcada como completada.',
                'success'
            );
            cargarCitas(); 
        } else {
            Swal.fire(
                'No se pudo completar',
                data.message, 
                'error'
            );
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'Error de conexión.', 'error');
    });
}