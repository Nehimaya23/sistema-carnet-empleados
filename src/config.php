<?php
declare(strict_types=1);

session_start();

function db(): PDO {
  static $pdo = null;
  if ($pdo instanceof PDO) {
    return $pdo;
  }

  $host = getenv('DB_HOST') ?: 'db';
  $name = getenv('DB_NAME') ?: 'peopleblue';
  $user = getenv('DB_USER') ?: 'peopleblue_user';
  $pass = getenv('DB_PASS') ?: 'peopleblue_pass';

  $pdo = new PDO(
    "mysql:host=$host;dbname=$name;charset=utf8mb4",
    $user,
    $pass,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
  );

  $pdo->exec("SET NAMES utf8mb4");

  return $pdo;
}