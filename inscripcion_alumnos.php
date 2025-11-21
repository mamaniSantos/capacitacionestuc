<?php
require_once 'db_connect.php';

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$mensaje = "";

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_alumno = $_POST['id_alumno'] ?? '';
    $id_curso = $_POST['id_curso'] ?? '';

    if ($id_alumno === "" || $id_curso === "") {
        $mensaje = "Faltan datos.";
    } else {

        // Verificar si ya está inscripto
        $sql_check = "SELECT * FROM inscripciones_tuc WHERE id_alumno = ? AND id_curso = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("ii", $id_alumno, $id_curso);
        $stmt_check->execute();
        $result = $stmt_check->get_result();

        if ($result->num_rows > 0) {
            $mensaje = "El alumno ya está inscripto en este curso.";
        } else {
            // Insertar la inscripción
            $sql = "INSERT INTO inscripciones_tuc (id_alumno, id_curso) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $id_alumno, $id_curso);

            if ($stmt->execute()) {
                $mensaje = "Alumno inscripto correctamente.";
            } else {
                $mensaje = "Error al inscribir.";
            }
        }
    }
}

// Traer alumnos y cursos reales
$alumnos = $conn->query("SELECT id_alumno, nombre, apellido, dni FROM alumnos_tuc ORDER BY apellido, nombre");
$cursos  = $conn->query("SELECT id_curso, nombre_curso FROM cursos_tuc ORDER BY nombre_curso");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inscribir Alumno a Curso</title>

<style>
    body{font-family:Arial,Helvetica,sans-serif;background:#f4f6f8;margin:0;padding:20px}
    .card{max-width:650px;margin:20px auto;background:#fff;padding:20px;border-radius:10px;
        box-shadow:0 4px 12px rgba(0,0,0,0.08)}
    h2{margin-top:0;color:#1e293b}
    label{font-weight:600;margin-top:12px;display:block;color:#334155}
    select,input{width:100%;padding:10px;margin-top:6px;border-radius:8px;border:1px solid #cbd5e1}
    button{background:#2563eb;color:white;border:none;padding:10px 14px;margin-top:15px;border-radius:8px;
        font-weight:700;cursor:pointer}
    button:hover{background:#1d4ed8}
    .msg{padding:10px;border-radius:8px;margin-bottom:12px;font-weight:600;text-align:center}
    .success{background:#ecfdf5;color:#065f46;border:1px solid #bbf7d0}
    .danger{background:#fef2f2;color:#7f1d1d;border:1px solid #fecaca}
    a.btn{display:inline-block;text-decoration:none;background:#e2e8f0;padding:10px 12px;border-radius:8px;
        font-weight:600;color:#334155;margin-top:10px}
    a.btn:hover{background:#cbd5e1}
</style>

</head>
<body>

<div class="card">
    <h2>Inscribir Alumno al Curso</h2>

    <?php if ($mensaje !== ""): ?>
        <div class="msg <?= strpos($mensaje,'correctamente') !== false ? 'success' : 'danger' ?>">
            <?= h($mensaje) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        
        <label>Alumno</label>
        <select name="id_alumno" required>
            <option value="">-- Seleccionar alumno --</option>
            <?php while($a = $alumnos->fetch_assoc()): ?>
                <option value="<?= $a['id_alumno'] ?>">
                    <?= h($a['apellido'] . ", " . $a['nombre'] . " (DNI: " . $a['dni'] . ")") ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Curso</label>
        <select name="id_curso" required>
            <option value="">-- Seleccionar curso --</option>
            <?php while($c = $cursos->fetch_assoc()): ?>
                <option value="<?= $c['id_curso'] ?>">
                    <?= h($c['nombre_curso']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit">Inscribir</button>
        <a class="btn" href="index.php">Volver al menú</a>

    </form>
</div>

</body>
</html>

