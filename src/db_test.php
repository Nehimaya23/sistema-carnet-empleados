<?php
require __DIR__ . '/config.php';

try {
  $pdo = db();
  $ver = $pdo->query("SELECT VERSION() v")->fetch();
  echo "<h1>✅ Conexión OK</h1>";
  echo "<p>MySQL version: " . htmlspecialchars($ver['v']) . "</p>";
} catch (Throwable $e) {
  http_response_code(500);
  echo "<h1>❌ Error de conexión</h1>";
  echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}
