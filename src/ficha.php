<?php
require_once __DIR__ . '/auth.php';
require_login();

$dni = trim($_GET['dni'] ?? '');

$stmt = db()->prepare("SELECT * FROM empleados WHERE dni=? LIMIT 1");
$stmt->execute([$dni]);
$e = $stmt->fetch();

if (!$e) {
  echo "Empleado no encontrado";
  exit;
}

$nombre = trim(($e['nombres'] ?? '') . ' ' . ($e['apellidos'] ?? ''));
$fechaHoy = date('d-m-Y');
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ficha empleado</title>
  <link rel="stylesheet" href="/assets/css/ficha.css?v=4">
</head>
<body>

<div class="ficha">
    <div class="titulo-ficha">FICHA DE PERSONAL</div>
  <div class="ficha-photo">
    <?php if (!empty($e['foto_path'])): ?>
      <img src="<?= htmlspecialchars($e['foto_path']) ?>" alt="Foto del empleado">
    <?php else: ?>
      <div class="ficha-photo-empty">👤</div>
    <?php endif; ?>
  </div>

  <div class="ficha-body">
    <h1><?= htmlspecialchars($nombre) ?></h1>

    <div class="campo">
      <div class="label">DNI</div>
      <div class="value"><?= htmlspecialchars($e['dni'] ?? '—') ?></div>
    </div>

    <div class="campo">
      <div class="label">Teléfono</div>
      <div class="value"><?= htmlspecialchars($e['telefono'] ?? '—') ?></div>
    </div>

    <div class="campo">
      <div class="label">Domicilio</div>
      <div class="value"><?= htmlspecialchars($e['domicilio'] ?? '—') ?></div>
    </div>

    <div class="campo">
      <div class="label">Tipo de sangre</div>
      <div class="value"><?= htmlspecialchars($e['tipo_sangre'] ?? '—') ?></div>
    </div>

    <div class="campo">
      <div class="label">Etnia</div>
      <div class="value"><?= htmlspecialchars($e['etnia'] ?? '—') ?></div>
    </div>

    <div class="campo">
      <div class="label">No. Personal</div>
      <div class="value"><?= htmlspecialchars($e['no_personal'] ?? '—') ?></div>
    </div>

    <div class="obs">
      <div class="label">Observaciones:</div>
      <div class="value"><?= nl2br(htmlspecialchars($e['observaciones'] ?? '—')) ?></div>
    </div>
  </div>

  <div class="ficha-footer-left">Fecha: <?= $fechaHoy ?></div>
  <div class="ficha-footer-right">Página 1 de 1</div>

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