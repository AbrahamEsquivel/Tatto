// --- FUNCIÓN DE ARRANQUE ---
// Esto se ejecuta en cuanto la página HTML (dashboard.php) está lista.
document.addEventListener('DOMContentLoaded', () => {
    cargarEstadisticas();
});


/**
 * Función principal: Llama al "backend" para obtener las estadísticas.
 */
function cargarEstadisticas() {
    
    fetch('php/getDashboardStats.php')
        .then(response => {
            // Primer filtro: ¿Se cayó la red?
            if (!response.ok) {
                throw new Error(`Error de red (${response.status})`);
            }
            return response.json();
        })
        .then(data => {
            // Segundo filtro: ¿El API nos devolvió un error? (ej. No logueado)
            if (!data.success) {
                throw new Error(data.message || 'Error en la respuesta del API');
            }

            // --- ¡ÉXITO! Tenemos los datos ---
            // 1. Mandamos los datos de las tarjetas a su función
            actualizarKPIs(data.kpis);
            
            // 2. Mandamos los datos del reporte a su función
            actualizarReporteArtistas(data.reporte_artistas);
        })
        .catch(error => {
            // --- MANEJO DE CUALQUIER ERROR ---
            console.error('Error al cargar el dashboard:', error);
            // Mostramos el error en la página para que el admin lo vea
            mostrarErrorEnDashboard(error.message);
        });
}

/**
 * Pinta los números en las 4 tarjetas de estadísticas.
 */
function actualizarKPIs(kpis) {
    document.getElementById('stat-ingresos-totales').innerHTML = formatCurrency(kpis.ingresos_totales);
    document.getElementById('stat-citas-pendientes').innerHTML = kpis.citas_pendientes;
    document.getElementById('stat-precio-promedio').innerHTML = formatCurrency(kpis.precio_promedio);
    document.getElementById('stat-total-citas').innerHTML = kpis.total_citas;
}

/**
 * "Dibuja" las filas de la tabla del reporte de artistas.
 */
function actualizarReporteArtistas(reporte) {
    const tablaBody = document.getElementById('reporte-tabla-body');
    
    // 1. Limpiamos el "Cargando..."
    tablaBody.innerHTML = '';

    // 2. Verificamos si hay datos que mostrar
    if (reporte.length === 0) {
        tablaBody.innerHTML = '<tr><td colspan="3">No hay ingresos completados para mostrar.</td></tr>';
        return;
    }

    // 3. Creamos una fila (<tr>) por cada artista
    reporte.forEach(artista => {
        const filaHTML = `
            <tr>
                <td>${artista.artista_nombre}</td>
                <td>${formatCurrency(artista.ingresos_generados)}</td>
                <td>${artista.citas_completadas}</td>
            </tr>
        `;
        // Añadimos la fila al HTML
        tablaBody.innerHTML += filaHTML;
    });
}

/**
 * Muestra un mensaje de error en todas las tarjetas y la tabla.
 */
function mostrarErrorEnDashboard(mensaje) {
    document.getElementById('stat-ingresos-totales').innerHTML = 'Error';
    document.getElementById('stat-citas-pendientes').innerHTML = 'Error';
    document.getElementById('stat-precio-promedio').innerHTML = 'Error';
    document.getElementById('stat-total-citas').innerHTML = 'Error';

    const tablaBody = document.getElementById('reporte-tabla-body');
    tablaBody.innerHTML = `<tr><td colspan="3">Error al cargar datos: ${mensaje}</td></tr>`;
}

/**
 * (Helper) Función para formatear números como dinero (ej. $1,500.00)
 */
function formatCurrency(value) {
    const numberValue = parseFloat(value) || 0;
    return '$' + numberValue.toLocaleString('es-MX', { 
        minimumFractionDigits: 2, 
        maximumFractionDigits: 2 
    });
}