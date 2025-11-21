<?php
require_once 'db_connect.php';

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$filter_course = intval($_GET['curso'] ?? 0);

// obtener cursos reales
$cursos_res = $conn->query("SELECT id_curso, nombre_curso FROM cursos_tuc ORDER BY nombre_curso ASC");

// consulta alumnos + cursos
if ($filter_course > 0) {

    $sql = "
      SELECT a.*, 
      GROUP_CONCAT(c.nombre_curso SEPARATOR ', ') AS cursos
      FROM alumnos_tuc a
      INNER JOIN inscripciones_tuc i ON a.id_alumno = i.id_alumno
      INNER JOIN cursos_tuc c ON i.id_curso = c.id_curso
      WHERE i.id_curso = ?
      GROUP BY a.id_alumno
      ORDER BY a.apellido ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $filter_course);
    $stmt->execute();
    $resultado = $stmt->get_result();

} else {

    // Todos los alumnos con lista de cursos
    $sql = "
      SELECT a.*,
      GROUP_CONCAT(c.nombre_curso SEPARATOR ', ') AS cursos
      FROM alumnos_tuc a
      LEFT JOIN inscripciones_tuc i ON a.id_alumno = i.id_alumno
      LEFT JOIN cursos_tuc c ON i.id_curso = c.id_curso
      GROUP BY a.id_alumno
      ORDER BY a.apellido ASC
    ";

    $resultado = $conn->query($sql);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Listado de Alumnos</title>
<style>
    body { font-family: Arial; padding: 20px; background: #f7f7f7; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; background:white; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    th { background-color: #eaeaea; }
    .volver {
        display: inline-block; margin-top: 20px;
        text-decoration: none; padding: 8px 12px;
        background: #007bff; color: white; border-radius: 5px;
    }
    .volver:hover { background: #0056b3; }
    .estado-A { color: green; font-weight: bold; }
    .estado-I { color: red; font-weight: bold; }
</style>
</head>
<body>

<h2>Listado de Alumnos</h2>

<form method="GET" action="">
    <label>Filtrar por curso:
        <select name="curso" onchange="this.form.submit()">
            <option value="0">-- Todos los cursos --</option>
            <?php while ($c = $cursos_res->fetch_assoc()): ?>
                <option value="<?=$c['id_curso']?>" <?=($filter_course == $c['id_curso'] ? 'selected' : '')?>>
                    <?=h($c['nombre_curso'])?>
                </option>
            <?php endwhile; ?>
        </select>
    </label>
</form>

<?php
if ($resultado->num_rows > 0) {

    echo "<table>";
    echo "<tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>DNI</th>
            <th>Teléfono</th>
            <th>Cursos</th>
            <th>Estado</th>
          </tr>";

    while ($fila = $resultado->fetch_assoc()) {

        $estado = $fila["estado"] ?? 'A';
        $estado_class = ($estado == "A") ? "estado-A" : "estado-I";

        echo "<tr>";
        echo "<td>" . $fila["id_alumno"] . "</td>";
        echo "<td>" . h($fila["nombre"]) . "</td>";
        echo "<td>" . h($fila["apellido"]) . "</td>";
        echo "<td>" . h($fila["dni"]) . "</td>";
        echo "<td>" . h($fila["telefono"]) . "</td>";
        echo "<td>" . h($fila["cursos"] ?: "—") . "</td>";
        echo "<td class='$estado_class'>" . $estado . "</td>";
        echo "</tr>";
    }

    echo "</table>";

} else {
    echo "<p style='color:red;'>No hay alumnos registrados.</p>";
}

$conn->close();
?>

<a href="index.php" class="volver">⬅ Volver al Menú Principal</a>
<a href="editar_alumno.php" class="volver">⬅ Editar alumnos</a>

</body>
</html>








