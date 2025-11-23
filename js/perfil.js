// js/perfil.js

// --- FUNCIÓN DE ARRANQUE ---
document.addEventListener('DOMContentLoaded', () => {
    // 1. Obtenemos el ID de la persona desde la URL
    const urlParams = new URLSearchParams(window.location.search);
    const idPersona = urlParams.get('id');

    if (!idPersona) {
        // Si no hay ID, mostramos un error general
        document.body.innerHTML = "<h1>Error: No se especificó un ID de persona.</h1>";
        return;
    }

    // 2. Llamamos al backend para cargar todos los datos
    cargarDatosPerfil(idPersona);
});

/**
 * Función principal: Llama al backend con el ID de la persona
 */
async function cargarDatosPerfil(id) {
    try {
        const response = await fetch(`php/getPerfilData.php?id=${id}`);
        if (!response.ok) throw new Error('Error de red al contactar al servidor.');

        const data = await response.json();
        if (!data.success) throw new Error(data.message || 'Error en la respuesta del API.');

        // --- ¡ÉXITO! Tenemos los datos ---
        // Enviamos cada parte del JSON a su función de "pintado"
        pintarKPIs(data.kpis, data.es_artista);
        pintarTablaCitas(data.citas);
        pintarTablaPagos(data.pagos);

    } catch (error) {
        console.error('Error al cargar datos del perfil:', error);
        // Mostramos un error general en todas las secciones
        document.getElementById('stat-citas-totales').innerHTML = '<span style="color:red; font-size:1rem;">Error</span>';
        document.getElementById('stat-gasto-total').innerHTML = '<span style="color:red; font-size:1rem;">Error</span>';
        document.getElementById('tabla-citas-persona').innerHTML = `<tr><td colspan="5" style="color:red;">${error.message}</td></tr>`;
        document.getElementById('tabla-pagos-persona').innerHTML = `<tr><td colspan="4" style="color:red;">${error.message}</td></tr>`;
    }
}

/**
 * "Pinta" las tarjetas de estadísticas (KPIs)
 */
function pintarKPIs(kpis, es_artista) { // <--- Recibimos el nuevo dato
    document.getElementById('stat-citas-totales').textContent = kpis.total_citas;
    
    const labelGasto = document.querySelector('#stat-gasto-total').previousElementSibling; // El <h3>
    
    if (es_artista) {
        labelGasto.textContent = "Ingresos Generados";
        document.querySelector('.perfil-card h2').innerHTML += ' <span style="font-size:0.6em; background:#3B82F6; color:white; padding:3px 8px; border-radius:4px; vertical-align:middle;">ARTISTA</span>';
    } else {
        labelGasto.textContent = "Gasto Total";
    }
    
    document.getElementById('stat-gasto-total').textContent = formatCurrency(kpis.gasto_total);
}

/**
 * "Pinta" la tabla de historial de citas
 */
function pintarTablaCitas(citas) {
    const tbody = document.getElementById('tabla-citas-persona');
    tbody.innerHTML = ''; // Limpiamos el "Cargando..."

    if (citas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5">Esta persona no tiene citas registradas.</td></tr>';
        return;
    }

    citas.forEach(cita => {
        const filaHTML = `
            <tr>
                <td>${cita.id_cita}</td>
                <td>${formatearFechaSimple(cita.fecha_hora)}</td>
                <td>${escapeHtml(cita.tatuaje_descripcion.substring(0, 40))}...</td>
                <td>${escapeHtml(cita.artista_nombre)}</td>
                <td>
                    <span class="estado-cita estado-${cita.estado_cita.toLowerCase()}">
                        ${cita.estado_cita}
                    </span>
                </td>
            </tr>
        `;
        tbody.innerHTML += filaHTML;
    });
}

/**
 * "Pinta" la tabla de historial de pagos
 */
function pintarTablaPagos(pagos) {
    const tbody = document.getElementById('tabla-pagos-persona');
    tbody.innerHTML = ''; // Limpiamos el "Cargando..."

    // Actualizamos el colspan a 5 porque ahora son 5 columnas
    if (pagos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:#888;">Esta persona no tiene pagos registrados.</td></tr>';
        return;
    }

    pagos.forEach(pago => {
        const filaHTML = `
            <tr>
                <td>#${pago.id_pago}</td>
                <td>${formatearFechaSimple(pago.fecha_pago)}</td>
                <td>${escapeHtml(pago.tipo_pago)}</td>
                
                <td>${escapeHtml(pago.metodo_pago)}</td>
                
                <td class="monto-pago">${formatCurrency(pago.monto)}</td>
            </tr>
        `;
        tbody.innerHTML += filaHTML;
    });
}


// --- FUNCIONES DE AYUDA (Helpers) ---

/**
 * (Helper) Formatea números como dinero.
 * (Necesario porque este JS es nuevo)
 */
function formatCurrency(value) {
    const numberValue = parseFloat(value) || 0;
    return '$' + numberValue.toLocaleString('es-MX', { 
        minimumFractionDigits: 2, 
        maximumFractionDigits: 2 
    });
}

/**
 * (Helper) Formatea fechas (versión simple)
 */
function formatearFechaSimple(fecha) {
    if (!fecha) return 'N/A';
    const date = new Date(fecha);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric', month: 'short', day: 'numeric'
    });
}

/**
 * (Helper) Evita inyecciones XSS en el HTML
 */
function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}