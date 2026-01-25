<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$user = $_SESSION['user'] ?? null;
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Library QR</title>
  <link rel="stylesheet" href="/library_qr/public/assets/style.css">
</head>
<body>
  <nav class="nav">
    <div class="nav-left">
      <a href="/library_qr/index.php">Library QR</a>
    </div>
    <div class="nav-right">
      <?php if ($user): ?>
        <span class="pill">Logged in: <?= htmlspecialchars($user['full_name']) ?> (<?= htmlspecialchars($user['role']) ?>)</span>
        <a href="/library_qr/logout.php">Logout</a>
      <?php else: ?>
        <a href="/library_qr/login.php">Login</a>
        <a href="/library_qr/register.php">Register</a>
      <?php endif; ?>
    </div>
  </nav>
  <main class="container">
