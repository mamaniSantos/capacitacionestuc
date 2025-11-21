<?php
require_once 'db_connect.php';

$mensaje = '';
$error = '';

// inicializar variables para evitar warnings
$nombre = $apellido = $dni = $telefono = $email = '';

// --- lógica de guardado ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $dni = trim($_POST['dni'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // validaciones
    if ($nombre === '' || $dni === '' || $telefono === '') {
        $error = "Los campos Nombre, DNI y Teléfono son obligatorios.";
    } else {
        // verificar duplicado por DNI
        $stmt = $conn->prepare("SELECT id_alumno FROM alumnos_tuc WHERE dni = ?");
        $stmt->bind_param("s", $dni);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($exists) {
            $error = "Ya existe un alumno con ese DNI.";
        } else {
            $stmt = $conn->prepare("INSERT INTO alumnos_tuc (nombre, apellido, dni, telefono, email, estado) VALUES (?, ?, ?, ?, ?, 'A')");
            $stmt->bind_param("sssss", $nombre, $apellido, $dni, $telefono, $email);
            if ($stmt->execute()) {
                $mensaje = "✅ Alumno registrado correctamente.";
                // limpiar campos del formulario
                $nombre = $apellido = $dni = $telefono = $email = '';
            } else {
                $error = "Error al registrar: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Alta de Alumno - TUC Capacitaciones</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; }
    body {
        background: #f2f2f2;
        color: #333;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 20px;
    }
    .panel {
        background: #fff;
        width: 520px;
        padding: 28px;
        border-radius: 12px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    }
    h1 {
        font-size: 20px;
        margin-bottom: 16px;
        color: #222;
        text-align: center;
    }
    form { display: grid; gap: 10px; }
    label { font-size: 14px; color: #444; }
    input[type="text"], input[type="email"], input[type="date"], textarea, select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d0d7de;
        border-radius: 8px;
        font-size: 14px;
        outline: none;
    }
    input[type="text"]:focus, input[type="email"]:focus, textarea:focus {
        border-color: #7da7ff;
        box-shadow: 0 0 0 3px rgba(125,167,255,0.08);
    }
    .actions { display:flex; gap:10px; align-items:center; margin-top:8px; }
    button.primary {
        background:#007bff;
        color:#fff;
        padding:10px 14px;
        border:none;
        border-radius:8px;
        cursor:pointer;
        font-weight:600;
    }
    button.primary:hover { background:#0069dd; transform: translateY(-1px); }
    a.link {
        display:inline-block;
        padding:10px 14px;
        background:#e9eefb;
        color:#0b5ed7;
        border-radius:8px;
        text-decoration:none;
        font-weight:600;
    }
    .msg { padding:10px 12px; border-radius:8px; margin-bottom:8px; font-weight:600; }
    .success { background:#e7f9ee; border:1px solid #a5d6a7; color:#155724; }
    .error { background:#fdecea; border:1px solid #f5c2c0; color:#721c24; }
    .small { font-size:13px; color:#666; text-align:center; margin-top:10px; }
</style>
</head>
<body>

<div class="panel">
    <h1>Alta de Nuevo Alumno</h1>

    <?php if ($mensaje): ?>
        <div class="msg success"><?=htmlspecialchars($mensaje)?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="msg error"><?=htmlspecialchars($error)?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Nombre*</label>
        <input type="text" name="nombre" value="<?=htmlspecialchars($nombre)?>" required>

        <label>Apellido</label>
        <input type="text" name="apellido" value="<?=htmlspecialchars($apellido)?>">

        <label>DNI*</label>
        <input type="text" name="dni" value="<?=htmlspecialchars($dni)?>" required>

        <label>Teléfono*</label>
        <input type="text" name="telefono" value="<?=htmlspecialchars($telefono)?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?=htmlspecialchars($email)?>">

        <div class="actions">
            <button type="submit" class="primary">Registrar Alumno</button>
            <a class="link" href="index.php">⬅ Volver al Menú</a>
        </div>
    </form>

</div>

</body>
</html>





