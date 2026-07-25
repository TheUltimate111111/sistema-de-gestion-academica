/* ===================================================
   app.js
   Sistema de Gestión Académica
   Lógica general: sidebar, validaciones, buscador,
   alertas y confirmaciones de eliminación.
   =================================================== */

document.addEventListener('DOMContentLoaded', function () {
    initSidebarToggle();
    initPageTransitions();
    initTableSearch();
    initInputFilters();
    initFormValidations();
    initDeleteConfirmations();
});

/* -----------------------------------------------------
   1) SIDEBAR: mostrar/ocultar en PC y móviles
   ----------------------------------------------------- */

function closeSidebar() {
    const sidebar = document.querySelector('.ga-sidebar');
    const backdrop = document.getElementById('gaBackdrop');
    if (sidebar) sidebar.classList.remove('show');
    if (backdrop) backdrop.classList.remove('show');
    updateToggleIcons();
}

function updateToggleIcons() {
    const sidebar = document.querySelector('.ga-sidebar');
    const toggleBtns = document.querySelectorAll('.ga-sidebar-toggle');
    if (!sidebar) return;

    const isClosed = sidebar.classList.contains('collapsed') || !sidebar.classList.contains('show');
    const iconClass = isClosed ? 'bi-list' : 'bi-x-lg';

    toggleBtns.forEach(function (btn) {
        const icon = btn.querySelector('i');
        if (icon) {
            icon.classList.remove('bi-list', 'bi-x-lg');
            icon.classList.add(iconClass);
        }
    });
}

function initSidebarToggle() {
    const toggleBtns = document.querySelectorAll('.ga-sidebar-toggle');
    const sidebar = document.querySelector('.ga-sidebar');
    const backdrop = document.getElementById('gaBackdrop');

    if (!sidebar) return;

    function toggleSidebar() {
        if (window.innerWidth <= 991.98) {
            sidebar.classList.toggle('show');
            if (backdrop) backdrop.classList.toggle('show');
        } else {
            sidebar.classList.toggle('collapsed');
        }
        updateToggleIcons();
    }

    toggleBtns.forEach(function (btn) {
        btn.addEventListener('click', toggleSidebar);
    });

    if (backdrop) {
        backdrop.addEventListener('click', function () {
            closeSidebar();
        });
    }

    // Cerrar sidebar al tocar cualquier enlace de navegación
    sidebar.querySelectorAll('.nav-link').forEach(function (link) {
        link.addEventListener('click', function () {
            closeSidebar();
        });
    });
}

/* -----------------------------------------------------
   1.5) ANIMACIONES DE TRANSICIÓN DE PÁGINA
   ----------------------------------------------------- */
function initPageTransitions() {
    const navLinks = document.querySelectorAll('.ga-sidebar .nav-link');
    const contentArea = document.querySelector('.ga-content') || document.querySelector('.ga-wrapper');

    navLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href && !href.startsWith('#') && !href.startsWith('http') && contentArea) {
                e.preventDefault();

                // Cerrar sidebar en móvil antes de navegar
                closeSidebar();

                // Activar animación de salida
                contentArea.classList.add('ga-content-fadeout');

                setTimeout(function () {
                    window.location.href = href;
                }, 150);
            }
        });
    });
}

/* -----------------------------------------------------
   2) BUSCADOR EN TABLAS
   Cualquier input con [data-table-search="idTabla"]
   filtra las filas <tbody><tr> de la tabla indicada.
   ----------------------------------------------------- */
function initTableSearch() {
    const searchInputs = document.querySelectorAll('[data-table-search]');

    searchInputs.forEach(function (input) {
        const targetId = input.getAttribute('data-table-search');
        const table = document.getElementById(targetId);
        if (!table) return;

        input.addEventListener('keyup', function () {
            const filtro = input.value.trim().toLowerCase();
            const filas = table.tagName === 'TABLE' ? table.querySelectorAll('tbody tr') : table.querySelectorAll('.ga-floating-row');

            filas.forEach(function (fila) {
                const texto = fila.textContent.toLowerCase();
                fila.style.display = texto.includes(filtro) ? '' : 'none';
            });
        });
    });
}

/* -----------------------------------------------------
   3) FILTROS EN TIEMPO REAL AL TECLEAR
   ----------------------------------------------------- */
function initInputFilters() {
    // Solo permitir números en cédula y teléfono
    const numericInputs = document.querySelectorAll('[data-tipo="cedula"], [data-tipo="telefono"]');
    numericInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });

    // Solo permitir letras y espacios en nombres y apellidos
    const textInputs = document.querySelectorAll('[data-tipo="letras"]');
    textInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
        });
    });

    // Restringir el ingreso por teclado para campos numéricos con min/max
    const rangeInputs = document.querySelectorAll('input[type="number"][min][max]');
    rangeInputs.forEach(input => {
        input.addEventListener('input', function() {
            // Evitar 'e', '-', '+' y otros caracteres no numéricos
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value !== '') {
                let val = parseInt(this.value, 10);
                let max = parseInt(this.getAttribute('max'), 10);
                let min = parseInt(this.getAttribute('min'), 10);
                // Si el valor ingresado es mayor al maximo, lo reemplazamos por el máximo
                if (val > max) {
                    this.value = max;
                }
            }
        });
    });
}

/* -----------------------------------------------------
   4) VALIDACIÓN DE FORMULARIOS Y MÓDULO 10
   ----------------------------------------------------- */
function validarCedulaEcuatoriana(cedula) {
    if (cedula.length !== 10) return false;
    const digitoRegion = parseInt(cedula.substring(0, 2), 10);
    if (digitoRegion < 1 || digitoRegion > 24) return false;
    
    const ultimoDigito = parseInt(cedula.substring(9, 10), 10);
    let suma = 0;
    
    for (let i = 0; i < 9; i++) {
        let digito = parseInt(cedula.charAt(i), 10);
        if (i % 2 === 0) {
            digito = digito * 2;
            if (digito > 9) digito -= 9;
        }
        suma += digito;
    }
    
    let digitoVerificador = suma % 10 ? 10 - (suma % 10) : 0;
    return digitoVerificador === ultimoDigito;
}

function initFormValidations() {
    const formularios = document.querySelectorAll('.ga-form');

    formularios.forEach(function (form) {
        form.addEventListener('submit', function (evento) {
            let esValido = true;

            const campos = form.querySelectorAll('input, select, textarea');

            campos.forEach(function (campo) {
                // Saltar campos ocultos
                if (campo.type === 'hidden') return;

                const valor = campo.value.trim();
                let campoValido = true;

                let mensajePersonalizado = null;

                // Campo obligatorio
                if (campo.hasAttribute('required') && valor === '') {
                    campoValido = false;
                    mensajePersonalizado = 'Este campo es obligatorio.';
                }

                // Validación de correo electrónico
                if (campoValido && campo.dataset.tipo === 'email' && valor !== '') {
                    const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!regexEmail.test(valor)) {
                        campoValido = false;
                        mensajePersonalizado = 'Ingresa un correo electrónico válido.';
                    }
                }

                // Validación de letras (Nombres, Apellidos)
                if (campoValido && campo.dataset.tipo === 'letras' && valor !== '') {
                    const regexLetras = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
                    if (!regexLetras.test(valor)) {
                        campoValido = false;
                        mensajePersonalizado = 'Este campo solo debe contener letras y espacios.';
                    }
                }

                // Validación de cédula (Algoritmo Módulo 10 Ecuatoriano)
                if (campoValido && campo.dataset.tipo === 'cedula' && valor !== '') {
                    if (!validarCedulaEcuatoriana(valor)) {
                        campoValido = false;
                        mensajePersonalizado = 'La cédula ingresada no es válida (Verifique los 10 dígitos).';
                    }
                }

                // Validación de teléfono (exactamente 10 números)
                if (campoValido && campo.dataset.tipo === 'telefono' && valor !== '') {
                    const regexTelefono = /^[0-9]{10}$/;
                    if (!regexTelefono.test(valor)) {
                        campoValido = false;
                        mensajePersonalizado = 'El teléfono debe tener 10 dígitos numéricos.';
                    }
                }

                // Validación de Min y Max numérico (p. ej. Créditos)
                if (campoValido && campo.type === 'number' && valor !== '') {
                    const numValue = parseFloat(valor);
                    if (campo.hasAttribute('min') && numValue < parseFloat(campo.getAttribute('min'))) {
                        campoValido = false;
                        mensajePersonalizado = `El valor mínimo permitido es ${campo.getAttribute('min')}.`;
                    }
                    if (campoValido && campo.hasAttribute('max') && numValue > parseFloat(campo.getAttribute('max'))) {
                        campoValido = false;
                        mensajePersonalizado = `El valor máximo permitido es ${campo.getAttribute('max')}.`;
                    }
                }

                marcarCampo(campo, campoValido, mensajePersonalizado);

                if (!campoValido) {
                    esValido = false;
                }
            });

            // Validación especial: fecha_fin >= fecha_inicio (convocatorias)
            const fechaInicio = form.querySelector('[name="fecha_inicio"]');
            const fechaFin = form.querySelector('[name="fecha_fin"]');
            if (fechaInicio && fechaFin && fechaInicio.value && fechaFin.value) {
                if (fechaFin.value < fechaInicio.value) {
                    marcarCampo(fechaFin, false, 'La fecha fin no puede ser anterior a la fecha inicio.');
                    esValido = false;
                }
            }

            if (!esValido) {
                evento.preventDefault();
                mostrarAlerta(form, 'Por favor corrige los errores señalados en el formulario.', 'danger');
            }
        });

        // Limpiar el estado de error al escribir
        form.querySelectorAll('[required], [data-tipo]').forEach(function (campo) {
            campo.addEventListener('input', function () {
                campo.classList.remove('is-invalid');
            });
        });
    });
}

/**
 * Marca visualmente un campo como válido o inválido
 * y actualiza su mensaje de error (invalid-feedback).
 */
function marcarCampo(campo, esValido, mensajePersonalizado) {
    const contenedor = campo.closest('.mb-3') || campo.parentElement;
    let feedback = contenedor ? contenedor.querySelector('.invalid-feedback') : null;

    if (esValido) {
        campo.classList.remove('is-invalid');
    } else {
        campo.classList.add('is-invalid');
        if (feedback && mensajePersonalizado) {
            feedback.textContent = mensajePersonalizado;
        }
    }
}

/* -----------------------------------------------------
   4) CONFIRMACIÓN DE ELIMINACIÓN
   Cualquier botón con la clase "btn-eliminar" mostrará
   un modal de confirmación antes de enviar la acción.
   ----------------------------------------------------- */
function initDeleteConfirmations() {
    const modalElemento = document.getElementById('modalConfirmarEliminar');
    if (!modalElemento) return;

    const modalEliminar = new bootstrap.Modal(modalElemento);
    const nombreRegistro = modalElemento.querySelector('#nombreRegistroEliminar');
    const formEliminar = modalElemento.querySelector('#formEliminar');
    const inputIdEliminar = modalElemento.querySelector('#idRegistroEliminar');

    document.querySelectorAll('.btn-eliminar').forEach(function (boton) {
        boton.addEventListener('click', function () {
            const id = boton.getAttribute('data-id');
            const nombre = boton.getAttribute('data-nombre') || 'este registro';

            if (nombreRegistro) nombreRegistro.textContent = nombre;
            if (inputIdEliminar) inputIdEliminar.value = id;
            if (formEliminar) formEliminar.action = boton.getAttribute('data-url') || '#';

            modalEliminar.show();
        });
    });
}

/* -----------------------------------------------------
   5) ALERTAS DINÁMICAS (éxito / error)
   ----------------------------------------------------- */
function mostrarAlerta(referencia, mensaje, tipo) {
    tipo = tipo || 'success';

    const contenedor = document.getElementById('alertContainer');
    const destino = contenedor || (referencia && referencia.parentElement) || document.body;

    const alerta = document.createElement('div');
    alerta.className = 'alert alert-' + tipo + ' alert-dismissible fade show shadow-sm';
    alerta.setAttribute('role', 'alert');
    alerta.innerHTML =
        '<i class="bi ' + (tipo === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle') + ' me-2"></i>' +
        mensaje +
        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>';

    destino.prepend(alerta);

    // Auto-cerrar luego de 4 segundos
    setTimeout(function () {
        alerta.classList.remove('show');
        alerta.addEventListener('transitionend', function () { alerta.remove(); });
    }, 4000);
}

/* Exponer función global por si se llama desde PHP (ej. tras guardar) */
window.mostrarAlerta = mostrarAlerta;
