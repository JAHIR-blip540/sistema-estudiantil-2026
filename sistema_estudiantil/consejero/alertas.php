<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */
require_once '../db.php';
requireRole('consejero');
include '../header.php';
?>

<h1><i class="fas fa-bell"></i> Alertas de Asistencia</h1>
<p class="text-secondary">Estudiantes con más de 3 faltas en el mes</p>
<a href="dashboard.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left"></i> Volver</a>

<div class="table-responsive">
    <table class="table table-dark">
        <thead>
            <tr>
                <th>Estudiante</th>
                <th>Curso</th>
                <th>Faltas</th>
                <th>Última falta</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Obtener estudiantes con más de 3 faltas en el mes
            $sql = "SELECT 
                        e.id, e.nombre, e.apellido, e.curso, e.seccion,
                        COUNT(a.id) as faltas,
                        MAX(a.fecha) as ultima_falta
                    FROM estudiantes e
                    LEFT JOIN asistencia a ON e.id = a.estudiante_id 
                        AND a.tipo = 'entrada' 
                        AND a.fecha >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
                    WHERE e.activo = 1
                    GROUP BY e.id
                    HAVING faltas > 3
                    ORDER BY faltas DESC";
            
            $result = mysqli_query($conn, $sql);
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $color = $row['faltas'] > 5 ? 'danger' : 'warning';
                    echo "<tr>
                        <td>{$row['nombre']} {$row['apellido']}</td>
                        <td>{$row['curso']} - {$row['seccion']}</td>
                        <td><span class='badge bg-$color'>{$row['faltas']}</span></td>
                        <td>" . ($row['ultima_falta'] ? date('d/m/Y', strtotime($row['ultima_falta'])) : '-') . "</td>
                        <td><span class='badge bg-$color'>Alerta</span></td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='5' class='text-center text-secondary'>No hay estudiantes con alertas</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include '../footer.php'; ?>