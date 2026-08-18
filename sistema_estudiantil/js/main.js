// js/main.js
console.log('DHS Access Control - Sistema Estudiantil');

// Función para confirmar eliminación
function confirmarEliminacion(mensaje) {
    return confirm(mensaje || '¿Estás seguro de eliminar este registro?');
}

// Función para imprimir QR
function imprimirQR() {
    window.print();
}

// Función para copiar al portapapeles
function copiarTexto(texto) {
    navigator.clipboard.writeText(texto).then(() => {
        alert('📋 Texto copiado al portapapeles');
    });
}

// Inicializar tooltips de Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});