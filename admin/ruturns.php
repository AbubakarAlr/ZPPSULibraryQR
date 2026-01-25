<?php
require __DIR__ . "/../config/db.php";
require __DIR__ . "/../includes/auth.php";
require_role('librarian');
require __DIR__ . "/../includes/header.php";

$err = "";
$ok = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $borrow_id = (int)($_POST['borrow_id'] ?? 0);
  if ($borrow_id <= 0) {
    $err = "Invalid borrow record.";
  } else {
    $stmt = $pdo->prepare("SELECT * FROM borrows WHERE id=? AND status='borrowed' LIMIT 1");
    $stmt->execute([$borrow_id]);
    $br = $stmt->fetch();

    if (!$br) {
      $err = "Borrow record not found or already returned.";
    } else {
      $pdo->beginTransaction();
      try {
        $stmt = $pdo->prepare("UPDATE borrows SET status='returned', returned_at=NOW() WHERE id=?");
        $stmt->execute([$borrow_id]);

        $stmt = $pdo->prepare("UPDATE books SET status='available' WHERE id=?");
        $stmt->execute([(int)$br['book_id']]);

        $pdo->commit();
        $ok = "Book returned successfully.";
      } catch (Throwable $t) {
        $pdo->rollBack();
        $err = "Failed to return.";
      }
    }
  }
}

$open = $pdo->query("
  SELECT b.id AS borrow_id, u.full_name, bk.title, bk.accession_no, b.borrowed_at, b.due_at
  FROM borrows b
  JOIN users u ON u.id=b.user_id
  JOIN books bk ON bk.id=b.book_id
  WHERE b.status='borrowed'
  ORDER BY b.borrowed_at DESC
")->fetchAll();
?>
<div class="card">
  <h2>Return Book</h2>
  <?php if ($err): ?><p style="color:#b00;"><?= htmlspecialchars($err) ?></p><?php endif; ?>
  <?php if ($ok): ?><p style="color:#070;"><?= htmlspecialchars($ok) ?></p><?php endif; ?>

  <table>
    <thead>
      <tr>
        <th>Borrow ID</th><th>Student</th><th>Book</th><th>Borrowed</th><th>Due</th><th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($open as $r): ?>
        <tr>
          <td><?= (int)$r['borrow_id'] ?></td>
          <td><?= htmlspecialchars($r['full_name']) ?></td>
          <td><?= htmlspecialchars($r['title']) ?> (<?= htmlspecialchars($r['accession_no']) ?>)</td>
          <td><?= htmlspecialchars($r['borrowed_at']) ?></td>
          <td><?= htmlspecialchars($r['due_at']) ?></td>
          <td>
            <form method="post" style="margin:0;">
              <input type="hidden" name="borrow_id" value="<?= (int)$r['borrow_id'] ?>">
              <button type="submit">Mark Returned</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
