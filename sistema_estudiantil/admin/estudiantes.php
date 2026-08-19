<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */
require_once '../db.php';
requireRole('admin');

$cursosPermitidos = [
    '7mo', '8vo', '9no',
    '10° Informática', '11° Informática', '12° Informática',
    '10° Agronomía', '11° Agronomía', '12° Agronomía',
    '10° Electromecánica', '11° Electromecánica', '12° Electromecánica'
];
$seccionesPermitidas = ['A', 'B', 'C', 'D'];

$busqueda = trim($_GET['q'] ?? '');
$curso = trim($_GET['curso'] ?? '');
$seccion = trim($_GET['seccion'] ?? '');
if (!in_array($curso, $cursosPermitidos, true)) $curso = '';
if (!in_array($seccion, $seccionesPermitidas, true)) $seccion = '';

$where = [];
$params = [];
$types = '';
if ($busqueda !== '') {
    // Búsqueda flexible: nombre completo, apellido + nombre, código, carnet y DNI.
    // El DNI también se compara sin espacios, guiones ni puntos para que formatos
    // como 0508-2008-00510 y 0508200800510 encuentren al mismo estudiante.
    $where[] = "(
        CONCAT_WS(' ', nombre, apellido) LIKE ?
        OR CONCAT_WS(' ', apellido, nombre) LIKE ?
        OR nombre LIKE ?
        OR apellido LIKE ?
        OR codigo_estudiante LIKE ?
        OR carnet_number LIKE ?
        OR dni LIKE ?
        OR REPLACE(REPLACE(REPLACE(dni, '-', ''), ' ', ''), '.', '') LIKE ?
    )";

    $like = '%' . $busqueda . '%';
    $dniLimpio = preg_replace('/[\s.\-]+/u', '', $busqueda);
    $likeDni = '%' . $dniLimpio . '%';

    for ($i = 0; $i < 7; $i++) $params[] = $like;
    $params[] = $likeDni;
    $types .= 'ssssssss';
}
if ($curso !== '') {
    $where[] = 'curso = ?';
    $params[] = $curso;
    $types .= 's';
}
if ($seccion !== '') {
    $where[] = 'seccion = ?';
    $params[] = $seccion;
    $types .= 's';
}

$sql = 'SELECT * FROM estudiantes';
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY curso ASC, seccion ASC, apellido ASC, nombre ASC';
$stmt = mysqli_prepare($conn, $sql);
if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$totalFiltrados = $result ? mysqli_num_rows($result) : 0;

include '../header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
    <div>
        <h1><i class="fas fa-users"></i> Gestión de Estudiantes</h1>
        <a href="dashboard.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left"></i> Volver</a>
        <p class="text-secondary mb-0">Busca por nombre o nombre completo, DNI, código o carnet; también puedes filtrar por grado/carrera y sección.</p>
    </div>
    <a href="estudiante_form.php" class="btn btn-primary"><i class="fas fa-user-plus"></i> Nuevo Estudiante</a>
</div>

<div class="filter-panel mb-3">
    <form method="GET" class="row g-3 align-items-end" id="studentFilters">
        <div class="col-lg-4 col-md-6">
            <label for="q">Buscar alumno</label>
            <input id="q" type="search" name="q" class="form-control" value="<?php echo htmlspecialchars($busqueda); ?>" placeholder="Nombre completo, DNI, código o carnet">
        </div>
        <div class="col-lg-4 col-md-6">
            <label for="curso">Grado / Carrera</label>
            <select id="curso" name="curso" class="form-select">
                <option value="">Todos los grados y carreras</option>
                <optgroup label="Educación Básica">
                    <?php foreach (['7mo','8vo','9no'] as $op): ?>
                        <option value="<?php echo htmlspecialchars($op); ?>" <?php echo $curso === $op ? 'selected' : ''; ?>><?php echo htmlspecialchars($op); ?></option>
                    <?php endforeach; ?>
                </optgroup>
                <optgroup label="BTP en Informática">
                    <?php foreach (['10° Informática','11° Informática','12° Informática'] as $op): ?>
                        <option value="<?php echo htmlspecialchars($op); ?>" <?php echo $curso === $op ? 'selected' : ''; ?>><?php echo htmlspecialchars($op); ?></option>
                    <?php endforeach; ?>
                </optgroup>
                <optgroup label="BTP en Agronomía">
                    <?php foreach (['10° Agronomía','11° Agronomía','12° Agronomía'] as $op): ?>
                        <option value="<?php echo htmlspecialchars($op); ?>" <?php echo $curso === $op ? 'selected' : ''; ?>><?php echo htmlspecialchars($op); ?></option>
                    <?php endforeach; ?>
                </optgroup>
                <optgroup label="BTP en Electromecánica">
                    <?php foreach (['10° Electromecánica','11° Electromecánica','12° Electromecánica'] as $op): ?>
                        <option value="<?php echo htmlspecialchars($op); ?>" <?php echo $curso === $op ? 'selected' : ''; ?>><?php echo htmlspecialchars($op); ?></option>
                    <?php endforeach; ?>
                </optgroup>
            </select>
        </div>
        <div class="col-lg-2 col-md-6">
            <label for="seccion">Sección</label>
            <select id="seccion" name="seccion" class="form-select">
                <option value="">Todas</option>
                <?php foreach ($seccionesPermitidas as $op): ?>
                    <option value="<?php echo $op; ?>" <?php echo $seccion === $op ? 'selected' : ''; ?>>Sección <?php echo $op; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-lg-2 col-md-6">
            <div class="filter-actions">
                <button type="submit" class="btn btn-info"><i class="fas fa-magnifying-glass"></i> Buscar</button>
                <a href="estudiantes.php" class="btn btn-secondary" title="Limpiar filtros"><i class="fas fa-rotate-left"></i></a>
            </div>
        </div>
    </form>
    <div class="filter-result"><i class="fas fa-filter"></i> <?php echo $totalFiltrados; ?> estudiante(s) encontrado(s)</div>
</div>

<form id="carnetSelectionForm" action="carnet_print.php" method="POST" target="_blank">
    <div class="selection-bar sticky-selection">
        <div>
            <div class="form-check mb-1">
                <input class="form-check-input" type="checkbox" id="selectAllCarnets">
                <label class="form-check-label mb-0" for="selectAllCarnets">Seleccionar todos los alumnos visibles</label>
            </div>
            <div class="selection-summary"><strong id="selectedCount">0</strong> carnet(s) preparado(s) para imprimir</div>
        </div>
        <div class="toolbar-actions">
            <button type="button" class="btn btn-secondary" id="clearSelectionBtn" disabled><i class="fas fa-xmark"></i> Quitar selección</button>
            <button type="submit" class="btn btn-info" id="printSelectedBtn" disabled><i class="fas fa-print"></i> Imprimir carnets seleccionados</button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-dark">
            <thead>
                <tr>
                    <th style="width:62px" class="text-center">Carnet</th>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Grado / Carrera</th>
                    <th>Sección</th>
                    <th>Jornada</th>
                    <th>N.º Carnet</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$result || $totalFiltrados === 0): ?>
                <tr><td colspan="8" class="text-center py-4 text-secondary"><i class="fas fa-user-slash"></i> No hay estudiantes que coincidan con los filtros.</td></tr>
            <?php else: ?>
                <?php while ($row = mysqli_fetch_assoc($result)): $id = (int)$row['id']; ?>
                    <tr>
                        <td class="text-center">
                            <input class="form-check-input student-select" type="checkbox" name="ids[]" value="<?php echo $id; ?>" aria-label="Seleccionar carnet de <?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellido']); ?>">
                        </td>
                        <td><strong><?php echo htmlspecialchars($row['codigo_estudiante']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellido']); ?></td>
                        <td><span class="course-chip"><?php echo htmlspecialchars($row['curso']); ?></span></td>
                        <td><span class="section-chip"><?php echo htmlspecialchars($row['seccion']); ?></span></td>
                        <td><?php echo htmlspecialchars($row['jornada']); ?></td>
                        <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($row['carnet_number']); ?></span></td>
                        <td>
                            <div class="action-buttons">
                                <a href="estudiante_form.php?id=<?php echo $id; ?>" class="btn btn-sm btn-warning" title="Editar estudiante"><i class="fas fa-edit"></i></a>
                                <a href="qr_generator.php?id=<?php echo $id; ?>" class="btn btn-sm btn-success" title="Ver código QR"><i class="fas fa-qrcode"></i></a>
                                <a href="carnet_print.php?id=<?php echo $id; ?>" target="_blank" class="btn btn-sm btn-info" title="Imprimir solo este carnet"><i class="fas fa-id-card"></i></a>
                                <a href="estudiante_delete.php?id=<?php echo $id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este estudiante?')" title="Eliminar estudiante"><i class="fas fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</form>

<script>
(function () {
    const form = document.getElementById('carnetSelectionForm');
    const selectAll = document.getElementById('selectAllCarnets');
    const checkboxes = Array.from(document.querySelectorAll('.student-select'));
    const count = document.getElementById('selectedCount');
    const printButton = document.getElementById('printSelectedBtn');
    const clearButton = document.getElementById('clearSelectionBtn');

    function updateSelection() {
        const selected = checkboxes.filter(cb => cb.checked).length;
        count.textContent = selected;
        printButton.disabled = selected === 0;
        clearButton.disabled = selected === 0;
        selectAll.disabled = checkboxes.length === 0;
        selectAll.checked = checkboxes.length > 0 && selected === checkboxes.length;
        selectAll.indeterminate = selected > 0 && selected < checkboxes.length;
        checkboxes.forEach(cb => cb.closest('tr')?.classList.toggle('row-selected', cb.checked));
    }

    selectAll.addEventListener('change', function () {
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updateSelection();
    });
    clearButton.addEventListener('click', function () {
        checkboxes.forEach(cb => cb.checked = false);
        updateSelection();
    });
    checkboxes.forEach(cb => cb.addEventListener('change', updateSelection));
    form.addEventListener('submit', function (event) {
        const selected = checkboxes.filter(cb => cb.checked).length;
        if (!selected) {
            event.preventDefault();
            alert('Selecciona al menos un estudiante para imprimir su carnet.');
            return;
        }
        if (selected > 30 && !confirm('Has seleccionado ' + selected + ' carnets. ¿Deseas continuar con la impresión?')) {
            event.preventDefault();
        }
    });
    updateSelection();
})();
</script>

<?php
mysqli_stmt_close($stmt);
include '../footer.php';
?>
