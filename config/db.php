<?php
declare(strict_types=1);

$host = "127.0.0.1";
$dbname = "library_qr";
$user = "root";
$pass = "";

try {
  $pdo = new PDO(
    "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
    $user,
    $pass,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
  );
} catch (PDOException $e) {
  die("DB connection failed: " . $e->getMessage());
}
