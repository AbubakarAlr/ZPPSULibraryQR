<?php
require __DIR__ . "/../config/db.php";
require __DIR__ . "/../includes/auth.php";
require_role('librarian');
require __DIR__ . "/../includes/header.php";

$students = $pdo->query("SELECT id, full_name FROM users WHERE role='student' ORDER BY full_name")->fetchAll();

$err = "";
$ok = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $student_id = (int)($_POST['student_id'] ?? 0);
  $book_code = trim($_POST['book_code'] ?? ""); // expects BOOK:<id>
  $due_days = (int)($_POST['due_days'] ?? 7);

  if ($student_id <= 0 || $book_code === "") {
    $err = "Please select student and scan a book QR.";
  } elseif (!preg_match('/^BOOK:\d+$/', $book_code)) {
    $err = "Invalid QR content. Expected BOOK:<id>";
  } else {
    $book_id = (int)str_replace("BOOK:", "", $book_code);

    // check book
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id=? LIMIT 1");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch();

    if (!$book) {
      $err = "Book not found.";
    } elseif ($book['status'] !== 'available') {
      $err = "Book is not available.";
    } else {
      $now = new DateTime("now");
      $due = (clone $now)->modify("+$due_days days");

      $pdo->beginTransaction();
      try {
        $stmt = $pdo->prepare("INSERT INTO borrows(user_id, book_id, borrowed_at, due_at) VALUES(?,?,?,?)");
        $stmt->execute([$student_id, $book_id, $now->format("Y-m-d H:i:s"), $due->format("Y-m-d H:i:s")]);

        $stmt = $pdo->prepare("UPDATE books SET status='borrowed' WHERE id=?");
        $stmt->execute([$book_id]);

        $pdo->commit();
        $ok = "Borrow recorded successfully.";
      } catch (Throwable $t) {
        $pdo->rollBack();
        $err = "Failed to borrow.";
      }
    }
  }
}
?>
<div class="card">
  <h2>Scan Book QR to Borrow</h2>
  <?php if ($err): ?><p style="color:#b00;"><?= htmlspecialchars($err) ?></p><?php endif; ?>
  <?php if ($ok): ?><p style="color:#070;"><?= htmlspecialchars($ok) ?></p><?php endif; ?>

  <div class="row">
    <div class="card">
      <h3>Scanner</h3>
      <div id="reader" style="width:100%;"></div>
      <p><small>Tip: Use your phone to open this page and scan the printed QR.</small></p>
    </div>

    <div class="card">
      <h3>Borrow Form</h3>
      <form method="post">
        <label>Student</label>
        <select name="student_id" required>
          <option value="">Select student</option>
          <?php foreach ($students as $s): ?>
            <option value="<?= (int)$s['id'] ?>"><?= htmlspecialchars($s['full_name']) ?></option>
          <?php endforeach; ?>
        </select>

        <label>Scanned Book Code</label>
        <input id="book_code" name="book_code" placeholder="BOOK:123" required>

        <label>Due Days</label>
        <input name="due_days" type="number" value="7" min="1" max="60">

        <button type="submit">Confirm Borrow</button>
      </form>
    </div>
  </div>
</div>

<!-- QR scanning library via CDN -->
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
  const bookInput = document.getElementById("book_code");

  function onScanSuccess(decodedText) {
    bookInput.value = decodedText;
  }

  const html5QrcodeScanner = new Html5QrcodeScanner(
    "reader",
    { fps: 10, qrbox: 250 },
    false
  );
  html5QrcodeScanner.render(onScanSuccess);
</script>

<?php require __DIR__ . "/../includes/footer.php"; ?>
