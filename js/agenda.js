document.addEventListener('DOMContentLoaded', () => {
    // Esta función ya la tenías
    cargarCitas();

    // ⬇️ NUEVO (PARTE 1) ⬇️
    // Lógica para "escuchar" clics en los botones de cancelar
    // Se añade al 'container' para que funcione con botones creados dinámicamente
    const container = document.getElementById('agenda-container');
    if (container) {
        container.addEventListener('click', (event) => {

            // Verificamos si el clic fue en un botón de cancelar
            if (event.target.classList.contains('btn-cancelar')) {
                event.preventDefault(); // Evita que el enlace <a> navegue

                // Obtenemos el ID que guardamos en 'data-id'
                const idCita = event.target.dataset.id;

                // Si el botón está deshabilitado, no hacemos nada
                if (event.target.classList.contains('btn-disabled')) {
                    return;
                }

                // Pedimos confirmación al admin
                if (confirm(`¿Estás seguro de que quieres CANCELAR la cita ID: ${idCita}? \n\nEsta acción cambiará el estado a 'Cancelada'.`)) {
                    cancelarCita(idCita);
                }
            }
        });
    }
    // ⬆️ FIN DE NUEVO (PARTE 1) ⬆️
});

function cargarCitas() {
    // Esta función no cambia en nada
    fetch('php/getCitas.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('La respuesta de la red no fue exitosa');
            }
            return response.json();
        })
        .then(citas => {
            console.log(citas);
            mostrarCitasEnHTML(citas);
        })
        .catch(error => {
            console.error('Error al cargar las citas:', error);
            const container = document.getElementById('agenda-container');
            container.innerHTML = "<p>Error al cargar la agenda. Intenta más tarde.</p>";
        });
}

function mostrarCitasEnHTML(citas) {
    const container = document.getElementById('agenda-container');
    container.innerHTML = '';

    if (citas.length === 0) {
        container.innerHTML = "<p>No hay citas programadas.</p>";
        return;
    }

    citas.forEach(cita => {
        // ⬇️ NUEVO (PARTE 2) ⬇️
        // Verificamos si la cita ya está cancelada
        const isCancelada = cita.estado_cita === 'Cancelada';

        // Añadimos una clase CSS a la tarjeta si está cancelada
        const cardClass = isCancelada ? 'cita-card card-cancelada' : 'cita-card';
        // ⬆️ FIN DE NUEVO (PARTE 2) ⬆️

        const citaHTML = `
                        <div class="${cardClass}">
                <h3>Cita ID: ${cita.id_cita}</h3>
                <p><strong>Fecha:</strong> ${cita.fecha_hora}</p>
                <p><strong>Cliente:</strong> ${cita.cliente_nombre} ${cita.cliente_apellido}</p>
                <p><strong>Artista:</strong> ${cita.artista_nombre}</p>
                <p><strong>Descripción:</strong> ${cita.tatuaje_descripcion}</p>
                                <p><strong>Estado:</strong> <span class="estado-cita estado-${cita.estado_cita.toLowerCase()}">${cita.estado_cita}</span></p>
                
                <div class="card-actions">
                    <a href="edit-cita.php?id=${cita.id_cita}" 
                       class="btn-editar ${isCancelada ? 'btn-disabled' : ''}">
                       Editar
                    </a>
                    
                    <a href="#" 
                       class="btn-cancelar ${isCancelada ? 'btn-disabled' : ''}" 
                       data-id="${cita.id_cita}">
                       Cancelar
                    </a>
                </div>
                            </div>
        `;
        container.innerHTML += citaHTML;
    });
}


// ⬇️ NUEVO (PARTE 4) ⬇️
// Esta función es llamada por el 'listener' de clic
function cancelarCita(idCita) {
    console.log("Cancelando cita:", idCita);

    // Usamos 'fetch' para enviar los datos por POST
    fetch('php/cancelarCita.php', {
        method: 'POST',
        headers: {
            // Decimos al servidor que estamos enviando datos de formulario
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id_cita=${idCita}` // Enviamos el ID en el cuerpo
    })
        .then(response => response.json()) // Esperamos una respuesta JSON
        .then(data => {
            if (data.success) {
                // Si el PHP nos dice que fue un éxito
                alert('¡Cita cancelada correctamente!');
                cargarCitas(); // Volvemos a dibujar la agenda para ver el cambio
            } else {
                // Si el PHP nos da un error
                alert('Error al cancelar la cita: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error en fetch:', error);
            alert('Error de conexión. No se pudo cancelar la cita.');
        });
}
// ⬆️ FIN DE NUEVO (PARTE 4) ⬆️