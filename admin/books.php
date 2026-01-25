<?php
require __DIR__ . "/../config/db.php";
require __DIR__ . "/../includes/auth.php";
require_role('librarian');
require __DIR__ . "/../includes/header.php";

// Delete
if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];
  $stmt = $pdo->prepare("DELETE FROM books WHERE id=?");
  $stmt->execute([$id]);
  header("Location: /library_qr/admin/books.php");
  exit;
}

// List
$books = $pdo->query("SELECT * FROM books ORDER BY id DESC")->fetchAll();
?>
<div class="card">
  <h2>Books</h2>
  <p><a href="/library_qr/admin/book_form.php">Add New Book</a></p>

  <table>
    <thead>
      <tr>
        <th>ID</th><th>Accession</th><th>Title</th><th>Status</th><th>QR</th><th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($books as $b): ?>
        <tr>
          <td><?= (int)$b['id'] ?></td>
          <td><?= htmlspecialchars($b['accession_no']) ?></td>
          <td><?= htmlspecialchars($b['title']) ?></td>
          <td><span class="badge"><?= htmlspecialchars($b['status']) ?></span></td>
          <td>
            <?php if ($b['qr_path']): ?>
              <a href="<?= htmlspecialchars($b['qr_path']) ?>" target="_blank">View QR</a>
            <?php else: ?>
              —
            <?php endif; ?>
          </td>
          <td>
            <a href="/library_qr/admin/book_form.php?id=<?= (int)$b['id'] ?>">Edit</a>
            |
            <a href="/library_qr/admin/books.php?delete=<?= (int)$b['id'] ?>" onclick="return confirm('Delete this book?')">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
