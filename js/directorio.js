// --- FUNCIÓN DE ARRANQUE ---
// Se ejecuta en cuanto la página HTML (directorio.php) está lista.
document.addEventListener('DOMContentLoaded', () => {
    cargarDirectorio();
});


/**
 * Función principal: Llama al "backend" para obtener la lista del directorio.
 */
function cargarDirectorio() {
    
    const tablaBody = document.getElementById('directorio-tabla-body');
    
    fetch('php/getDirectorio.php')
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
            if (data.directorio.length === 0) {
                tablaBody.innerHTML = '<tr><td colspan="5">No hay personas en el directorio.</td></tr>';
                return;
            }

            // 5. "Dibujamos" cada fila de la tabla
            data.directorio.forEach(persona => {
                
                // Asignamos una clase CSS bonita según el tipo
                const tipoClase = persona.tipo_persona === 'Cliente' ? 'tipo-cliente' : 'tipo-artista';
                
                const filaHTML = `
                    <tr>
                        <td>${persona.nombre}</td>
                        <td>${persona.apellido}</td>
                        <td>${persona.email}</td>
                        <td>${persona.telefono || 'N/A'}</td>
                        <td>
                            <span class_name="${tipoClase}">${persona.tipo_persona}</span>
                        </td>
                    </tr>
                `;
                // Añadimos la fila al HTML
                tablaBody.innerHTML += filaHTML;
            });

        })
        .catch(error => {
            // --- MANEJO DE CUALQUIER ERROR ---
            console.error('Error al cargar el directorio:', error);
            tablaBody.innerHTML = `<tr><td colspan="5">Error al cargar datos: ${error.message}</td></tr>`;
        });
}