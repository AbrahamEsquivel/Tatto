// --- FUNCIÓN DE ARRANQUE ---
document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Obtenemos los elementos del DOM que usaremos
    const inputFecha = document.getElementById('fecha_hora_preferida');
    const validatorMessage = document.getElementById('time-validator-message');
    const submitButton = document.getElementById('btn-submit-cita');

    // 2. Ejecutamos la lógica de "fecha mínima" que movimos aquí
    setMinimaFecha(inputFecha);

    // 3. (IMPORTANTE) Deshabilitamos el botón de enviar al inicio
    submitButton.disabled = true;

    // 4. Añadimos el "escuchador" de eventos
    // 'change' se dispara cuando el usuario CIERRA el selector de fecha
    inputFecha.addEventListener('change', (event) => {
        const selectedDate = event.target.value;
        // Llamamos a nuestra función de validación
        debounceCheck(selectedDate, validatorMessage, submitButton);
    });
});

/**
 * (Helper) Establece la fecha y hora mínima en el input
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
 * Esto evita que hagamos 100 llamadas al servidor si el usuario
 * juega con las flechitas. Espera 500ms antes de llamar.
 */
let debounceTimer;
function debounceCheck(selectedDate, validatorMessage, submitButton) {
    // Limpiamos el timer anterior
    clearTimeout(debounceTimer);
    
    // Mostramos "cargando" inmediatamente
    validatorMessage.textContent = 'Verificando disponibilidad...';
    validatorMessage.className = 'validator-message loading';
    submitButton.disabled = true;

    // Creamos un nuevo timer
    debounceTimer = setTimeout(() => {
        // Después de 500ms, llamamos al validador real
        checkAvailability(selectedDate, validatorMessage, submitButton);
    }, 500); // 500ms = medio segundo
}

/**
 * La función REAL que hace el 'fetch' al backend
 */
async function checkAvailability(selectedDate, validatorMessage, submitButton) {
    
    // Preparamos los datos para enviar por POST
    const formData = new FormData();
    formData.append('fecha_hora', selectedDate);

    try {
        const response = await fetch('php/verificarDisponibilidad.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error('Error de conexión con el servidor.');
        }

        const data = await response.json();

        if (data.disponible) {
            // ¡ÉXITO!
            validatorMessage.textContent = '¡Horario disponible!';
            validatorMessage.className = 'validator-message success';
            submitButton.disabled = false; // Habilitamos el botón
        } else {
            // ¡FALLO!
            validatorMessage.textContent = data.message || 'Horario no disponible. Por favor, elige otro.';
            validatorMessage.className = 'validator-message error';
            submitButton.disabled = true; // Mantenemos el botón deshabilitado
        }

    } catch (error) {
        console.error('Error en checkAvailability:', error);
        validatorMessage.textContent = 'Error al verificar. Intenta de nuevo.';
        validatorMessage.className = 'validator-message error';
        submitButton.disabled = true;
    }
}