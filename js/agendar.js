// js/agendar.js

document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Elementos del DOM
    const inputFecha = document.getElementById('fecha_hora_preferida');
    const validatorMessage = document.getElementById('time-validator-message');
    const submitButton = document.getElementById('btn-submit-cita');
    const form = document.getElementById('form-crear-cita-cliente');

    // Inputs para validación en tiempo real
    const inputNombre = document.getElementById('cliente_nombre');
    const inputApellido = document.getElementById('cliente_apellido');
    const inputEmail = document.getElementById('cliente_email');
    const inputTelefono = document.getElementById('cliente_telefono');

    // 2. Configuración inicial
    setMinimaFecha(inputFecha);
    submitButton.disabled = true; // Deshabilitado al inicio

    // 3. Listeners para validación en tiempo real
    // Validamos cada vez que el usuario escribe o sale del campo (blur)
    inputNombre.addEventListener('input', () => validarCampo(inputNombre, reglas.nombre));
    inputNombre.addEventListener('blur', () => validarCampo(inputNombre, reglas.nombre));

    inputApellido.addEventListener('input', () => validarCampo(inputApellido, reglas.apellido));
    inputApellido.addEventListener('blur', () => validarCampo(inputApellido, reglas.apellido));

    inputEmail.addEventListener('input', () => validarCampo(inputEmail, reglas.email));
    inputEmail.addEventListener('blur', () => validarCampo(inputEmail, reglas.email));

    inputTelefono.addEventListener('input', () => validarCampo(inputTelefono, reglas.telefono));
    inputTelefono.addEventListener('blur', () => validarCampo(inputTelefono, reglas.telefono));


    // 4. Listener para Fecha (Tu lógica original + validación extra)
    inputFecha.addEventListener('change', (event) => {
        const selectedDate = event.target.value;
        // Validación básica: que no esté vacío
        if (!selectedDate) {
            validatorMessage.textContent = '';
            submitButton.disabled = true;
            return;
        }
        debounceCheck(selectedDate, validatorMessage, submitButton);
    });

    // 5. Listener para Envío del Formulario
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        
        // Validación final de TODOS los campos antes de enviar
        const esNombreValido = validarCampo(inputNombre, reglas.nombre);
        const esApellidoValido = validarCampo(inputApellido, reglas.apellido);
        const esEmailValido = validarCampo(inputEmail, reglas.email);
        const esTelefonoValido = validarCampo(inputTelefono, reglas.telefono); // Opcional

        if (esNombreValido && esApellidoValido && esEmailValido && esTelefonoValido) {
            // Si todo es válido, procedemos
            handleSubmit(form, submitButton);
        } else {
            // Si hay errores, mostramos una alerta suave
            Swal.fire({
                icon: 'warning',
                title: 'Datos incorrectos',
                text: 'Por favor, corrige los errores marcados en rojo antes de enviar.',
                confirmButtonColor: '#007bff'
            });
        }
    });
});

// --- OBJETO DE REGLAS DE VALIDACIÓN ---
const reglas = {
    nombre: {
        patron: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,20}$/, // Solo letras y espacios, 2-50 caracteres
        mensaje: 'Solo letras (min 2, max 20).'
    },
    apellido: {
        patron: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,20}$/,
        mensaje: 'Solo letras (min 2, max 20).'
    },
    email: {
        patron: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, // Formato email estándar
        mensaje: 'Formato de correo inválido.'
    },
    telefono: {
        // Opcional: permite vacío O 10 dígitos numéricos
        patron: /^$|^\d{10}$/, 
        mensaje: 'Debe ser un número de 10 dígitos (o dejar vacío).'
    }
};

/**
 * Valida un input según una regla y muestra/oculta el error
 */
function validarCampo(input, regla) {
    const valor = input.value.trim();
    const formGroup = input.closest('.form-group');
    const errorDiv = formGroup.querySelector('.input-error-message');
    
    // Si es opcional y está vacío, es válido (caso teléfono)
    if (regla === reglas.telefono && valor === '') {
        formGroup.classList.remove('error');
        errorDiv.style.display = 'none';
        return true;
    }

    if (regla.patron.test(valor)) {
        // VÁLIDO
        formGroup.classList.remove('error');
        errorDiv.style.display = 'none';
        return true;
    } else {
        // INVÁLIDO
        formGroup.classList.add('error');
        errorDiv.textContent = regla.mensaje; // Actualizamos mensaje si es necesario
        errorDiv.style.display = 'block';
        return false;
    }
}

/**
 * (Helper) Establece la fecha mínima
 */
function setMinimaFecha(inputFecha) {
    if (!inputFecha) return;
    const ahora = new Date();
    // Restamos minutos de zona horaria para ajustar a local
    ahora.setMinutes(ahora.getMinutes() - ahora.getTimezoneOffset());
    const fechaMinima = ahora.toISOString().slice(0, 16);
    inputFecha.min = fechaMinima;
    
    // Opcional: Establecer fecha máxima (ej. 2 años en el futuro)
    const futuro = new Date();
    futuro.setFullYear(futuro.getFullYear() + 2);
    const fechaMaxima = futuro.toISOString().slice(0, 16);
    inputFecha.max = fechaMaxima;
}

// ... (Resto de tus funciones: debounceCheck, checkAvailability, handleSubmit se mantienen igual) ...

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
            
            // Solo habilitamos si los demás campos también parecen válidos (opcional, o dejamos que el submit valide final)
            submitButton.disabled = false; 
        } else {
            validatorMessage.textContent = data.message || 'Horario no disponible.';
            validatorMessage.className = 'validator-message error';
            submitButton.disabled = true; 
        }
    } catch (error) {
        console.error('Error:', error);
        validatorMessage.textContent = 'Error al verificar.';
        validatorMessage.className = 'validator-message error';
        submitButton.disabled = true;
    }
}

async function handleSubmit(form, submitButton) {
    Swal.fire({
        title: 'Enviando...',
        text: 'Por favor espera.',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    const formData = new FormData(form);

    try {
        const response = await fetch('php/crearCita.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Cita Registrada!',
                text: 'Nos pondremos en contacto contigo pronto.',
                confirmButtonColor: '#3B82F6'
            });
            form.reset();
            submitButton.disabled = true;
            document.getElementById('time-validator-message').textContent = '';
            // Limpiar clases de error si quedaron
            document.querySelectorAll('.form-group.error').forEach(el => el.classList.remove('error'));
            document.querySelectorAll('.input-error-message').forEach(el => el.style.display = 'none');
            
        } else {
            throw new Error(data.message || 'Error desconocido.');
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message,
            confirmButtonColor: '#EF4444'
        });
    }
}