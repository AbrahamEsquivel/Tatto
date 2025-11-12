// --- FUNCIÓN DE ARRANQUE ---
// Se ejecuta en cuanto la página HTML (historial-pagos.php) está lista.
document.addEventListener('DOMContentLoaded', () => {
    cargarHistorialPagos();
});

/**
 * Función principal: Llama al "backend" para obtener la lista de pagos.
 */
function cargarHistorialPagos() {
    
    const tablaBody = document.getElementById('pagos-tabla-body');
    
    fetch('php/getHistorialPagos.php')
        .then(response => {
            // 1. Filtro de red
            if (!response.ok) {
                throw new Error(`Error de red (${response.status})`);
            }
            return response.json();
        })
        .then(data => {
            // 2. Filtro de API (ej. no logueado)
            if (!data.success) {
                throw new Error(data.message || 'Error en la respuesta del API');
            }

            // --- ¡ÉXITO! Tenemos los datos ---
            // 3. Limpiamos el "Cargando..."
            tablaBody.innerHTML = '';

            // 4. Verificamos si hay datos que mostrar
            if (data.pagos.length === 0) {
                tablaBody.innerHTML = '<tr><td colspan="7">No se ha registrado ningún pago todavía.</td></tr>';
                return;
            }

            // 5. "Dibujamos" cada fila de la tabla
            data.pagos.forEach(pago => {
                
                const montoFormateado = (typeof formatCurrency === 'function') 
                                        ? formatCurrency(pago.monto) 
                                        : `$${pago.monto}`;

                // ⬇️ HTML MODIFICADO ⬇️
                const filaHTML = `
                    <tr>
                        <td>${pago.id_pago}</td>
                        <td>${pago.fecha_pago}</td>
                        <td>${pago.cliente_nombre} ${pago.cliente_apellido}</td>
                        <td>(ID: ${pago.id_cita}) ${pago.tatuaje_descripcion.substring(0, 30)}...</td>
                        <td>${pago.tipo_pago}</td>
                        <td>${pago.metodo_pago}</td>
                        <td class="monto-pago">${montoFormateado}</td>
                        
                        <td>
                            <a href="pago-form.php?id_pago=${pago.id_pago}" class="btn-editar-pago">
                                <i class="fas fa-pen"></i> Editar
                            </a>
                        </td>
                    </tr>
                `;
                // ⬆️ FIN DE HTML MODIFICADO ⬆️
                tablaBody.innerHTML += filaHTML;
            });
        })
        .catch(error => {
            // --- MANEJO DE CUALQUIER ERROR ---
            console.error('Error al cargar el historial de pagos:', error);
            tablaBody.innerHTML = `<tr><td colspan="7" style="color: #dc3545;">Error al cargar datos: ${error.message}</td></tr>`;
        });
}