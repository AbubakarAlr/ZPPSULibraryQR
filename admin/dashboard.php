<?php
require __DIR__ . "/../config/db.php";
require __DIR__ . "/../includes/auth.php";
require_role('librarian');
require __DIR__ . "/../includes/header.php";
?>
<div class="card">
  <h2>Librarian Dashboard</h2>
  <p>
    <a href="/library_qr/admin/books.php">Manage Books</a> |
    <a href="/library_qr/admin/scan.php">Scan to Borrow</a> |
    <a href="/library_qr/admin/returns.php">Return Book</a> |
    <a href="/library_qr/admin/history.php">Borrow History</a>
  </p>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
