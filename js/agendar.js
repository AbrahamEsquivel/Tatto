// --- FUNCIÓN DE ARRANQUE ---
document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Obtenemos los elementos del DOM
    const inputFecha = document.getElementById('fecha_hora_preferida');
    const validatorMessage = document.getElementById('time-validator-message');
    const submitButton = document.getElementById('btn-submit-cita');
    const form = document.getElementById('form-crear-cita-cliente'); // ⬇️ NUEVO ⬇️

    // 2. Ejecutamos la lógica de "fecha mínima"
    setMinimaFecha(inputFecha);

    // 3. Deshabilitamos el botón de enviar al inicio
    submitButton.disabled = true;

    // 4. "Escuchador" para el selector de fecha
    inputFecha.addEventListener('change', (event) => {
        const selectedDate = event.target.value;
        debounceCheck(selectedDate, validatorMessage, submitButton);
    });

    // 5. ⬇️ ¡EL "SECUESTRADOR" DE FORMULARIO! (NUEVO) ⬇️
    // Añadimos un "escuchador" al evento SUBMIT del formulario
    form.addEventListener('submit', (event) => {
        // ¡Prevenimos que la página se recargue!
        event.preventDefault();
        
        // Llamamos a nuestra nueva función que maneja el envío
        handleSubmit(form, submitButton);
    });
});

/**
 * (Helper) Establece la fecha y hora mínima en el input
 * (Esta función no cambia)
 */
function setMinimaFecha(inputFecha) {
    if (!inputFecha) return;
    const ahora = new Date();
    ahora.setMinutes(ahora.getMinutes() - ahora.getTimezoneOffset());
    const fechaMinima = ahora.toISOString().slice(0, 16);
    inputFecha.min = fechaMinima;
}

/**
 * (Helper) Función "debounce"
 * (Esta función no cambia)
 */
let debounceTimer;
function debounceCheck(selectedDate, validatorMessage, submitButton) {
    clearTimeout(debounceTimer);
    
    validatorMessage.textContent = 'Verificando disponibilidad...';
    validatorMessage.className = 'validator-message loading';
    submitButton.disabled = true;

    debounceTimer = setTimeout(() => {
        checkAvailability(selectedDate, validatorMessage, submitButton);
    }, 500); 
}

/**
 * La función REAL que hace el 'fetch' al backend
 * (Esta función no cambia)
 */
async function checkAvailability(selectedDate, validatorMessage, submitButton) {
    const formData = new FormData();
    formData.append('fecha_hora', selectedDate);

    try {
        const response = await fetch('php/verificarDisponibilidad.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) throw new Error('Error de conexión.');

        const data = await response.json();

        if (data.disponible) {
            validatorMessage.textContent = '¡Horario disponible!';
            validatorMessage.className = 'validator-message success';
            submitButton.disabled = false; // Habilitamos el botón
        } else {
            validatorMessage.textContent = data.message || 'Horario no disponible.';
            validatorMessage.className = 'validator-message error';
            submitButton.disabled = true; 
        }

    } catch (error) {
        console.error('Error en checkAvailability:', error);
        validatorMessage.textContent = 'Error al verificar. Intenta de nuevo.';
        validatorMessage.className = 'validator-message error';
        submitButton.disabled = true;
    }
}


// ⬇️ --- ¡TODA ESTA FUNCIÓN ES NUEVA! --- ⬇️
/**
 * Maneja el envío del formulario con AJAX y SweetAlert
 */
async function handleSubmit(form, submitButton) {

    // 1. Mostramos un pop-up de "Cargando"
    Swal.fire({
        title: 'Enviando tu solicitud...',
        text: 'Por favor espera.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // 2. Obtenemos TODOS los datos del formulario
    const formData = new FormData(form);

    try {
        // 3. Enviamos los datos a crearCita.php
        const response = await fetch('php/crearCita.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        // 4. Analizamos la respuesta del PHP
        if (data.success) {
            // ¡ÉXITO!
            Swal.fire({
                icon: 'success',
                title: '¡Cita Registrada!',
                text: 'Nos pondremos en contacto contigo pronto para confirmar.',
                confirmButtonColor: '#3B82F6' // Azul (color de tu admin)
            });
            
            // 5. Limpiamos el formulario y deshabilitamos el botón
            form.reset();
            submitButton.disabled = true;
            document.getElementById('time-validator-message').textContent = '';

        } else {
            // ¡ERROR!
            throw new Error(data.message || 'Error desconocido del servidor.');
        }

    } catch (error) {
        console.error('Error en handleSubmit:', error);
        // Mostramos un pop-up de error
        Swal.fire({
            icon: 'error',
            title: 'Oops... algo salió mal',
            text: `Error: ${error.message}`,
            confirmButtonColor: '#EF4444' // Rojo
        });
    }
}