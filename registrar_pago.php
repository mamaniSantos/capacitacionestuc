<?php
require_once 'db_connect.php';
mysqli_report(MYSQLI_REPORT_OFF);

// helpers
function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$mensaje = '';
$error = '';

// Si viene por GET podemos preseleccionar alumno/curso
$pre_id_alumno = intval($_GET['id_alumno'] ?? 0);
$pre_id_curso  = intval($_GET['id_curso'] ?? 0);

// Procesar POST (registro)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_alumno = intval($_POST['id_alumno'] ?? 0);
    $id_curso  = intval($_POST['id_curso'] ?? 0);
    $mes_pagado = intval($_POST['mes_pagado'] ?? 0);

    if (!$id_alumno || !$id_curso || $mes_pagado < 1 || $mes_pagado > 12) {
        $error = "Completar todos los datos correctamente (alumno, curso, mes 1-12).";
    } else {
        // verificar que exista alumno y curso
        $stmt = $conn->prepare("SELECT 1 FROM alumnos_tuc WHERE id_alumno = ?");
        $stmt->bind_param("i", $id_alumno);
        $stmt->execute();
        $existsA = $stmt->get_result()->fetch_row();
        $stmt->close();

        $stmt = $conn->prepare("SELECT 1 FROM cursos_tuc WHERE id_curso = ?");
        $stmt->bind_param("i", $id_curso);
        $stmt->execute();
        $existsC = $stmt->get_result()->fetch_row();
        $stmt->close();

        if (!$existsA || !$existsC) {
            $error = "Alumno o curso seleccionado no existen.";
        } else {
            // intentar insertar
            $fecha_pago = date('Y-m-d');
            $stmt = $conn->prepare("INSERT INTO pagos_tuc (id_alumno, id_curso, mes_pagado, fecha_pago) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", $id_alumno, $id_curso, $mes_pagado, $fecha_pago);
            if ($stmt->execute()) {
                $mensaje = "Pago registrado correctamente (mes: {$mes_pagado}).";
            } else {
                // 1062 duplicate entry
                if ($conn->errno == 1062) {
                    $error = "Ya existe un pago registrado para ese mes, alumno y curso.";
                } else {
                    $error = "Error al registrar pago: " . $stmt->error;
                }
            }
            $stmt->close();
        }
    }
}

// Traer listas para el formulario (alumnos y cursos)
$alumnos = $conn->query("SELECT id_alumno, nombre, apellido, dni FROM alumnos_tuc ORDER BY apellido, nombre");
$cursos  = $conn->query("SELECT id_curso, nombre_curso FROM cursos_tuc ORDER BY nombre_curso");

// Si venimos con preselección por GET y no hubo POST, usamos esos valores para el form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $select_alumno = $pre_id_alumno;
    $select_curso  = $pre_id_curso;
} else {
    $select_alumno = intval($_POST['id_alumno'] ?? 0);
    $select_curso  = intval($_POST['id_curso'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Registrar Pago - TUC</title>
<style>
    body{font-family:Arial,Helvetica,sans-serif;background:#f4f6f8;padding:20px}
    .card{max-width:760px;margin:20px auto;background:#fff;padding:18px;border-radius:10px;box-shadow:0 6px 18px rgba(10,20,40,0.06)}
    h2{margin:0 0 12px}
    label{display:block;margin-top:10px;font-weight:600}
    select,input[type="number"]{width:100%;padding:9px;border:1px solid #d1d5db;border-radius:8px;margin-top:6px}
    .row{display:flex;gap:10px}
    .col{flex:1}
    .actions{margin-top:14px;display:flex;gap:10px;align-items:center}
    button.primary{background:#10b981;color:#fff;border:none;padding:10px 14px;border-radius:8px;cursor:pointer;font-weight:700}
    a.btn{display:inline-block;padding:9px 12px;border-radius:8px;background:#eef2ff;color:#0d4ed8;text-decoration:none;font-weight:700}
    .msg{padding:10px;border-radius:8px;margin-top:12px;font-weight:700}
    .success{background:#ecfdf5;color:#065f46;border:1px solid #bbf7d0}
    .danger{background:#fff1f2;color:#7f1d1d;border:1px solid #fecaca}
    .note{font-size:13px;color:#475569;margin-top:8px}
</style>
</head>
<body>
<div class="card">
    <h2>Registrar pagos</h2>

    <?php if ($mensaje): ?>
        <div class="msg success"><?=h($mensaje)?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="msg danger"><?=h($error)?></div>
    <?php endif; ?>

    <form method="POST" action="registrar_pago.php">
        <label>Alumno</label>
        <select name="id_alumno" required>
            <option value="">-- Seleccionar alumno --</option>
            <?php if ($alumnos && $alumnos->num_rows>0): ?>
                <?php while($r = $alumnos->fetch_assoc()): 
                    $lab = $r['apellido'] . ", " . $r['nombre'] . " (DNI: " . $r['dni'] . ")";
                    $sel = ($select_alumno == $r['id_alumno']) ? 'selected' : '';
                ?>
                    <option value="<?= $r['id_alumno'] ?>" <?= $sel ?>><?= h($lab) ?></option>
                <?php endwhile; ?>
            <?php else: ?>
                <option value="">No hay alumnos</option>
            <?php endif; ?>
        </select>

        <label>Curso</label>
        <select name="id_curso" required>
            <option value="">-- Seleccionar curso --</option>
            <?php if ($cursos && $cursos->num_rows>0): ?>
                <?php while($c = $cursos->fetch_assoc()):
                    $selc = ($select_curso == $c['id_curso']) ? 'selected' : '';
                ?>
                    <option value="<?= $c['id_curso'] ?>" <?= $selc ?>><?= h($c['nombre_curso']) ?></option>
                <?php endwhile; ?>
            <?php else: ?>
                <option value="">No hay cursos</option>
            <?php endif; ?>
        </select>

        <div class="row">
            <div class="col">
                <label>Mes pagado (1 – 12)</label>
                <input type="number" name="mes_pagado" min="1" max="12" required value="<?= h(intval($_POST['mes_pagado'] ?? '')) ?>">
            </div>
            <div class="col" style="align-self:flex-end">
                <div class="actions">
                    <button type="submit" class="primary">Registrar pago</button>
                    <a class="btn" href="gestion_pagos.php">Ir a gestión</a>
                    <a class="btn" href="index.php">Volver al menú principal</a>
                </div>
            </div>
        </div>

    </form>
</div>
</body>
</html>







