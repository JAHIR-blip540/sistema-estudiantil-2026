<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */
require_once '../db.php';
requireRole('guardia');
include '../header.php';
?>

<h1><i class="fas fa-camera"></i> Escáner QR con Cámara</h1>
<a href="dashboard.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left"></i> Volver</a>
<p class="text-secondary">Apunta la cámara al código QR del estudiante</p>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card-dashboard">
            <div id="reader" style="width:100%; max-width:500px; margin:0 auto;"></div>
            
            <div id="info_escaneo" class="mt-2 text-center" style="display:none;">
                <p class="text-secondary">📌 Código escaneado: <span id="codigo_escaneado"></span></p>
            </div>
            
            <div id="resultado" class="mt-3 text-center" style="display:none;">
                <div id="datos_estudiante"></div>
                <div id="botones_accion" class="mt-3 scanner-actions"></div>
            </div>
            
            <div class="text-center mt-3">
                <button class="btn btn-danger" onclick="reiniciarEscanner()">
                    <i class="fas fa-redo"></i> Reiniciar Escáner
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let html5QrCode;
let escaneando = true;

function iniciarEscanner() {
    if (html5QrCode) {
        html5QrCode.clear();
        html5QrCode = null;
    }
    
    html5QrCode = new Html5Qrcode("reader");
    
    const config = {
        fps: 10,
        qrbox: { width: 250, height: 250 },
        aspectRatio: 1.0
    };
    
    html5QrCode.start(
        { facingMode: "environment" },
        config,
        onScanSuccess,
        onScanError
    );
}

function onScanSuccess(decodedText, decodedResult) {
    if (!escaneando) return;
    escaneando = false;
    
    document.getElementById('info_escaneo').style.display = 'block';
    document.getElementById('codigo_escaneado').textContent = decodedText;
    
    // Limpiar el código
    let codigoLimpio = decodedText.trim().replace(/\s+/g, '').replace(/\n/g, '').replace(/\r/g, '');
    
    // Enviar al servidor
    fetch('procesar_qr.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ codigo_qr: codigoLimpio })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarEstudiante(data.estudiante);
        } else {
            mostrarError(data.message);
        }
    })
    .catch(error => {
        mostrarError('Error al procesar el QR: ' + error.message);
    });
}

function onScanError(error) {
    // No mostrar errores
}

function mostrarEstudiante(estudiante) {
    const resultado = document.getElementById('resultado');
    const datos = document.getElementById('datos_estudiante');
    const botones = document.getElementById('botones_accion');
    
    resultado.style.display = 'block';
    
    datos.innerHTML = `
        <div class="card-dashboard" style="background: rgba(0,230,118,0.1); border: 2px solid #00E676;">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div style="background: white; padding: 10px; border-radius: 12px; display: inline-block;">
                        <img src="${estudiante.qr_url}" alt="QR" style="width:100px;">
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
        <button class="btn btn-secondary" onclick="reiniciarEscanner()">
            <i class="fas fa-redo"></i> Escanear Otro
        </button>
    `;
    
    if (html5QrCode) {
        html5QrCode.stop();
    }
}

function mostrarError(mensaje) {
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
        <button class="btn btn-primary" onclick="reiniciarEscanner()">
            <i class="fas fa-redo"></i> Intentar de Nuevo
        </button>
    `;
    
    if (html5QrCode) {
        html5QrCode.stop();
    }
}

function registrarAsistencia(estudianteId, tipo) {
    fetch('registrar_asistencia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
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
                <button class="btn btn-primary" onclick="reiniciarEscanner()">
                    <i class="fas fa-redo"></i> Escanear Otro
                </button>
            `;
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error al registrar asistencia: ' + error.message);
    });
}

function reiniciarEscanner() {
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
    
    setTimeout(() => {
        iniciarEscanner();
    }, 500);
}

window.onload = function() {
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        iniciarEscanner();
    } else {
        document.getElementById('reader').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> 
                Tu navegador no soporta cámara. Usa Chrome, Edge o Firefox.
            </div>
        `;
    }
};
</script>

<?php include '../footer.php'; ?>