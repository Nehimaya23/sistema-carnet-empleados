<?php
require_once __DIR__ . '/config.php';

function require_login(): void {
  if (empty($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
  }
}

function require_admin(): void {
  require_login();

  if (($_SESSION['user']['rol'] ?? '') !== 'ADMIN') {
    http_response_code(403);
    echo '403 - No autorizado';
    exit;
  }
}