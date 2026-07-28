document.addEventListener('DOMContentLoaded', function () {
    initSidebarToggle();
    initPageTransitions();
    initTableSearch();
    initAdvancedFilters();
    initInputFilters();
    initFormValidations();
    initDeleteConfirmations();
});

function initSidebarToggle() {
    const toggleBtn = document.querySelector('.ga-sidebar-toggle');
    const sidebar = document.querySelector('.ga-sidebar');
    const backdrop = document.querySelector('.ga-sidebar-backdrop');

    if (!toggleBtn || !sidebar) return;

    toggleBtn.addEventListener('click', function () {
        if (window.innerWidth <= 991.98) {
            sidebar.classList.toggle('show');
            if (backdrop) backdrop.classList.toggle('show');
        } else {
            sidebar.classList.toggle('collapsed');
        }
    });

    if (backdrop) {
        backdrop.addEventListener('click', function () {
            sidebar.classList.remove('show');
            backdrop.classList.remove('show');
        });
    }
}

function initPageTransitions() {
    const navLinks = document.querySelectorAll('.ga-sidebar .nav-link');
    const contentArea = document.querySelector('.ga-content') || document.querySelector('.ga-wrapper');

    navLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href && !href.startsWith('#') && !href.startsWith('http') && contentArea) {
                e.preventDefault();
                contentArea.classList.add('ga-content-fadeout');
                setTimeout(() => {
                    window.location.href = href;
                }, 300);
            }
        });
    });
}

function initTableSearch() {
    const searchInputs = document.querySelectorAll('[data-table-search]');

    searchInputs.forEach(function (input) {
        if (input.closest('.ga-filter-container')) return;

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

function initAdvancedFilters() {
    const containers = document.querySelectorAll('.ga-filter-container');
    if (!containers.length) return;

    containers.forEach(function (container) {
        const tableId = container.getAttribute('data-filter-table');
        const table = document.getElementById(tableId);
        if (!table) return;

        const filters = container.querySelectorAll('.ga-filter-input, .ga-filter-select, .ga-filter-date, .ga-filter-pill, .ga-filter-num-min, .ga-filter-num-max');

        function applyFilters() {
            const rows = table.querySelectorAll('.ga-floating-row');
            const activePills = container.querySelectorAll('.ga-filter-pill.active');

            rows.forEach(function (row) {
                let show = true;

                const searchInput = container.querySelector('[data-table-search]');
                if (show && searchInput && searchInput.value.trim()) {
                    const filtro = searchInput.value.trim().toLowerCase();
                    if (!row.textContent.toLowerCase().includes(filtro)) show = false;
                }

                if (show) {
                    const prefixInputs = container.querySelectorAll('.ga-filter-input[data-filter-type="prefix"]');
                    prefixInputs.forEach(function (inp) {
                        if (!show) return;
                        const val = inp.value.trim();
                        if (!val) return;
                        const key = inp.getAttribute('data-filter-target');
                        const rowVal = (row.dataset[key] || '').toLowerCase();
                        if (!rowVal.startsWith(val.toLowerCase())) show = false;
                    });
                }

                if (show) {
                    const selects = container.querySelectorAll('.ga-filter-select');
                    selects.forEach(function (sel) {
                        if (!show) return;
                        const val = sel.value;
                        if (!val) return;
                        const key = sel.getAttribute('data-filter-target');
                        const rowVal = row.dataset[key] || '';
                        if (rowVal !== val) show = false;
                    });
                }

                if (show && activePills.length) {
                    let pillMatch = false;
                    activePills.forEach(function (pill) {
                        const key = pill.getAttribute('data-filter-target');
                        const pillVal = pill.getAttribute('data-filter-value');
                        const rowVal = (row.dataset[key] || '').toLowerCase();
                        if (rowVal.startsWith(pillVal.toLowerCase())) pillMatch = true;
                    });
                    if (!pillMatch) show = false;
                }

                if (show) {
                    const dateFrom = container.querySelector('.ga-filter-date-from');
                    const dateTo = container.querySelector('.ga-filter-date-to');
                    if (dateFrom && dateFrom.value) {
                        const key = dateFrom.getAttribute('data-filter-target');
                        const rowDate = row.dataset[key];
                        if (rowDate && rowDate < dateFrom.value) show = false;
                    }
                    if (dateTo && dateTo.value) {
                        const key = dateTo.getAttribute('data-filter-target');
                        const rowDate = row.dataset[key];
                        if (rowDate && rowDate > dateTo.value) show = false;
                    }
                }

                if (show) {
                    const numMin = container.querySelector('.ga-filter-num-min');
                    const numMax = container.querySelector('.ga-filter-num-max');
                    if (numMin && numMin.value) {
                        const key = numMin.getAttribute('data-filter-target');
                        const rowVal = parseInt(row.dataset[key], 10);
                        if (!isNaN(rowVal) && rowVal < parseInt(numMin.value, 10)) show = false;
                    }
                    if (numMax && numMax.value) {
                        const key = numMax.getAttribute('data-filter-target');
                        const rowVal = parseInt(row.dataset[key], 10);
                        if (!isNaN(rowVal) && rowVal > parseInt(numMax.value, 10)) show = false;
                    }
                }

                row.style.display = show ? '' : 'none';
            });
        }

        filters.forEach(function (input) {
            var eventType;
            if (input.tagName === 'SELECT' || input.type === 'date') {
                eventType = 'change';
            } else if (input.classList.contains('ga-filter-pill')) {
                eventType = 'click';
            } else {
                eventType = 'input';
            }

            input.addEventListener(eventType, function () {
                if (input.classList.contains('ga-filter-pill')) {
                    input.classList.toggle('active');
                }
                applyFilters();
            });
        });

        const resetBtn = container.querySelector('.ga-filter-reset');
        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                container.querySelectorAll('.ga-filter-input, .ga-filter-select, .ga-filter-date, .ga-filter-num-min, .ga-filter-num-max').forEach(function (el) {
                    el.value = '';
                    el.removeAttribute('min');
                    el.removeAttribute('max');
                });
                container.querySelectorAll('.ga-filter-pill.active').forEach(function (el) {
                    el.classList.remove('active');
                });
                applyFilters();
            });
        }

        // Link date-from and date-to pairs so "Hasta" >= "Desde"
        const dateFrom = container.querySelector('.ga-filter-date-from');
        const dateTo = container.querySelector('.ga-filter-date-to');
        if (dateFrom && dateTo) {
            dateFrom.addEventListener('change', function () {
                dateTo.min = this.value;
                if (dateTo.value && dateTo.value < this.value) {
                    dateTo.value = this.value;
                }
                applyFilters();
            });
            dateTo.addEventListener('change', function () {
                dateFrom.max = this.value;
                applyFilters();
            });
        }
    });
}

function initInputFilters() {
    const numericInputs = document.querySelectorAll('[data-tipo="cedula"], [data-tipo="telefono"]');
    numericInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });

    const textInputs = document.querySelectorAll('[data-tipo="letras"]');
    textInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
        });
    });

    const rangeInputs = document.querySelectorAll('input[type="number"][min][max]');
    rangeInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value !== '') {
                let val = parseInt(this.value, 10);
                let max = parseInt(this.getAttribute('max'), 10);
                let min = parseInt(this.getAttribute('min'), 10);
                if (val > max) {
                    this.value = max;
                }
            }
        });
    });
}

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
                if (campo.type === 'hidden') return;

                const valor = campo.value.trim();
                let campoValido = true;

                let mensajePersonalizado = null;

                if (campo.hasAttribute('required') && valor === '') {
                    campoValido = false;
                    mensajePersonalizado = 'Este campo es obligatorio.';
                }

                if (campoValido && campo.dataset.tipo === 'email' && valor !== '') {
                    const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!regexEmail.test(valor)) {
                        campoValido = false;
                        mensajePersonalizado = 'Ingresa un correo electrónico válido.';
                    }
                }

                if (campoValido && campo.dataset.tipo === 'letras' && valor !== '') {
                    const regexLetras = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
                    if (!regexLetras.test(valor)) {
                        campoValido = false;
                        mensajePersonalizado = 'Este campo solo debe contener letras y espacios.';
                    }
                }

                if (campoValido && campo.dataset.tipo === 'cedula' && valor !== '') {
                    if (!validarCedulaEcuatoriana(valor)) {
                        campoValido = false;
                        mensajePersonalizado = 'La cédula ingresada no es válida (Verifique los 10 dígitos).';
                    }
                }

                if (campoValido && campo.dataset.tipo === 'telefono' && valor !== '') {
                    const regexTelefono = /^[0-9]{10}$/;
                    if (!regexTelefono.test(valor)) {
                        campoValido = false;
                        mensajePersonalizado = 'El teléfono debe tener 10 dígitos numéricos.';
                    }
                }

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

        form.querySelectorAll('[required], [data-tipo]').forEach(function (campo) {
            campo.addEventListener('input', function () {
                campo.classList.remove('is-invalid');
            });
        });
    });
}

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

    setTimeout(function () {
        alerta.classList.remove('show');
        alerta.addEventListener('transitionend', function () { alerta.remove(); });
    }, 4000);
}

window.mostrarAlerta = mostrarAlerta;
