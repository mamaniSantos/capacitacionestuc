<?php
// --- Conexión a la base de datos ---
$servername = "localhost";
$username = "root";
$password = "";
$database = "capacitaciones_tuc";

$conn = new mysqli($servername, $username, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Buscar Alumno por DNI</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f7f7f7;
    margin: 0;
    padding: 0;
}
.container {
    width: 450px;
    margin: 100px auto;
    background: white;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 0 10px rgba(0,0,0,0.2);
}
h2 {
    text-align: center;
    color: #333;
}
form {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
input[type="text"] {
    padding: 10px;
    border: 1px solid #aaa;
    border-radius: 5px;
}
input[type="submit"] {
    padding: 10px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
input[type="submit"]:hover {
    background: #0056b3;
}
.resultado {
    margin-top: 20px;
    padding: 15px;
    border-radius: 5px;
}
.exito {
    background: #e7f9ee;
    border: 1px solid #a5d6a7;
}
.error {
    background: #fdecea;
    border: 1px solid #f5c2c0;
}
.volver {
    display: block;
    width: 180px;
    margin: 15px auto;
    padding: 10px;
    text-align: center;
    background: #007bff;
    color: white;
    border-radius: 5px;
    text-decoration: none;
}
.volver:hover {
    background: #0056b3;
}
</style>
</head>
<body>
<div class="container">
<h2>Buscar Alumno por DNI</h2>

<form method="POST" action="">
    <input type="text" name="dni" placeholder="Ingrese DNI del alumno" required>
    <input type="submit" value="Buscar">
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dni = $_POST["dni"];

    $sql = "SELECT * FROM alumnos_tuc WHERE dni = '$dni'";
    $resultado = $conn->query($sql);

    if ($resultado->num_rows > 0) {
        $alumno = $resultado->fetch_assoc();

        echo "<div class='resultado exito'>";
        echo "<strong>Alumno encontrado:</strong><br>";
        echo "Nombre: " . $alumno["nombre"] . "<br>";
        echo "Apellido: " . $alumno["apellido"] . "<br>";
        echo "DNI: " . $alumno["dni"] . "<br>";

        // Verificar si el alumno está inscripto
        $id_alumno = $alumno["id_alumno"];
        $sql_cursos = "SELECT c.nombre_curso FROM inscripciones_tuc i INNER JOIN cursos_tuc c ON i.id_curso = c.id_curso WHERE i.id_alumno = '$id_alumno'";
        $resultado_cursos = $conn->query($sql_cursos);

        if ($resultado_cursos->num_rows > 0) {
            echo "<br><strong>Cursos inscriptos:</strong><br>";
            while ($curso = $resultado_cursos->fetch_assoc()) {
                echo "- " . $curso["nombre_curso"] . "<br>";
            }
        } else {
            echo "<br><strong style='color:red;'>El alumno no está inscripto en ningún curso.</strong>";
        }

        echo "</div>";
    } else {
        echo "<div class='resultado error'>No se encontró ningún alumno con el DNI ingresado.</div>";
    }
}
$conn->close();
?>

<a href='index.htmlphp' class='volver'>⬅ Volver al Menú Principal</a>
</div>
</body>
</html>





