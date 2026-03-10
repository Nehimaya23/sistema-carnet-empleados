<?php
require_once __DIR__ . '/auth.php';
require_login();

$err = $_GET['err'] ?? null;

// Bitácora (últimos 20)
$stmt = db()->prepare("
  SELECT
    b.username,
    b.dni,
    CONCAT(e.nombres,' ',e.apellidos) AS nombre,
    b.tipo,
    DATE_FORMAT(b.fecha_hora, '%Y-%m-%d') AS fecha,
    DATE_FORMAT(b.fecha_hora, '%h:%i:%s %p') AS hora,
    b.resultado
  FROM bitacora_impresion b
  LEFT JOIN empleados e ON e.dni = b.dni
  ORDER BY b.fecha_hora DESC
  LIMIT 20
");
$stmt->execute();
$logs = $stmt->fetchAll();

$rol = $_SESSION['user']['rol'] ?? 'USER';
$user = $_SESSION['user']['username'] ?? '';
$initial = strtoupper(substr($user, 0, 1));
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="/assets/img/LogPeopleBluecolor.webp">
  <title>PeopleBlue • Buscar</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <link rel="stylesheet" href="/assets/css/styles.css?v=5">
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
    <button class="pb-avatar-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
      <div class="pb-avatar"><?= htmlspecialchars($initial) ?></div>
    </button>

    <ul class="dropdown-menu dropdown-menu-end pb-menu">
      <li class="dropdown-header"><?= htmlspecialchars($user) ?></li>

      <li>
        <a class="dropdown-item" href="#" onclick="return false;">
          <i class="bi bi-person"></i> Perfil
        </a>
      </li>

      <?php if ($rol === 'ADMIN'): ?>
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

<main class="pb-page">
  <div class="pb-surface">

    <!-- BUSCAR -->
    <div class="p-4">
      <div class="text-center mb-3">
        <h1 class="h5 mb-1" style="font-weight:800;">Buscar empleado</h1>
        <div class="text-secondary">
          Ingresa el DNI (13 dígitos) para ver el empleado y generar <b>Carnet</b> o <b>Ficha</b>.
        </div>
      </div>

      <form class="pb-search-form" action="/buscar.php" method="get">

  <div class="pb-search-wrap">
    <i class="bi bi-search"></i>

    <input
      class="form-control pb-search-input"
      name="dni"
      inputmode="numeric"
      pattern="\d{13}"
      maxlength="13"
      placeholder="0801199914474"
      required
      autofocus
    >
  </div>

  <button class="btn pb-search-btn text-white" type="submit">
    Buscar
  </button>

</form>

      <?php if ($err): ?>
        <div class="alert alert-danger rounded-4 py-2 mt-3 mb-0">
          <i class="bi bi-exclamation-triangle me-1"></i>
          <?= htmlspecialchars($err) ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- BITÁCORA -->
      <div class="p-4 mt-4">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
          <div class="fw-bold">Bitácora reciente</div>
          <div class="text-secondary small">Últimas impresiones generadas (carnet / ficha).</div>
        </div>

        <?php if ($rol === 'ADMIN'): ?>
          <a class="pb-btn-ghost text-decoration-none" href="/admin/bitacora.php">
            <i class="bi bi-journal-text me-1"></i> Ver completa
          </a>
        <?php endif; ?>
      </div>

      <div class="table-responsive">
        <table class="table table-sm align-middle pb-table mb-0">
          <thead>
            <tr>
              <th>Usuario</th>
              <th>DNI</th>
              <th>Nombre</th>
              <th>Tipo</th>
              <th>Fecha</th>
              <th>Hora</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($logs)): ?>
              <tr>
                <td colspan="7" class="text-secondary">Aún no hay registros para mostrar.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($logs as $r): ?>
                <tr>
                  <td class="text-nowrap"><?= htmlspecialchars($r['username']) ?></td>
                  <td class="text-nowrap"><?= htmlspecialchars($r['dni']) ?></td>
                  <td><?= htmlspecialchars($r['nombre'] ?? '—') ?></td>
                  <td>
                    <span class="pb-tag <?= $r['tipo'] === 'CARNET' ? 'tag-carnet' : 'tag-ficha' ?>">
                      <?= htmlspecialchars($r['tipo']) ?>
                    </span>
                  </td>
                  <td class="text-nowrap"><?= htmlspecialchars($r['fecha']) ?></td>
                  <td class="text-nowrap"><?= htmlspecialchars($r['hora']) ?></td>
                  <td>
                    <span class="pb-tag <?= $r['resultado'] === 'OK' ? 'tag-ok' : 'tag-error' ?>">
                      <?= htmlspecialchars($r['resultado']) ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</main>

<script>
  // Fondo network
  const canvas = document.getElementById("bg-network");
  const ctx = canvas.getContext("2d");

  function resize(){
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

  function draw(){
    ctx.clearRect(0,0,canvas.width,canvas.height);

    for(const p of particles){
      p.x += p.vx; p.y += p.vy;
      if(p.x < 0 || p.x > canvas.width) p.vx *= -1;
      if(p.y < 0 || p.y > canvas.height) p.vy *= -1;

      ctx.beginPath();
      ctx.arc(p.x,p.y,2,0,Math.PI*2);
      ctx.fillStyle="rgba(110,168,254,0.75)";
      ctx.fill();
    }

    for(let i=0;i<particles.length;i++){
      for(let j=i+1;j<particles.length;j++){
        const dx = particles[i].x - particles[j].x;
        const dy = particles[i].y - particles[j].y;
        const dist = Math.sqrt(dx*dx + dy*dy);

        if(dist < 120){
          ctx.beginPath();
          ctx.moveTo(particles[i].x,particles[i].y);
          ctx.lineTo(particles[j].x,particles[j].y);
          ctx.strokeStyle="rgba(110,168,254,0.13)";
          ctx.stroke();
        }
      }
    }
    requestAnimationFrame(draw);
  }
  draw();
  window.addEventListener("resize", resize);

  document.querySelector("input[name='dni']").addEventListener("input", function(){

  this.value = this.value.replace(/\D/g,'');

});

document.querySelector(".pb-search-input")
.addEventListener("keypress",function(e){

  if(e.key === "Enter"){
    this.form.submit();
  }

});
</script>





<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>