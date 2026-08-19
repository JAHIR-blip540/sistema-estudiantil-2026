// js/qr-scanner.js - Escáner QR con cámara
// Usa la librería html5-qrcode

let html5QrCode;
let escaneando = true;

/**
 * Inicia el escáner QR
 */
function iniciarEscanner(elementId, onSuccess, onError) {
    if (html5QrCode) {
        html5QrCode.clear();
        html5QrCode = null;
    }
    
    html5QrCode = new Html5Qrcode(elementId);
    
    const config = {
        fps: 10,
        qrbox: { width: 250, height: 250 },
        aspectRatio: 1.0
    };
    
    html5QrCode.start(
        { facingMode: "environment" },
        config,
        function(decodedText, decodedResult) {
            if (!escaneando) return;
            escaneando = false;
            
            // Mostrar el código escaneado
            document.getElementById('codigo_escaneado').textContent = decodedText;
            document.getElementById('info_escaneo').style.display = 'block';
            
            if (onSuccess) {
                onSuccess(decodedText, decodedResult);
            }
        },
        function(error) {
            // No mostrar errores para no molestar al usuario
            if (onError) {
                onError(error);
            }
        }
    );
}

/**
 * Detiene el escáner QR
 */
function detenerEscanner() {
    if (html5QrCode) {
        html5QrCode.stop();
        html5QrCode.clear();
        html5QrCode = null;
    }
}

/**
 * Reinicia el escáner QR
 */
function reiniciarEscanner(elementId, onSuccess, onError) {
    escaneando = true;
    document.getElementById('resultado').style.display = 'none';
    document.getElementById('info_escaneo').style.display = 'none';
    document.getElementById('datos_estudiante').innerHTML = '';
    document.getElementById('botones_accion').innerHTML = '';
    document.getElementById('codigo_escaneado').textContent = '';
    
    if (html5QrCode) {
        html5QrCode.clear();
        html5QrCode = null;
    }
    
    setTimeout(function() {
        iniciarEscanner(elementId, onSuccess, onError);
    }, 500);
}

/**
 * Verifica si el navegador soporta cámara
 */
function soportaCamara() {
    return !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
}

/**
 * Muestra mensaje de error en el escáner
 */
function mostrarErrorEscanner(mensaje) {
    const resultado = document.getElementById('resultado');
    const datos = document.getElementById('datos_estudiante');
    const botones = document.getElementById('botones_accion');
    
    resultado.style.display = 'block';
    
    datos.innerHTML = `
        <div class="card-dashboard" style="background: rgba(255,23,68,0.1); border: 2px solid #FF1744;">
            <h3 style="color: #FF1744;">❌ ${mensaje}</h3>
            <p class="text-secondary">Verifica que el QR sea válido</p>
        </div>
    `;
    
    botones.innerHTML = `
        <button class="btn btn-primary" onclick="location.reload()">
            <i class="fas fa-redo"></i> Intentar de Nuevo
        </button>
    `;
    
    if (html5QrCode) {
        html5QrCode.stop();
    }
}

/**
 * Muestra los datos del estudiante escaneado
 */
function mostrarEstudianteEscaneado(estudiante) {
    const resultado = document.getElementById('resultado');
    const datos = document.getElementById('datos_estudiante');
    const botones = document.getElementById('botones_accion');
    
    resultado.style.display = 'block';
    
    datos.innerHTML = `
        <div class="card-dashboard" style="background: rgba(0,230,118,0.1); border: 2px solid #00E676;">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div style="background: white; padding: 10px; border-radius: 12px; display: inline-block;">
                        <img src="${estudiante.qr_url || 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + estudiante.codigo_qr}" alt="QR" style="width:100px;">
                    </div>
                </div>
                <div class="col-md-8 text-start">
                    <h3 style="color: #00E676;">✅ Acceso Autorizado</h3>
                    <h4>${estudiante.nombre} ${estudiante.apellido}</h4>
                    <p class="text-secondary">Código: ${estudiante.codigo_estudiante}</p>
                    <p class="text-secondary">${estudiante.curso} - ${estudiante.seccion}</p>
                    <p class="text-secondary">Jornada: ${estudiante.jornada}</p>
                    <p class="text-secondary">Carnet: ${estudiante.carnet_number}</p>
                </div>
            </div>
        </div>
    `;
    
    botones.innerHTML = `
        <button class="btn btn-success" onclick="registrarAsistencia(${estudiante.id}, 'entrada')">
            <i class="fas fa-sign-in-alt"></i> Confirmar Entrada
        </button>
        <button class="btn btn-info" onclick="registrarAsistencia(${estudiante.id}, 'salida')">
            <i class="fas fa-sign-out-alt"></i> Confirmar Salida
        </button>
        <button class="btn btn-secondary" onclick="location.reload()">
            <i class="fas fa-redo"></i> Escanear Otro
        </button>
    `;
    
    if (html5QrCode) {
        html5QrCode.stop();
    }
}

/**
 * Registra asistencia desde el escáner
 */
function registrarAsistencia(estudianteId, tipo) {
    fetch('registrar_asistencia.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            estudiante_id: estudianteId,
            tipo: tipo
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const datos = document.getElementById('datos_estudiante');
            datos.innerHTML = `
                <div class="card-dashboard" style="background: rgba(0,230,118,0.2); border: 2px solid #00E676;">
                    <h3 style="color: #00E676;">✅ ${data.message}</h3>
                    <p class="text-secondary">Hora: ${data.hora}</p>
                </div>
            `;
            document.getElementById('botones_accion').innerHTML = `
                <button class="btn btn-primary" onclick="location.reload()">
                    <i class="fas fa-redo"></i> Escanear Otro
                </button>
            `;
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al registrar asistencia');
    });
}

// Exportar funciones para uso global
window.iniciarEscanner = iniciarEscanner;
window.detenerEscanner = detenerEscanner;
window.reiniciarEscanner = reiniciarEscanner;
window.soportaCamara = soportaCamara;
window.mostrarErrorEscanner = mostrarErrorEscanner;
window.mostrarEstudianteEscaneado = mostrarEstudianteEscaneado;
window.registrarAsistencia = registrarAsistencia;