<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */
require_once '../db.php';
require_once '../funciones.php';

// Permitir acceso a admin y consejero
if ($_SESSION['user_rol'] == 'admin') {
    requireRole('admin');
} elseif ($_SESSION['user_rol'] == 'consejero') {
    requireRole('consejero');
} else {
    header('Location: ../login.php');
    exit();
}

include '../header.php';
?>

<h1><i class="fas fa-file-alt"></i> Reportes</h1>
<p class="text-secondary">Genera reportes de asistencia</p>
<a href="dashboard.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left"></i> Volver</a>

<div class="row">
    <div class="col-md-4">
        <div class="card-dashboard">
            <h5><i class="fas fa-calendar-day"></i> Reporte Diario</h5>
            <p>Asistencia del día de hoy</p>
            <form method="GET" action="reporte_pdf.php" target="_blank">
                <input type="hidden" name="tipo" value="diario">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-file-pdf"></i> Generar PDF
                </button>
            </form>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-dashboard">
            <h5><i class="fas fa-calendar-week"></i> Reporte Mensual</h5>
            <p>Asistencia del mes actual</p>
            <form method="GET" action="reporte_pdf.php" target="_blank">
                <input type="hidden" name="tipo" value="mensual">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-file-pdf"></i> Generar PDF
                </button>
            </form>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-dashboard">
            <h5><i class="fas fa-graduation-cap"></i> Reporte por Curso</h5>
            <p>Asistencia por curso y sección</p>
            <form method="GET" action="reporte_pdf.php" target="_blank">
                <input type="hidden" name="tipo" value="curso">
                <div class="mb-2">
                    <label class="text-secondary">Curso</label>
                    <select name="curso" class="form-control" required>
                        <option value="">Seleccionar curso...</option>
                        <option value="7mo">7mo</option>
                        <option value="8vo">8vo</option>
                        <option value="9no">9no</option>
                        <option value="10mo">10mo</option>
                        <option value="11vo">11vo</option>
                        <option value="12vo">12vo</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="text-secondary">Sección</label>
                    <select name="seccion" class="form-control">
                        <option value="">Todas las secciones</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-file-pdf"></i> Generar PDF
                </button>
            </form>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>