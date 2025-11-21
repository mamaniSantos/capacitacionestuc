<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id_alumno']);
    // Borrado en cascada está definido en las FK (inscripciones, pagos)
    $stmt = $conn->prepare("DELETE FROM alumnos_tuc WHERE id_alumno = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: listado_alumnos.php?deleted=1");
        exit;
    } else {
        $error = "Error al eliminar: " . $stmt->error;
    }
}

if (!isset($_GET['id']) && !isset($id)) {
    header('Location: listado_alumnos.php');
    exit;
}
$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT nombre, apellido, dni FROM alumnos_tuc WHERE id_alumno = ?");
$stmt->bind_param("i",$id);
$stmt->execute();
$al = $stmt->get_result()->fetch_assoc();
$stmt->close();

?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><title>Eliminar Alumno</title></head>
<body style="font-family:Arial, sans-serif;">
<div style="max-width:600px;margin:30px auto;background:#fff;padding:20px;border-radius:8px;box-shadow:0 0 8px rgba(0,0,0,0.1)">
    <h2>Eliminar Alumno</h2>
    <?php if (!empty($error)) echo "<div style='color:red'>$error</div>"; ?>
    <p>Estás por eliminar al alumno: <strong><?=htmlspecialchars($al['nombre'].' '.$al['apellido'])?></strong> (DNI: <?=htmlspecialchars($al['dni'])?>)</p>
    <p>Esta acción eliminará también sus inscripciones y pagos. ¿Querés continuar?</p>
    <form method="POST" action="">
        <input type="hidden" name="id_alumno" value="<?=$id?>">
        <button type="submit" style="padding:8px 12px;background:#dc3545;color:#fff;border:none;border-radius:4px">Sí, eliminar</button>
        <a href="listado_alumnos.php" style="margin-left:8px">Cancelar</a>
    </form>
</div>
</body>
</html>
