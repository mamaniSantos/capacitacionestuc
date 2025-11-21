<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "capacitaciones_tuc";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Alta de Curso</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f4f4;
}
.container {
    width: 500px;
    margin: 60px auto;
    background: white;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 0 10px rgba(0,0,0,0.2);
}
h2 {
    text-align: center;
}
form {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
input[type="text"], textarea {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
}
textarea {
    resize: none;
    height: 80px;
}
input[type="submit"] {
    background: #007bff;
    color: white;
    padding: 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
input[type="submit"]:hover {
    background: #0056b3;
}
.mensaje {
    text-align: center;
    margin-top: 15px;
    font-weight: bold;
}
.exito { color: green; }
.error { color: red; }
.volver {
    display: block;
    width: 180px;
    margin: 20px auto;
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
<h2>Alta de Nuevo Curso</h2>

<form method="POST" action="">
    <input type="text" name="nombre_curso" placeholder="Nombre del curso" required>
    <textarea name="descripcion" placeholder="Descripción del curso (opcional)"></textarea>
    <input type="submit" value="Registrar Curso">
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_curso = $_POST["nombre_curso"];
    $descripcion = $_POST["descripcion"];

    $sql = "INSERT INTO cursos_tuc (nombre_curso, descripcion) VALUES ('$nombre_curso', '$descripcion')";
    if ($conn->query($sql) === TRUE) {
        echo "<p class='mensaje exito'>✅ Curso registrado correctamente.</p>";
    } else {
        echo "<p class='mensaje error'>⚠ Error al registrar el curso: " . $conn->error . "</p>";
    }
}
$conn->close();
?>

<a href="index.html" class="volver">⬅ Volver al Menú Principal</a>
</div>

</body>
</html>



