<?php
require __DIR__ . "/../config/db.php";
require __DIR__ . "/../includes/auth.php";
require_role('student');
require __DIR__ . "/../includes/header.php";
?>
<div class="card">
  <h2>Student Dashboard</h2>
  <p>
    <a href="/library_qr/student/my_borrows.php">My Borrowed Books</a>
  </p>
  <p><small>Borrowing is confirmed by the librarian during scanning.</small></p>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
