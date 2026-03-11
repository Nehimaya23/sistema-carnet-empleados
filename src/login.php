<?php
require __DIR__ . '/config.php';

if (!empty($_SESSION['user'])) {
  header('Location: /index.php');
  exit;
}

$error = null;
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  $stmt = db()->prepare("SELECT username, password_hash, rol, activo FROM usuarios WHERE username=? LIMIT 1");
  $stmt->execute([$username]);
  $u = $stmt->fetch();

  if (!$u || (int)$u['activo'] !== 1 || !password_verify($password, $u['password_hash'])) {
    $error = "Usuario o contraseña incorrectos.";
  } else {
    $_SESSION['user'] = ['username' => $u['username'], 'rol' => $u['rol']];
    header('Location: /index.php');
    exit;
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="/assets/img/LogPeopleBluecolor.webp">
  <title>PeopleBlue • Login</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <link rel="stylesheet" href="/assets/css/main.css?v=3">
</head>
<body class="login-page">
<canvas id="bg-network"></canvas>

<div class="container h-100">
  <div class="row h-100 align-items-center justify-content-center py-5">
    <div class="col-12 col-sm-10 col-md-7 col-lg-5 col-xl-4">

      <div class="text-center mb-4">
        <img class="pb-logo" src="/assets/img/LogoPeopleBlueblanco.webp" alt="PeopleBlue">
     
        <div class="pb-muted">Acceso al sistema de carnets y fichas</div>

        <span class="badge rounded-pill mt-3 px-3 py-2"
              style="background: rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.12); color: rgba(255,255,255,.85);">
          <i class="bi bi-shield-lock me-1"></i> Acceso seguro
        </span>
      </div>

      <div class="pb-card">
        <div class="text-center mb-4">
          <h2 class="h5 mb-0 fw-semibold">Iniciar sesión</h2>
        </div>

        <?php if ($error): ?>
          <div class="alert alert-danger rounded-4 py-2 mb-3">
            <i class="bi bi-exclamation-triangle me-1"></i>
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <form method="post" class="vstack gap-3">
          <div>
            <label class="form-label pb-muted mb-1">Usuario</label>
            <div class="input-group input-group-lg">
              <span class="input-group-text pe-0"><i class="bi bi-person"></i></span>
              <input class="form-control pb-field ps-2"
                     name="username"
                     required
                     autocomplete="username"
                     value="<?= htmlspecialchars($username) ?>"
                     placeholder="Ej: mperez">
            </div>
          </div>

          <div class="pb-pass-wrap">
            <label class="form-label pb-muted mb-1">Contraseña</label>
            <div class="input-group input-group-lg">
              <span class="input-group-text pe-0"><i class="bi bi-key"></i></span>
              <input class="form-control pb-field ps-2 pe-5"
                     id="password"
                     type="password"
                     name="password"
                     required
                     autocomplete="current-password"
                     placeholder="••••••••">
            </div>

            <button class="pb-eye" type="button" id="togglePass" title="Mostrar/ocultar" aria-label="Mostrar u ocultar contraseña">
              <i class="bi bi-eye"></i>
            </button>
          </div>

          <button class="btn pb-btn btn-lg text-white w-100" type="submit">
            <i class="bi bi-box-arrow-in-right me-1"></i> Acceder
          </button>
        </form>
      </div>

      <div class="text-center pb-muted small mt-3">
        © <?= date('Y') ?> PeopleBlue • Carnets
      </div>

    </div>
  </div>
</div>

<script>
  // Mostrar / ocultar contraseña
  const btn = document.getElementById('togglePass');
  const input = document.getElementById('password');
  if (btn && input) {
    btn.addEventListener('click', () => {
      const isPass = input.type === 'password';
      input.type = isPass ? 'text' : 'password';
      btn.innerHTML = isPass ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
    });
  }

  // Fondo network (ligero)
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
      p.x += p.vx; p.y += p.vy;
      if (p.x < 0 || p.x > canvas.width) p.vx *= -1;
      if (p.y < 0 || p.y > canvas.height) p.vy *= -1;

      ctx.beginPath();
      ctx.arc(p.x, p.y, 2, 0, Math.PI * 2);
      ctx.fillStyle = "rgba(110,168,254,0.8)";
      ctx.fill();
    }

    for (let i = 0; i < particles.length; i++) {
      for (let j = i + 1; j < particles.length; j++) {
        const dx = particles[i].x - particles[j].x;
        const dy = particles[i].y - particles[j].y;
        const dist = Math.sqrt(dx*dx + dy*dy);

        if (dist < 120) {
          ctx.beginPath();
          ctx.moveTo(particles[i].x, particles[i].y);
          ctx.lineTo(particles[j].x, particles[j].y);
          ctx.strokeStyle = "rgba(110,168,254,0.15)";
          ctx.stroke();
        }
      }
    }

    requestAnimationFrame(draw);
  }
  draw();

  window.addEventListener("resize", resize);
</script>

</body>
</html>