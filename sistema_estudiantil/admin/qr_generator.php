<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */
require_once '../db.php';
requireRole('admin');

$id = isset($_GET['id']) ? $_GET['id'] : 0;
$sql = "SELECT * FROM estudiantes WHERE id=$id";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

if (!$row) {
    die("Estudiante no encontrado");
}

include '../header.php';
?>

<h1><i class="fas fa-qrcode"></i> Código QR</h1>

<div class="text-center">
    <div style="background: white; padding: 30px; border-radius: 16px; display: inline-block;">
        <img src="<?php echo generarQR($row['codigo_qr']); ?>" alt="QR Code">
    </div>
    
    <h3 class="mt-3"><?php echo $row['nombre'] . ' ' . $row['apellido']; ?></h3>
    <p class="text-secondary">Código: <?php echo $row['codigo_estudiante']; ?></p>
    <p class="text-secondary">Carnet: <?php echo $row['carnet_number']; ?></p>
    <p class="text-secondary"><?php echo $row['curso'] . ' - ' . $row['seccion']; ?></p>
    <p class="text-secondary">Jornada: <?php echo $row['jornada']; ?></p>
    <p class="text-secondary">QR: <?php echo $row['codigo_qr']; ?></p>
    
    <div class="toolbar-actions justify-content-center mt-3">
        <a href="estudiantes.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
        <button class="btn btn-primary" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir QR
        </button>
        <a href="carnet_print.php?id=<?php echo $row['id']; ?>" target="_blank" class="btn btn-info">
            <i class="fas fa-id-card"></i> Ver Carnet
        </a>
    </div>
</div>

<?php include '../footer.php'; ?>