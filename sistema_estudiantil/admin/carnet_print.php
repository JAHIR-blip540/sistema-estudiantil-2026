<?php
/* Derechos de autor: Jahir Alexander Zuniga Enamorado
   Duodecimo Grado en Informatica Año 2026 */
require_once '../db.php';
require_once '../funciones.php';
requireRole('admin');

$ids = [];
$bulkIds = $_POST['ids'] ?? ($_GET['ids'] ?? []);
if (is_array($bulkIds)) {
    foreach ($bulkIds as $value) {
        $id = (int)$value;
        if ($id > 0) $ids[] = $id;
    }
} elseif (is_string($bulkIds)) {
    foreach (explode(',', $bulkIds) as $value) {
        $id = (int)$value;
        if ($id > 0) $ids[] = $id;
    }
}
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id > 0) $ids[] = $id;
}
$ids = array_values(array_unique($ids));

if (!$ids) {
    die('No se seleccionaron estudiantes para generar carnets.');
}

$idList = implode(',', $ids);
$sql = "SELECT * FROM estudiantes WHERE id IN ($idList)";
$result = mysqli_query($conn, $sql);
$studentsById = [];
while ($row = mysqli_fetch_assoc($result)) {
    $studentsById[(int)$row['id']] = $row;
}
$estudiantes = [];
foreach ($ids as $id) {
    if (isset($studentsById[$id])) $estudiantes[] = $studentsById[$id];
}
if (!$estudiantes) {
    die('No se encontraron los estudiantes seleccionados.');
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carnets estudiantiles - DHS Access Control</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --hn-blue: #0073cf;
            --hn-cyan: #27c8e4;
            --deep: #06203c;
            --gold: #f5c84c;
            --paper: #eef6fb;
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            font-family: Inter, 'Segoe UI', Arial, sans-serif;
            background: #07111f;
            color: white;
            padding: 24px;
        }
        .screen-toolbar {
            width: min(1180px, 100%);
            margin: 0 auto 22px;
            padding: 16px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            background: #10233a;
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 16px;
            box-shadow: 0 12px 34px rgba(0,0,0,.22);
        }
        .toolbar-title { font-weight: 800; font-size: 1.02rem; }
        .toolbar-subtitle { color: #a9bfd2; font-size: .82rem; margin-top: 3px; }
        .toolbar-actions { display: flex; flex-wrap: wrap; gap: 9px; }
        .btn-ui {
            min-height: 42px;
            padding: 10px 15px;
            border-radius: 11px;
            border: 0;
            cursor: pointer;
            font-weight: 750;
            font-size: .9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            text-decoration: none;
        }
        .btn-print { background: linear-gradient(135deg, #0877c9, #27c8e4); color: white; }
        .btn-back { background: rgba(255,255,255,.08); color: white; border: 1px solid rgba(255,255,255,.12); }

        .cards-sheet {
            width: min(1180px, 100%);
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(324px, 1fr));
            gap: 18px;
            justify-items: center;
        }

        /* Carnet vertical estándar: 54 mm x 85.6 mm */
        .student-card {
            width: 54mm;
            height: 85.6mm;
            min-width: 54mm;
            min-height: 85.6mm;
            position: relative;
            overflow: hidden;
            border-radius: 4.2mm;
            color: white;
            background:
                radial-gradient(circle at 85% 9%, rgba(39,200,228,.38), transparent 25%),
                linear-gradient(155deg, #075d9f 0%, #063b70 48%, #041d38 100%);
            border: .5mm solid #42d4eb;
            box-shadow: 0 14px 34px rgba(0,0,0,.38);
            isolation: isolate;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .student-card::before {
            content: '';
            position: absolute;
            left: -12mm;
            right: -12mm;
            top: 30mm;
            height: 18mm;
            border-radius: 50%;
            border-top: .7mm solid rgba(255,255,255,.14);
            transform: rotate(-6deg);
            z-index: -1;
        }
        .student-card::after {
            content: '';
            position: absolute;
            inset: auto -9mm -12mm auto;
            width: 36mm;
            height: 36mm;
            border-radius: 50%;
            background: rgba(39,200,228,.09);
            z-index: -1;
        }
        .flag-strip {
            height: 3.6mm;
            background: linear-gradient(to bottom, #058bd0 0 33.33%, #fff 33.33% 66.66%, #058bd0 66.66% 100%);
            position: relative;
        }
        .flag-stars {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.7mm;
            color: #0683c6;
            font-size: 1.55mm;
            line-height: 1;
        }
        .card-inner { padding: 3.1mm 3.4mm 2.8mm; height: calc(100% - 3.6mm); display: flex; flex-direction: column; }
        .card-head { display: grid; grid-template-columns: 8.2mm 1fr 8.2mm; align-items: center; gap: 1.5mm; }
        .school-logo {
            width: 8.2mm;
            height: 8.2mm;
            border-radius: 50%;
            padding: .6mm;
            object-fit: contain;
            background: white;
            border: .35mm solid rgba(255,255,255,.9);
        }
        .school-name { text-align: center; font-weight: 850; font-size: 2.65mm; line-height: 1.08; text-transform: uppercase; letter-spacing: .05mm; }
        .school-name small { display: block; color: #b9f3fb; font-size: 1.65mm; font-weight: 700; margin-top: .7mm; letter-spacing: .22mm; }
        .deer-badge {
            width: 8.2mm;
            height: 8.2mm;
            display: grid;
            place-items: center;
            border-radius: 50%;
            color: #f8d264;
            background: rgba(1,16,31,.32);
            border: .3mm solid rgba(248,210,100,.48);
            padding: 1mm;
        }
        .deer-badge img { width: 100%; height: 100%; object-fit: contain; filter: brightness(0) saturate(100%) invert(84%) sepia(77%) saturate(614%) hue-rotate(339deg) brightness(101%); }
        .identity-zone { display: grid; grid-template-columns: 17mm 1fr; gap: 2.2mm; margin-top: 2.6mm; align-items: center; }
        .portrait {
            width: 17mm;
            height: 20mm;
            border-radius: 2.8mm;
            display: grid;
            place-items: center;
            background: linear-gradient(145deg, rgba(255,255,255,.14), rgba(255,255,255,.04));
            border: .4mm solid rgba(128,225,241,.58);
            position: relative;
            overflow: hidden;
        }
        .portrait i { font-size: 9.2mm; color: #9feaf5; opacity: .92; }
        .portrait::after { content: 'ESTUDIANTE'; position: absolute; bottom: 1.1mm; font-size: 1.25mm; letter-spacing: .24mm; color: rgba(255,255,255,.68); }
        .name { font-size: 3.1mm; font-weight: 900; line-height: 1.06; margin-bottom: 1.5mm; text-wrap: balance; }
        .mini-line { font-size: 1.85mm; color: #cdeaf4; margin-top: .8mm; line-height: 1.2; }
        .mini-line strong { color: white; font-weight: 800; }
        .data-zone { display: grid; grid-template-columns: 1fr 22.5mm; gap: 2.1mm; margin-top: 2.5mm; align-items: end; }
        .details { align-self: stretch; display: flex; flex-direction: column; justify-content: center; gap: 1.15mm; }
        .detail-label { color: #7fdbea; text-transform: uppercase; font-size: 1.42mm; letter-spacing: .24mm; font-weight: 800; }
        .detail-value { color: white; font-size: 2.05mm; font-weight: 800; margin-top: .15mm; }
        .qr-box { background: white; border-radius: 2.2mm; padding: 1.25mm; width: 22.5mm; height: 22.5mm; box-shadow: 0 3px 12px rgba(0,0,0,.24); }
        .qr-box img { width: 100%; height: 100%; display: block; object-fit: contain; }
        .card-bottom { margin-top: auto; padding-top: 2mm; border-top: .32mm solid rgba(115,224,241,.42); }
        .card-number { display: flex; align-items: center; justify-content: space-between; gap: 2mm; }
        .card-number .number { font-size: 2.4mm; font-weight: 900; letter-spacing: .15mm; }
        .honduras-sign { color: #a5eaf4; font-size: 1.5mm; font-weight: 750; display: flex; align-items: center; gap: .7mm; white-space: nowrap; }
        .mountains {
            height: 3mm;
            margin-top: 1.1mm;
            opacity: .45;
            background: linear-gradient(145deg, transparent 0 10%, rgba(255,255,255,.5) 10% 12%, transparent 12% 25%, rgba(255,255,255,.36) 25% 27%, transparent 27% 42%, rgba(255,255,255,.47) 42% 44%, transparent 44%);
            clip-path: polygon(0 100%, 13% 28%, 24% 100%, 39% 47%, 50% 100%, 66% 20%, 82% 100%, 91% 51%, 100% 100%);
            background-color: rgba(144,225,238,.42);
        }

        .screen-copyright { text-align: center; color: #8fa9bd; font-size: 11px; line-height: 1.55; padding-top: 23px; }
        .screen-copyright strong { color: #dcecf7; }

        @media (max-width: 520px) {
            body { padding: 14px 8px; }
            .screen-toolbar { align-items: stretch; }
            .toolbar-actions { width: 100%; }
            .btn-ui { flex: 1 1 130px; }
            .cards-sheet { grid-template-columns: 1fr; }
        }

        @page { size: A4 portrait; margin: 8mm; }
        @media print {
            html, body { width: 210mm; min-height: 297mm; background: white !important; padding: 0 !important; margin: 0 !important; }
            body * { visibility: hidden !important; }
            .cards-sheet, .cards-sheet * { visibility: visible !important; }
            .screen-toolbar, .screen-copyright { display: none !important; }
            .cards-sheet {
                position: absolute;
                left: 0;
                top: 0;
                width: 194mm;
                display: grid;
                grid-template-columns: repeat(3, 54mm);
                grid-auto-rows: 85.6mm;
                column-gap: 8mm;
                row-gap: 5mm;
                justify-content: start;
                align-content: start;
                margin: 0;
            }
            .student-card {
                width: 54mm !important;
                height: 85.6mm !important;
                min-width: 54mm !important;
                min-height: 85.6mm !important;
                margin: 0 !important;
                border-radius: 3mm;
                box-shadow: none !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>
    <div class="screen-toolbar no-print">
        <div>
            <div class="toolbar-title"><i class="fas fa-id-card-clip"></i> Vista previa de carnets</div>
            <div class="toolbar-subtitle"><?php echo count($estudiantes); ?> carnet(s) seleccionado(s). Al imprimir solo aparecerán los carnets.</div>
        </div>
        <div class="toolbar-actions">
            <button type="button" class="btn-ui btn-print" onclick="window.print()"><i class="fas fa-print"></i> Imprimir carnets</button>
            <a href="estudiantes.php" class="btn-ui btn-back"><i class="fas fa-arrow-left"></i> Volver</a>
        </div>
    </div>

    <main class="cards-sheet" id="printArea">
        <?php foreach ($estudiantes as $row): ?>
            <article class="student-card">
                <div class="flag-strip" aria-hidden="true">
                    <div class="flag-stars">★ ★ ★ ★ ★</div>
                </div>
                <div class="card-inner">
                    <header class="card-head">
                        <img class="school-logo" src="../css/logo.png" alt="Logo institucional">
                        <div class="school-name">
                            CEMG Técnico<br>“Dr. Jorge Fidel Durón”
                            <small>DHS ACCESS CONTROL</small>
                        </div>
                        <div class="deer-badge" title="Venado cola blanca de Honduras">
                            <img src="../assets/venado-cola-blanca.svg" alt="Venado cola blanca">
                        </div>
                    </header>

                    <section class="identity-zone">
                        <div class="portrait"><i class="fas fa-user-graduate"></i></div>
                        <div>
                            <div class="name"><?php echo h($row['nombre'] . ' ' . $row['apellido']); ?></div>
                            <div class="mini-line"><strong>Código:</strong> <?php echo h($row['codigo_estudiante']); ?></div>
                            <div class="mini-line"><strong>DNI:</strong> <?php echo h($row['dni'] ?: 'No registrado'); ?></div>
                        </div>
                    </section>

                    <section class="data-zone">
                        <div class="details">
                            <div><div class="detail-label">Curso / Sección</div><div class="detail-value"><?php echo h($row['curso'] . ' - ' . $row['seccion']); ?></div></div>
                            <div><div class="detail-label">Jornada</div><div class="detail-value"><?php echo h($row['jornada']); ?></div></div>
                            <div><div class="detail-label">Identificación</div><div class="detail-value"><?php echo h($row['carnet_number']); ?></div></div>
                        </div>
                        <div class="qr-box"><img src="<?php echo h(generarQR($row['codigo_qr'])); ?>" alt="Código QR"></div>
                    </section>

                    <footer class="card-bottom">
                        <div class="card-number">
                            <span class="number"><?php echo h($row['carnet_number']); ?></span>
                            <span class="honduras-sign"><i class="fas fa-star"></i> Honduras · 2026</span>
                        </div>
                        <div class="mountains" aria-hidden="true"></div>
                    </footer>
                </div>
            </article>
        <?php endforeach; ?>
    </main>

    <div class="screen-copyright no-print">
        <strong>© 2026 Jahir Alexander Zuniga Enamorado</strong><br>
        Duodecimo Grado en Informatica Año 2026
    </div>
</body>
</html>
