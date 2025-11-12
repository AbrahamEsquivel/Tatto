// --- FUNCIÓN DE ARRANQUE ---
document.addEventListener('DOMContentLoaded', () => {
    // 1. Cargamos todas las estadísticas
    cargarEstadisticas();

    // 2. Le damos "vida" al botón del cursor
    const btnRecordatorios = document.getElementById('btn-enviar-recordatorios');
    if (btnRecordatorios) {
        btnRecordatorios.addEventListener('click', () => {
            ejecutarTareaRecordatorios();
        });
    }
});


/**
 * Función principal: Carga TODOS los datos del dashboard.
 */
function cargarEstadisticas() {
    
    fetch('php/getDashboardStats.php')
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                // Ahora sí podemos manejar el error que venga del PHP
                throw new Error(data.message || 'Error en la respuesta del API');
            }

            // 1. Pintamos las tarjetas
            actualizarKPIs(data.kpis);
            
            // 2. Pintamos el reporte de artistas
            actualizarReporteArtistas(data.reporte_artistas);

            // 3. Pintamos la bitácora
            actualizarBitacora(data.log_notificaciones);
        })
        .catch(error => {
            console.error('Error al cargar el dashboard:', error);
            // ¡AQUÍ ESTÁ LA FUNCIÓN QUE FALTABA!
            mostrarErrorEnDashboard(error.message);
        });
}

/**
 * Pinta los números en las 4 tarjetas
 */
function actualizarKPIs(kpis) {
    document.getElementById('stat-ingresos-totales').innerHTML = formatCurrency(kpis.ingresos_totales);
    document.getElementById('stat-citas-pendientes').innerHTML = kpis.citas_pendientes;
    document.getElementById('stat-precio-promedio').innerHTML = formatCurrency(kpis.precio_promedio);
    document.getElementById('stat-total-citas').innerHTML = kpis.total_citas;
}

/**
 * "Dibuja" las filas de la tabla del reporte de artistas
 * MODIFICADA PARA USAR 'es_total_general'
 */
function actualizarReporteArtistas(reporte) {
    const tablaBody = document.getElementById('reporte-tabla-body');
    tablaBody.innerHTML = ''; // Limpiamos "Cargando..."

    if (reporte.length === 0) {
        tablaBody.innerHTML = '<tr><td colspan="3">No hay ingresos completados para mostrar.</td></tr>';
        return;
    }

    reporte.forEach(fila => {
        let filaHTML = '';
        
        // 'es_total_general' será '1' para la fila ROLLUP y '0' para las demás
        if (fila.es_total_general == 1) { 
            // ¡Esta es la fila del GRAN TOTAL!
            filaHTML = `
                <tr style="font-weight: bold; border-top: 2px solid #ff0000ff;">
                    <td>GRAN TOTAL (Calculado por ROLLUP)</td>
                    <td>${formatCurrency(fila.ingresos_generados)}</td>
                    
                </tr>
            `;
        } else {
            // Esta es una fila de artista normal
            filaHTML = `
                <tr>
                    <td>${fila.artista_nombre}</td>
                    <td>${formatCurrency(fila.ingresos_generados)}</td>
                    <td>${fila.citas_completadas}</td>
                </tr>
            `;
        }
        tablaBody.innerHTML += filaHTML;
    });
}

/**
 * "Dibuja" las filas de la tabla de bitácora.
 */
function actualizarBitacora(logs) {
    const tablaBody = document.getElementById('bitacora-tabla-body');
    tablaBody.innerHTML = ''; // Limpiamos "Cargando..."

    if (logs.length === 0) {
        tablaBody.innerHTML = '<tr><td colspan="3">La bitácora de notificaciones está vacía.</td></tr>';
        return;
    }

    logs.forEach(log => {
        const filaHTML = `
            <tr>
                <td>${log.fecha_envio}</td>
                <td>${log.id_cita || 'N/A'}</td>
                <td>${log.mensaje}</td>
            </tr>
        `;
        tablaBody.innerHTML += filaHTML;
    });
}

/**
 * Llama al script que ejecuta el cursor (sp_EnviarRecordatoriosPendientes)
 */
function ejecutarTareaRecordatorios() {
    const btn = document.getElementById('btn-enviar-recordatorios');
    const statusSpan = document.getElementById('task-status');

    btn.disabled = true;
    statusSpan.innerHTML = 'Procesando...';
    statusSpan.style.color = '#007bff';
    statusSpan.style.display = 'inline';

    fetch('php/runRecordatorios.php', { method: 'POST' })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            statusSpan.innerHTML = '¡Completado! Actualizando...';
            statusSpan.style.color = '#28a745';
            
            setTimeout(() => {
                cargarEstadisticas();
                statusSpan.style.display = 'none';
            }, 1500);

        } else {
            throw new Error(data.message || 'Error desconocido al ejecutar la tarea.');
        }
    })
    .catch(error => {
        console.error('Error en la tarea:', error);
        statusSpan.innerHTML = `Error: ${error.message}`;
        statusSpan.style.color = '#dc3545';
    })
    .finally(() => {
        setTimeout(() => {
            btn.disabled = false;
        }, 2000);
    });
}

// ⬇️ ¡LA FUNCIÓN QUE FALTABA! ⬇️
/**
 * Muestra un mensaje de error en todas las secciones del dashboard.
 */
function mostrarErrorEnDashboard(mensaje) {
    // Tarjetas
    document.getElementById('stat-ingresos-totales').innerHTML = '<span style="color: #dc3545; font-size: 1rem;">Error</span>';
    document.getElementById('stat-citas-pendientes').innerHTML = '<span style="color: #dc3545; font-size: 1rem;">Error</span>';
    document.getElementById('stat-precio-promedio').innerHTML = '<span style="color: #dc3545; font-size: 1rem;">Error</span>';
    document.getElementById('stat-total-citas').innerHTML = '<span style="color: #dc3545; font-size: 1rem;">Error</span>';
    
    // Tabla de Reporte
    const tablaBodyReporte = document.getElementById('reporte-tabla-body');
    tablaBodyReporte.innerHTML = `<tr><td colspan="3" style="color: #dc3545;">Error al cargar reporte: ${mensaje}</td></tr>`;

    // Tabla de Bitácora
    const tablaBodyBitacora = document.getElementById('bitacora-tabla-body');
    tablaBodyBitacora.innerHTML = `<tr><td colspan="3" style="color: #dc3545;">Error al cargar bitácora: ${mensaje}</td></tr>`;
}


/**
 * (Helper) Función para formatear dinero (sin cambios)
 */
function formatCurrency(value) {
    const numberValue = parseFloat(value) || 0;
    return '$' + numberValue.toLocaleString('es-MX', { 
        minimumFractionDigits: 2, 
        maximumFractionDigits: 2 
    });
}