<?php
require_once 'db_connect.php';

// ELIMINAR CURSO
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $stmt = $conn->prepare("DELETE FROM cursos_tuc WHERE id_curso = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: listado_cursos.php");
    exit;
}

// EDITAR -> se maneja en esta misma página por GET (traer datos)
$curso_editar = null;
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $stmt = $conn->prepare("SELECT * FROM cursos_tuc WHERE id_curso = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $curso_editar = $res->fetch_assoc();
    $stmt->close();
}

// GUARDAR EDICIÓN
if (isset($_POST['guardar_curso'])) {
    $id = intval($_POST['id_curso']);
    $nombre = trim($_POST['nombre_curso']);
    $desc = trim($_POST['descripcion']);
    $fecha = $_POST['fecha_inicio'] ?: null;
    $estado = $_POST['estado'];

    $stmt = $conn->prepare("
        UPDATE cursos_tuc 
        SET nombre_curso = ?, descripcion = ?, fecha_inicio = ?, estado = ? 
        WHERE id_curso = ?
    ");
    $stmt->bind_param("ssssi", $nombre, $desc, $fecha, $estado, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: listado_cursos.php");
    exit;
}

// LISTAR CURSOS
$cursos = $conn->query("SELECT * FROM cursos_tuc ORDER BY nombre_curso ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Listado de Cursos</title>
<style>
    :root{
        --bg:#f4f6f8;
        --card:#fff;
        --accent:#007bff;
        --muted:#6b7280;
    }
    *{box-sizing:border-box}
    body{
        font-family:Inter, Arial, Helvetica, sans-serif;
        background:var(--bg);
        margin:0;
        padding:30px 10px;
        color:#222;
    }
    .container{
        max-width:1100px;
        margin:0 auto;
    }
    .card{
        background:var(--card);
        border-radius:10px;
        padding:18px;
        box-shadow:0 6px 18px rgba(15,23,42,0.06);
    }
    h2{margin:0 0 14px 0; font-size:20px; color:#111}
    .actions-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
    .btn{
        display:inline-block;padding:8px 12px;border-radius:8px;text-decoration:none;color:#fff;background:var(--accent);
        transition:transform .12s ease, box-shadow .12s;
    }
    .btn:hover{transform:translateY(-2px); box-shadow:0 6px 18px rgba(0,123,255,0.12)}
    table{
        width:100%;
        border-collapse:collapse;
        margin-top:8px;
        font-size:14px;
    }
    th, td{
        padding:12px 10px;
        border-bottom:1px solid #eee;
        text-align:left;
    }
    th{background:#fafafa;color:var(--muted);font-weight:600}
    tr:last-child td{border-bottom:none}
    td.actions{white-space:nowrap}
    a.link-action{color:var(--accent);text-decoration:none;margin-right:10px}
    a.link-action.delete{color:#e03131}
    .form-editar{
        margin-top:20px;
        padding:16px;
        background:#fcfcff;
        border:1px solid #eef2ff;
        border-radius:8px;
    }
    label{display:block;margin-top:10px;color:#333;font-size:13px}
    input[type="text"], input[type="date"], textarea, select{
        width:100%;padding:8px;border:1px solid #d1d5db;border-radius:6px;margin-top:6px;
        font-size:14px;
    }
    textarea{min-height:90px;resize:vertical}
    button.primary{
        margin-top:12px;background:var(--accent);color:#fff;border:none;padding:10px 14px;border-radius:8px;cursor:pointer;
    }
    .small-link{font-size:13px;color:var(--muted);text-decoration:none}
</style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="actions-top">
            <h2>Listado de Cursos</h2>
            <div>
                <a class="btn" href="index.html">⬅ Volver</a>
            </div>
        </div>

        <table>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Fecha Inicio</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>

            <?php if ($cursos && $cursos->num_rows > 0): ?>
                <?php while ($c = $cursos->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($c['id_curso']) ?></td>
                    <td><?= htmlspecialchars($c['nombre_curso']) ?></td>
                    <td><?= nl2br(htmlspecialchars($c['descripcion'])) ?></td>
                    <td><?= htmlspecialchars($c['fecha_inicio']) ?></td>
                    <td><?= ($c['estado'] === 'A' ? 'Activo' : 'Inactivo') ?></td>
                    <td class="actions">
                        <a class="link-action" href="listado_cursos.php?editar=<?= $c['id_curso'] ?>">✏ Editar</a>
                        <a class="link-action delete" href="listado_cursos.php?eliminar=<?= $c['id_curso'] ?>"
                           onclick="return confirm('¿Seguro que deseas eliminar este curso?');">🗑 Eliminar</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6">No hay cursos cargados.</td></tr>
            <?php endif; ?>
        </table>

        <?php if ($curso_editar): ?>
        <div class="form-editar">
            <h3>Editar curso</h3>
            <form method="POST" style="margin-top:8px">
                <input type="hidden" name="id_curso" value="<?= htmlspecialchars($curso_editar['id_curso']) ?>">
                <label>Nombre del curso
                    <input type="text" name="nombre_curso" required value="<?= htmlspecialchars($curso_editar['nombre_curso']) ?>">
                </label>
                <label>Descripción
                    <textarea name="descripcion" required><?= htmlspecialchars($curso_editar['descripcion']) ?></textarea>
                </label>
                <label>Fecha de inicio
                    <input type="date" name="fecha_inicio" value="<?= htmlspecialchars($curso_editar['fecha_inicio']) ?>">
                </label>
                <label>Estado
                    <select name="estado" required>
                        <option value="A" <?= ($curso_editar['estado']=='A'?'selected':'') ?>>Activo</option>
                        <option value="I" <?= ($curso_editar['estado']=='I'?'selected':'') ?>>Inactivo</option>
                    </select>
                </label>
                <button class="primary" type="submit" name="guardar_curso">💾 Guardar Cambios</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>

