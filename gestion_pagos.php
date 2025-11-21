<?php
require_once 'db_connect.php';

$alumno = null;
$inscripciones = [];
$error = '';
$mensaje = '';

// BUSCAR ALUMNO POR DNI
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar_dni'])) {

    $dni = trim($_POST['dni']);

    if ($dni === '') {
        $error = "Ingresá un DNI.";
    } else {
        $stmt = $conn->prepare("SELECT id_alumno, nombre, apellido FROM alumnos_tuc WHERE dni = ?");
        $stmt->bind_param("s", $dni);
        $stmt->execute();
        $alumno = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$alumno) {
            $error = "No se encontró alumno con ese DNI.";
        } else {

            // Cargar cursos inscriptos
            $stmt = $conn->prepare("
                SELECT c.id_curso, c.nombre_curso 
                FROM inscripciones_tuc i 
                JOIN cursos_tuc c ON i.id_curso = c.id_curso 
                WHERE i.id_alumno = ?
            ");
            $stmt->bind_param("i", $alumno['id_alumno']);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($r = $res->fetch_assoc()) $inscripciones[] = $r;
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestión de Pagos</title>

<style>
body { font-family:Arial; background:#eef2f6; padding:20px; }
.panel { background:white; padding:20px; border-radius:10px; max-width:900px; margin:auto; }
table { width:100%; border-collapse:collapse; margin-top:10px; }
th,td { padding:10px; border-bottom:1px solid #ddd; }
th { background:#f8f9fc; }
.btn { padding:8px 12px; border-radius:6px; text-decoration:none; font-weight:bold; }
.btn-blue { background:#0d6efd; color:white; }
.btn-green { background:#16a34a; color:white; }
.btn-red { background:#dc2626; color:white; }
.msg { padding:10px; border-radius:6px; margin-top:10px; }
.success { background:#d1fae5; color:#065f46; }
.error { background:#fee2e2; color:#991b1b; }
</style>

</head>
<body>
<div class="panel">

<h2>Gestión de Pagos</h2>

<!-- mensajes -->
<?php if ($error): ?>
    <div class="msg error"><?= $error ?></div>
<?php endif; ?>
<?php if ($mensaje): ?>
    <div class="msg success"><?= $mensaje ?></div>
<?php endif; ?>

<!-- BUSCADOR -->
<form method="POST" class="search">
    <input type="text" name="dni" placeholder="Ingresá DNI" required
           value="<?= htmlspecialchars($_POST['dni'] ?? '') ?>"
           style="padding:8px; width:200px;">
    <button class="btn btn-blue" name="buscar_dni">Buscar</button>
    <a href="index.html" class="btn btn-red">Volver</a>
</form>

<?php if ($alumno): ?>
    <hr><br>

    <h3>Alumno: <?= $alumno['apellido'] . ', ' . $alumno['nombre'] ?></h3>

    <?php if (empty($inscripciones)): ?>
        <p style="color:#dc2626;">Este alumno no está inscripto en ningún curso.</p>

    <?php else: ?>
        <table>
            <tr>
                <th>Curso inscripto</th>
                <th>Meses pagados de 12</th>
                <th>Acción</th>
            </tr>

            <?php foreach ($inscripciones as $cur): 
                $id_curso = $cur['id_curso'];

                // Obtener pagos
                $stmt = $conn->prepare("
                    SELECT mes_pagado, fecha_pago 
                    FROM pagos_tuc 
                    WHERE id_alumno = ? AND id_curso = ?
                    ORDER BY mes_pagado ASC
                ");
                $stmt->bind_param("ii", $alumno['id_alumno'], $id_curso);
                $stmt->execute();
                $pagos = $stmt->get_result();
                ?>
                <tr>
                    <td><b><?= $cur['nombre_curso'] ?></b></td>

                    <td>
                        <?php
                        if ($pagos->num_rows === 0) {
                            echo "<i>No hay pagos registrados</i>";
                        } else {
                            $lista = [];
                            while ($p = $pagos->fetch_assoc()) {
                                $lista[] = $p['mes_pagado'];
                            }
                            echo implode(" - ", $lista);
                        }
                        ?>
                    </td>

                    <td>
                        <a class="btn btn-green" href="registrar_pago.php?id_alumno=<?= $alumno['id_alumno'] ?>&id_curso=<?= $id_curso ?>"> Registrar otro pago </a>
                    </td>
                </tr>
            <?php endforeach; ?>

        </table>
    <?php endif; ?>

<?php endif; ?>

</div>
</body>
</html>





