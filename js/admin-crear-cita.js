// js/admin-crear-cita.js

// 1. DEFINIMOS LAS REGLAS EN EL ÁMBITO GLOBAL
const reglas = {
    nombre: {
        // Acepta letras, espacios y acentos. Min 2, Max 50 caracteres.
        patron: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,50}$/,
        mensaje: 'Solo letras y espacios (mínimo 2 caracteres).'
    },
    apellido: {
        patron: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,50}$/,
        mensaje: 'Solo letras y espacios (mínimo 2 caracteres).'
    },
    email: {
        // Validación estándar de correo
        patron: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        mensaje: 'Ingresa un correo electrónico válido.'
    },
    telefono: {
        // Permite campo vacío O exactamente 10 dígitos
        patron: /^$|^\d{10}$/, 
        mensaje: 'Debe ser un número de 10 dígitos (o dejar vacío).'
    }
};

document.addEventListener('DOMContentLoaded', () => {
    
    const form = document.getElementById('form-admin-cita');
    
    // Inputs a validar
    const inputNombre = document.getElementById('cliente_nombre');
    const inputApellido = document.getElementById('cliente_apellido');
    const inputEmail = document.getElementById('cliente_email');
    const inputTelefono = document.getElementById('cliente_telefono');
    const inputFecha = document.getElementById('fecha_hora');

    // --- BLOQUEAR FECHAS PASADAS ---
    if (inputFecha) {
        const ahora = new Date();
        // Ajustamos la zona horaria local
        ahora.setMinutes(ahora.getMinutes() - ahora.getTimezoneOffset());
        // Formato para input datetime-local: YYYY-MM-DDTHH:MM
        const fechaMinima = ahora.toISOString().slice(0, 16);
        inputFecha.min = fechaMinima;
    }
    
    // --- LISTENERS PARA VALIDACIÓN EN TIEMPO REAL ---
    // Se activan cuando escribes (input) y cuando sales del campo (blur)
    
    if(inputNombre) {
        inputNombre.addEventListener('input', () => validarCampo(inputNombre, reglas.nombre));
        inputNombre.addEventListener('blur', () => validarCampo(inputNombre, reglas.nombre));
    }

    if(inputApellido) {
        inputApellido.addEventListener('input', () => validarCampo(inputApellido, reglas.apellido));
        inputApellido.addEventListener('blur', () => validarCampo(inputApellido, reglas.apellido));
    }

    if(inputEmail) {
        inputEmail.addEventListener('input', () => validarCampo(inputEmail, reglas.email));
        inputEmail.addEventListener('blur', () => validarCampo(inputEmail, reglas.email));
    }

    if(inputTelefono) {
        inputTelefono.addEventListener('input', () => validarCampo(inputTelefono, reglas.telefono));
        inputTelefono.addEventListener('blur', () => validarCampo(inputTelefono, reglas.telefono));
    }

    // --- LISTENER PARA EL ENVÍO DEL FORMULARIO ---
    if(form) {
        form.addEventListener('submit', (e) => {
            // 1. Validamos todo de golpe antes de enviar
            const esNombreValido = validarCampo(inputNombre, reglas.nombre);
            const esApellidoValido = validarCampo(inputApellido, reglas.apellido);
            const esEmailValido = validarCampo(inputEmail, reglas.email);
            const esTelefonoValido = validarCampo(inputTelefono, reglas.telefono);

            // 2. Si algo falló, detenemos el envío y mostramos alerta
            if (!esNombreValido || !esApellidoValido || !esEmailValido || !esTelefonoValido) {
                e.preventDefault(); // ¡Alto! No envíes nada al servidor.
                
                Swal.fire({
                    icon: 'warning',
                    title: 'Datos Incorrectos',
                    text: 'Por favor corrige los campos marcados en rojo antes de registrar.',
                    background: '#1a1a1a',
                    color: '#fff',
                    confirmButtonColor: '#3B82F6'
                });
            }
            // 3. Si todo está bien, el formulario se envía normal (PHP hará la redirección)
        });
    }
});

/**
 * Función genérica para validar un campo visualmente
 */
function validarCampo(input, regla) {
    if (!input) return true; // Si el input no existe, pasamos
    
    const valor = input.value.trim();
    const formGroup = input.closest('.form-group');
    const errorDiv = formGroup.querySelector('.input-error-message');
    
    // Caso especial para teléfono (opcional: si está vacío es válido)
    if (regla === reglas.telefono && valor === '') {
        formGroup.classList.remove('error');
        if(errorDiv) errorDiv.style.display = 'none';
        return true;
    }

    if (regla.patron.test(valor)) {
        // VÁLIDO
        formGroup.classList.remove('error');
        if(errorDiv) errorDiv.style.display = 'none';
        return true;
    } else {
        // INVÁLIDO
        formGroup.classList.add('error');
        if(errorDiv) {
            errorDiv.textContent = regla.mensaje;
            errorDiv.style.display = 'block';
        }
        return false;
    }
}