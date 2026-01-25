<?php
require __DIR__ . "/includes/header.php";
$user = $_SESSION['user'] ?? null;
?>
<div class="card">
  <h2>Library Book Borrowing System (QR/Barcode)</h2>
  <p>This system is web-based with hardware integration via camera/QR scanning.</p>

  <?php if (!$user): ?>
    <p><a href="/library_qr/login.php">Login</a> or <a href="/library_qr/register.php">Register</a></p>
  <?php else: ?>
    <?php if ($user['role'] === 'librarian'): ?>
      <p><a href="/library_qr/admin/dashboard.php">Go to Librarian Dashboard</a></p>
    <?php else: ?>
      <p><a href="/library_qr/student/dashboard.php">Go to Student Dashboard</a></p>
    <?php endif; ?>
  <?php endif; ?>
</div>
<?php require __DIR__ . "/includes/footer.php"; ?>
