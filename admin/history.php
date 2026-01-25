<?php
require __DIR__ . "/../config/db.php";
require __DIR__ . "/../includes/auth.php";
require_role('librarian');
require __DIR__ . "/../includes/header.php";

$rows = $pdo->query("
  SELECT b.id, u.full_name, bk.title, bk.accession_no, b.borrowed_at, b.due_at, b.returned_at, b.status
  FROM borrows b
  JOIN users u ON u.id=b.user_id
  JOIN books bk ON bk.id=b.book_id
  ORDER BY b.id DESC
")->fetchAll();
?>
<div class="card">
  <h2>Borrow History</h2>
  <table>
    <thead>
      <tr>
        <th>ID</th><th>Student</th><th>Book</th><th>Borrowed</th><th>Due</th><th>Returned</th><th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td><?= htmlspecialchars($r['full_name']) ?></td>
          <td><?= htmlspecialchars($r['title']) ?> (<?= htmlspecialchars($r['accession_no']) ?>)</td>
          <td><?= htmlspecialchars($r['borrowed_at']) ?></td>
          <td><?= htmlspecialchars($r['due_at']) ?></td>
          <td><?= htmlspecialchars($r['returned_at'] ?? '-') ?></td>
          <td><span class="badge"><?= htmlspecialchars($r['status']) ?></span></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
