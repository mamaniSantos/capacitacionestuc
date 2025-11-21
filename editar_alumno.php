<?php
require_once 'db_connect.php';

/* TABLA Y COLUMNAS */
$tabla        = 'alumnos_tuc';
$id_col       = 'id_alumno';
$nombre_col   = 'nombre';
$apellido_col = 'apellido';
$dni_col      = 'dni';
$email_col    = 'email';
$estado_col   = 'estado';

/* =============================
   🔴 ELIMINAR ALUMNO
   ============================= */
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $stmt = $conn->prepare("DELETE FROM $tabla WHERE $id_col = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: listado_alumnos.php");
    exit;
}

/* =============================
   ✏ CARGAR ALUMNO A EDITAR
   ============================= */
$alumno_editar = null;
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $stmt = $conn->prepare("SELECT * FROM $tabla WHERE $id_col = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $alumno_editar = $res->fetch_assoc();
    $stmt->close();
}

/* =============================
   💾 GUARDAR EDICIÓN (DNI NO EDITABLE)
   ============================= */
if (isset($_POST['guardar_alumno'])) {
    $id       = intval($_POST[$id_col]);
    $nombre   = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email    = trim($_POST['email']);
    $estado   = $_POST['estado'];

    // Consulta SIN modificar el DNI
    $stmt = $conn->prepare("
        UPDATE $tabla 
        SET 
            $nombre_col = ?,
            $apellido_col = ?,
            $email_col = ?,
            $estado_col = ?
        WHERE $id_col = ?
    ");

    $stmt->bind_param("ssssi", $nombre, $apellido, $email, $estado, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: listado_alumnos.php");
    exit;
}

/* =============================
   📌 LISTAR ALUMNOS
   ============================= */
$alumnos = $conn->query("SELECT * FROM $tabla ORDER BY $apellido_col ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Listado de Alumnos</title>

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
    input[type="text"], input[type="email"], select{
        width:100%;padding:8px;border:1px solid #d1d5db;border-radius:6px;margin-top:6px;
        font-size:14px;
    }
    input.disabled{
        background:#e5e7eb;
        color:#555;
        cursor:not-allowed;
    }
    button.primary{
        margin-top:12px;background:var(--accent);color:#fff;border:none;padding:10px 14px;border-radius:8px;cursor:pointer;
    }
</style>
</head>

<body>
<div class="container">
    <div class="card">

        <div class="actions-top">
            <h2>Listado de Alumnos</h2>
            <a class="btn" href="index.html">⬅ Volver</a>
        </div>

        <!-- TABLA -->
        <table>
            <tr>
                <th>ID</th>
                <th>Apellido</th>
                <th>Nombre</th>
                <th>DNI</th>
                <th>Email</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>

            <?php if ($alumnos && $alumnos->num_rows > 0): ?>
                <?php while ($a = $alumnos->fetch_assoc()): ?>
                <tr>
                    <td><?= $a[$id_col] ?></td>
                    <td><?= htmlspecialchars($a[$apellido_col]) ?></td>
                    <td><?= htmlspecialchars($a[$nombre_col]) ?></td>
                    <td><?= htmlspecialchars($a[$dni_col]) ?></td>
                    <td><?= htmlspecialchars($a[$email_col]) ?></td>
                    <td><?= ($a[$estado_col]=='A' ? 'Activo' : 'Inactivo') ?></td>

                    <td class="actions">
                        <a class="link-action" href="editar_alumno.php?editar=<?= $a[$id_col] ?>">✏ Editar</a>
                        <a class="link-action delete"
                           href="editar_alumno.php?eliminar=<?= $a[$id_col] ?>"
                           onclick="return confirm('¿Eliminar este alumno?');">🗑 Eliminar</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7">No hay alumnos cargados.</td></tr>
            <?php endif; ?>
        </table>

        <!-- FORMULARIO DE EDICIÓN -->
        <?php if ($alumno_editar): ?>
        <div class="form-editar">
            <h3>Editar Alumno</h3>

            <form method="POST">

                <input type="hidden" name="<?= $id_col ?>" value="<?= $alumno_editar[$id_col] ?>">

                <label>Nombre
                    <input type="text" name="nombre" required value="<?= htmlspecialchars($alumno_editar[$nombre_col]) ?>">
                </label>

                <label>Apellido
                    <input type="text" name="apellido" required value="<?= htmlspecialchars($alumno_editar[$apellido_col]) ?>">
                </label>


                <label>Email
                    <input type="email" name="email" value="<?= htmlspecialchars($alumno_editar[$email_col]) ?>">
                </label>

                <label>Estado
                    <select name="estado">
                        <option value="A" <?= ($alumno_editar[$estado_col]=='A'?'selected':'') ?>>Activo</option>
                        <option value="I" <?= ($alumno_editar[$estado_col]=='I'?'selected':'') ?>>Inactivo</option>
                    </select>
                </label>

                <button type="submit" name="guardar_alumno" class="primary">💾 Guardar cambios</button>
            </form>
        </div>
        <?php endif; ?>

    </div>
</div>
</body>
</html>



