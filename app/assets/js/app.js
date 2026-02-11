/**
 * JavaScript principal de la aplicación
 * Funciones comunes y utilidades
 */

// Mostrar/ocultar spinner de carga
function showLoading() {
    let spinner = document.querySelector('.spinner-overlay');
    if (!spinner) {
        spinner = document.createElement('div');
        spinner.className = 'spinner-overlay';
        spinner.innerHTML = '<div class="spinner-border text-light" role="status"><span class="visually-hidden">Cargando...</span></div>';
        document.body.appendChild(spinner);
    }
    spinner.classList.add('show');
}

function hideLoading() {
    const spinner = document.querySelector('.spinner-overlay');
    if (spinner) {
        spinner.classList.remove('show');
    }
}

// Mostrar alerta
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.setAttribute('role', 'alert');
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    const container = document.querySelector('.container-fluid');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);

        // Auto-cerrar después de 5 segundos
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
}

// Confirmar acción
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Formatear fecha
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString('es-MX', options);
}

// Formatear número con decimales
function formatNumber(number, decimals = 2) {
    return parseFloat(number).toFixed(decimals);
}

// Validar email
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Validar teléfono (10 dígitos)
function isValidPhone(phone) {
    const re = /^\d{10}$/;
    return re.test(phone.replace(/\D/g, ''));
}

// Obtener badge de semáforo
function getSemaforoBadge(interpretacion) {
    const badges = {
        'Normal': '<span class="badge badge-semaforo badge-normal">🟢 Normal</span>',
        'Precaución': '<span class="badge badge-semaforo badge-precaucion">🟡 Precaución</span>',
        'Alerta': '<span class="badge badge-semaforo badge-alerta">🔴 Alerta</span>'
    };
    return badges[interpretacion] || '<span class="badge bg-secondary">Sin datos</span>';
}

// Inicialización al cargar la página
document.addEventListener('DOMContentLoaded', function () {
    // Agregar clase fade-in a elementos
    document.querySelectorAll('.card').forEach(card => {
        card.classList.add('fade-in');
    });

    // Tooltips de Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Confirmar eliminaciones
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function (e) {
            if (!confirm('¿Está seguro de que desea eliminar este registro?')) {
                e.preventDefault();
            }
        });
    });
});

// Búsqueda AJAX genérica
function searchAjax(url, searchTerm, resultsContainer) {
    showLoading();

    $.ajax({
        url: url,
        method: 'GET',
        data: { search: searchTerm },
        dataType: 'json',
        success: function (response) {
            hideLoading();
            if (response.success) {
                $(resultsContainer).html(response.html);
            } else {
                showAlert(response.message || 'Error en la búsqueda', 'danger');
            }
        },
        error: function () {
            hideLoading();
            showAlert('Error de conexión. Por favor, intente nuevamente.', 'danger');
        }
    });
}

// Debounce para búsquedas
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
