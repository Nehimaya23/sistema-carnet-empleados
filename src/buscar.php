<?php
require_once __DIR__ . '/auth.php';
require_login();

$dni = trim($_GET['dni'] ?? '');
if (!preg_match('/^\d{13}$/', $dni)) {
  header('Location: /index.php?err=DNI inválido (13 números).');
  exit;
}

$stmt = db()->prepare("SELECT * FROM empleados WHERE dni=? AND activo=1 LIMIT 1");
$stmt->execute([$dni]);
$e = $stmt->fetch();

if (!$e) {
  header('Location: /index.php?err=No se encontró empleado con ese DNI.');
  exit;
}

function edad(?string $fecha): string {
  if (!$fecha) return '—';
  $f = new DateTime($fecha);
  return $f->diff(new DateTime())->y . " años";
}

$nombreCompleto = trim(($e['nombres'] ?? '') . ' ' . ($e['apellidos'] ?? ''));
$iniciales = strtoupper(substr(trim($e['nombres'] ?? 'X'), 0, 1) . substr(trim($e['apellidos'] ?? ''), 0, 1));
$fotoPath = trim($e['foto_path'] ?? '');
$fotoUrl  = $fotoPath ? $fotoPath : null;
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="/assets/img/LogPeopleBluecolor.webp">
  <title>PeopleBlue • Empleado</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <link rel="stylesheet" href="/assets/css/main.css?v=11">
</head>

<body>
<canvas id="bg-network"></canvas>

<header class="pb-topbar">
  <a href="/index.php" class="pb-brand text-decoration-none">
    <img src="/assets/img/LogoPeopleBlueblanco.webp" alt="PeopleBlue">
    <div>
      PeopleBlue Carnets
      <div class="pb-muted" style="font-size:.85rem;">Sistema de carnets</div>
    </div>
  </a>

  <div class="dropdown">
    <button class="pb-avatar-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
      <div class="pb-avatar"><?= strtoupper(substr($_SESSION['user']['username'], 0, 1)) ?></div>
    </button>

    <ul class="dropdown-menu dropdown-menu-end pb-menu">
      <li class="dropdown-header"><?= htmlspecialchars($_SESSION['user']['username']) ?></li>

      <?php if (($_SESSION['user']['rol'] ?? '') === 'ADMIN'): ?>
      <li>
        <a class="dropdown-item" href="/admin/bitacora.php">
          <i class="bi bi-journal-text"></i> Bitácora
        </a>
      </li>
      <?php endif; ?>

      <li><hr class="dropdown-divider"></li>

      <li>
        <a class="dropdown-item text-danger" href="/logout.php">
          <i class="bi bi-box-arrow-right"></i> Cerrar sesión
        </a>
      </li>
    </ul>
  </div>
</header>

<main class="container pb-center pb-page-employee">
  <section class="pb-surface pb-surface-light">

    <div class="pb-employee-head">
      <div class="pb-employee-title">
        <a class="pb-back" href="/index.php" title="Volver">
          <i class="bi bi-arrow-left"></i>
        </a>

        <div>
          <div class="pb-employee-name">
            <?= htmlspecialchars($nombreCompleto ?: 'Empleado') ?>
          </div>

          <div class="pb-employee-sub">
            <span class="pb-chip-mini">
              <i class="bi bi-person-vcard"></i>
              DNI: <?= htmlspecialchars($e['dni']) ?>
            </span>

            <?php if (!empty($e['no_personal'])): ?>
            <span class="pb-chip-mini">
              <i class="bi bi-hash"></i>
              No. personal: <?= htmlspecialchars($e['no_personal']) ?>
            </span>
            <?php endif; ?>
          </div>
        </div>
      </div>

     <div class="pb-actions-bar">

  <a class="pb-action-btn is-primary"
     href="#"
     onclick="imprimirCarnet('<?= urlencode($e['dni']) ?>'); return false;">
    <i class="bi bi-credit-card-2-front"></i>
    <span>Carnet</span>
  </a>

  <a class="pb-action-btn"
     href="#"
     onclick="imprimirFicha('<?= urlencode($e['dni']) ?>'); return false;">
    <i class="bi bi-file-earmark-text"></i>
    <span>Ficha</span>
  </a>

  <a class="pb-action-btn"
     href="/acta.php?dni=<?= urlencode($e['dni']) ?>"
     target="_blank"
     rel="noopener">
    <i class="bi bi-file-earmark-text"></i>
    <span>Acta</span>
  </a>

</div>
    </div>

    <div class="pb-employee-grid">
      <aside class="pb-card pb-card-soft">
        <div class="pb-photo">
          <?php if ($fotoUrl): ?>
            <img src="<?= htmlspecialchars($fotoUrl) ?>" alt="Foto empleado">
          <?php else: ?>
            <div class="pb-photo-fallback"><?= htmlspecialchars($iniciales ?: 'PB') ?></div>
          <?php endif; ?>
        </div>

        <div class="pb-mini">
          <div class="pb-mini-row">
            <span class="pb-mini-label">Edad</span>
            <span class="pb-mini-value"><?= htmlspecialchars(edad($e['fecha_nacimiento'] ?? null)) ?></span>
          </div>

          <div class="pb-mini-row">
            <span class="pb-mini-label">Tipo sangre</span>
            <span class="pb-mini-value"><?= htmlspecialchars($e['tipo_sangre'] ?: '—') ?></span>
          </div>

          <div class="pb-mini-row">
            <span class="pb-mini-label">Etnia</span>
            <span class="pb-mini-value"><?= htmlspecialchars($e['etnia'] ?: '—') ?></span>
          </div>
        </div>
      </aside>

      <div class="pb-card pb-card-soft">
        <div class="pb-section-title">
          <i class="bi bi-info-circle"></i> Información general
        </div>

        <div class="pb-kv">
          <div class="pb-kv-item">
            <div class="pb-kv-k">Nombres</div>
            <div class="pb-kv-v"><?= htmlspecialchars($e['nombres'] ?: '—') ?></div>
          </div>

          <div class="pb-kv-item">
            <div class="pb-kv-k">Apellidos</div>
            <div class="pb-kv-v"><?= htmlspecialchars($e['apellidos'] ?: '—') ?></div>
          </div>

          <div class="pb-kv-item">
            <div class="pb-kv-k">Teléfono</div>
            <div class="pb-kv-v"><?= htmlspecialchars($e['telefono'] ?: '—') ?></div>
          </div>

          <div class="pb-kv-item">
            <div class="pb-kv-k">Domicilio</div>
            <div class="pb-kv-v"><?= htmlspecialchars($e['domicilio'] ?: '—') ?></div>
          </div>
        </div>

        <div class="pb-divider"></div>

        <div class="pb-section-title">
          <i class="bi bi-journal-text"></i> Observaciones
        </div>

        <div class="pb-observaciones">
          <?= !empty($e['observaciones']) ? nl2br(htmlspecialchars($e['observaciones'])) : '<span class="pb-muted">—</span>' ?>
        </div>
      </div>
    </div>

  </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
  const canvas = document.getElementById("bg-network");
  const ctx = canvas.getContext("2d");

  function resize() {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
  }
  resize();

  const particles = Array.from({ length: 70 }, () => ({
    x: Math.random() * canvas.width,
    y: Math.random() * canvas.height,
    vx: (Math.random() - 0.5) * 0.5,
    vy: (Math.random() - 0.5) * 0.5
  }));

  function draw() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    for (const p of particles) {
      p.x += p.vx;
      p.y += p.vy;
      if (p.x < 0 || p.x > canvas.width) p.vx *= -1;
      if (p.y < 0 || p.y > canvas.height) p.vy *= -1;

      ctx.beginPath();
      ctx.arc(p.x, p.y, 2, 0, Math.PI * 2);
      ctx.fillStyle = "rgba(110,168,254,0.75)";
      ctx.fill();
    }

    for (let i = 0; i < particles.length; i++) {
      for (let j = i + 1; j < particles.length; j++) {
        const dx = particles[i].x - particles[j].x;
        const dy = particles[i].y - particles[j].y;
        const dist = Math.sqrt(dx * dx + dy * dy);

        if (dist < 120) {
          ctx.beginPath();
          ctx.moveTo(particles[i].x, particles[i].y);
          ctx.lineTo(particles[j].x, particles[j].y);
          ctx.strokeStyle = "rgba(110,168,254,0.13)";
          ctx.stroke();
        }
      }
    }

    requestAnimationFrame(draw);
  }
  draw();

  window.addEventListener("resize", resize);

  function imprimirCarnet(dni) {
    const url = '/carnet.php?dni=' + dni;
    const popup = window.open(url, 'printCarnet', 'width=420,height=720');

    if (!popup) {
      alert('El navegador bloqueó la ventana emergente. Debes permitir popups.');
    }
  }
  function imprimirFicha(dni) {

  const url = '/ficha.php?dni=' + dni;

  const popup = window.open(
    url,
    'printFicha',
    'width=900,height=1100'
  );

  if (!popup) {
    alert('El navegador bloqueó la ventana emergente. Debes permitir popups.');
  }

}
</script>
</body>
</html>