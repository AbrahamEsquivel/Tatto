// js/admin-crear-cita.js

// 1. DEFINIMOS LAS REGLAS EN EL ÁMBITO GLOBAL (Para que todos las vean)
const reglas = {
    nombre: {
        patron: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,20}$/,
        mensaje: 'Solo letras y espacios (mínimo 20 caracteres).'
    },
    apellido: {
        patron: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,20}$/,
        mensaje: 'Solo letras y espacios (mínimo 20 caracteres).'
    },
    email: {
        patron: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        mensaje: 'Ingresa un correo electrónico válido.'
    },
    telefono: {
        patron: /^$|^\d{10}$/, // Vacío o 10 dígitos
        mensaje: 'Debe ser un número de 10 dígitos (o dejar vacío).'
    }
};

document.addEventListener('DOMContentLoaded', () => {
    
    const form = document.getElementById('form-admin-cita');
    const btnRegistrar = document.getElementById('btn-registrar');

    // Inputs a validar
    const inputNombre = document.getElementById('cliente_nombre');
    const inputApellido = document.getElementById('cliente_apellido');
    const inputEmail = document.getElementById('cliente_email');
    const inputTelefono = document.getElementById('cliente_telefono');

    // --- LISTENERS PARA VALIDACIÓN EN TIEMPO REAL ---
    
    inputNombre.addEventListener('input', () => validarCampo(inputNombre, reglas.nombre));
    inputNombre.addEventListener('blur', () => validarCampo(inputNombre, reglas.nombre));

    inputApellido.addEventListener('input', () => validarCampo(inputApellido, reglas.apellido));
    inputApellido.addEventListener('blur', () => validarCampo(inputApellido, reglas.apellido));

    inputEmail.addEventListener('input', () => validarCampo(inputEmail, reglas.email));
    inputEmail.addEventListener('blur', () => validarCampo(inputEmail, reglas.email));

    inputTelefono.addEventListener('input', () => validarCampo(inputTelefono, reglas.telefono));
    inputTelefono.addEventListener('blur', () => validarCampo(inputTelefono, reglas.telefono));

    // --- LISTENER PARA EL ENVÍO DEL FORMULARIO ---
    form.addEventListener('submit', (e) => {
        // 1. Validamos todo de golpe
        const esNombreValido = validarCampo(inputNombre, reglas.nombre);
        const esApellidoValido = validarCampo(inputApellido, reglas.apellido);
        const esEmailValido = validarCampo(inputEmail, reglas.email);
        const esTelefonoValido = validarCampo(inputTelefono, reglas.telefono);

        // 2. Si algo falló, detenemos el envío y mostramos alerta
        if (!esNombreValido || !esApellidoValido || !esEmailValido || !esTelefonoValido) {
            e.preventDefault(); // ¡Alto! No envíes nada.
            
            Swal.fire({
                icon: 'warning',
                title: 'Datos Incorrectos',
                text: 'Por favor corrige los campos marcados en rojo antes de registrar.',
                background: '#1a1a1a',
                color: '#fff',
                confirmButtonColor: '#3B82F6'
            });
        }
        // 3. Si todo está bien, dejamos que el formulario se envíe normal
    });
});

/**
 * Función genérica para validar un campo visualmente
 */
function validarCampo(input, regla) {
    const valor = input.value.trim();
    const formGroup = input.closest('.form-group');
    const errorDiv = formGroup.querySelector('.input-error-message');
    
    // Caso especial para teléfono (opcional)
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
        errorDiv.textContent = regla.mensaje;
        errorDiv.style.display = 'block';
        return false;
    }
}