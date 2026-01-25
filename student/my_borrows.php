<?php
require __DIR__ . "/../config/db.php";
require __DIR__ . "/../includes/auth.php";
require_role('student');
require __DIR__ . "/../includes/header.php";

$userId = (int)$_SESSION['user']['id'];
$rows = $pdo->prepare("
  SELECT b.id, bk.title, bk.accession_no, b.borrowed_at, b.due_at, b.returned_at, b.status
  FROM borrows b
  JOIN books bk ON bk.id=b.book_id
  WHERE b.user_id=?
  ORDER BY b.id DESC
");
$rows->execute([$userId]);
$data = $rows->fetchAll();
?>
<div class="card">
  <h2>My Borrowed Books</h2>
  <table>
    <thead>
      <tr><th>ID</th><th>Book</th><th>Borrowed</th><th>Due</th><th>Status</th></tr>
    </thead>
    <tbody>
      <?php foreach ($data as $r): ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td><?= htmlspecialchars($r['title']) ?> (<?= htmlspecialchars($r['accession_no']) ?>)</td>
          <td><?= htmlspecialchars($r['borrowed_at']) ?></td>
          <td><?= htmlspecialchars($r['due_at']) ?></td>
          <td><span class="badge"><?= htmlspecialchars($r['status']) ?></span></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
