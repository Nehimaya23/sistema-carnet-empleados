<?php
require_once __DIR__ . '/auth.php';
require_login();

$dni = $_GET['dni'] ?? '';

$stmt = db()->prepare("SELECT * FROM empleados WHERE dni=? LIMIT 1");
$stmt->execute([$dni]);
$e = $stmt->fetch();

if (!$e) {
    die("Empleado no encontrado");
}

function esc($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$nombre = trim(($e['nombres'] ?? '') . ' ' . ($e['apellidos'] ?? ''));
$cargo  = $e['cargo'] ?? 'Empleado';
$idNo   = $e['no_personal'] ?? 'PB-001';
$email  = $e['correo'] ?? '';
$phone  = $e['telefono'] ?? '';

$foto = !empty($e['foto']) ? $e['foto'] : 'assets/img/default-user.png';
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Carnet PeopleBlue</title>
<link rel="stylesheet" href="/assets/css/carnet.css?v=10">
</head>
<body class="carnet-page">

<div class="print-stack">

    <!-- FRENTE -->
    <div class="card front front-page">
        <div class="photo">
            <img src="<?= esc($foto) ?>" alt="Foto empleado">
        </div>

        <div class="name"><?= esc($nombre) ?></div>

        <div class="role"><?= esc($cargo) ?></div>

        <div class="info">
            <div class="row">
                <div class="label">ID No</div>
                <div class="colon">:</div>
                <div class="value"><?= esc($idNo) ?></div>
            </div>

            <?php if (!empty($email)): ?>
            <div class="row">
                <div class="label">Email</div>
                <div class="colon">:</div>
                <div class="value"><?= esc($email) ?></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($phone)): ?>
            <div class="row">
                <div class="label">Phone</div>
                <div class="colon">:</div>
                <div class="value"><?= esc($phone) ?></div>
            </div>
            <?php endif; ?>
        </div>

        <div class="qr-box">
            <div class="qr-placeholder">
                <div class="qr-inner"></div>
            </div>
        </div>
    </div>

    <!-- REVERSO -->
    <div class="card back">
        <div class="back-id"><?= esc($idNo) ?></div>
    </div>

</div>



<script>
window.onload = function () {

    setTimeout(() => {
        window.print();

        setTimeout(() => {
            window.close();
        }, 1000);

    }, 400);

};
</script>

</body>
</html>