// Este código es el que ya habíamos probado, que funciona
document.addEventListener('DOMContentLoaded', () => {
    cargarCitas();
});

function cargarCitas() {
    // 1. Llama a tu API de PHP (el que ya hicimos, getCitas.php)
    fetch('php/getCitas.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('La respuesta de la red no fue exitosa');
            }
            return response.json();
        })
        .then(citas => {
            // 3. ¡LISTO! 'citas' es tu Array
            console.log(citas);
            mostrarCitasEnHTML(citas);
        })
        .catch(error => {
            console.error('Error al cargar las citas:', error);
            const container = document.getElementById('agenda-container');
            container.innerHTML = "<p>Error al cargar la agenda. Intenta más tarde.</p>";
        });
}

// ... en tu archivo js/agenda.js ...

function mostrarCitasEnHTML(citas) {
    const container = document.getElementById('agenda-container');
    container.innerHTML = '';

    if (citas.length === 0) {
        container.innerHTML = "<p>No hay citas programadas.</p>";
        return;
    }

    citas.forEach(cita => {
        const citaHTML = `
<div class="cita-card">
<h3>Cita ID: ${cita.id_cita}</h3>
<p><strong>Fecha:</strong> ${cita.fecha_hora}</p>
<p><strong>Cliente:</strong> ${cita.cliente_nombre} ${cita.cliente_apellido}</p>
<p><strong>Artista:</strong> ${cita.artista_nombre}</p>
p><strong>Descripción:</strong> ${cita.tatuaje_descripcion}</p>
<p><strong>Estado:</strong> ${cita.estado_cita}</p>
                
                <a href="edit-cita.php?id=${cita.id_cita}" class="btn-editar">Editar Cita</a>
</div>
`;
        container.innerHTML += citaHTML;
    });
}