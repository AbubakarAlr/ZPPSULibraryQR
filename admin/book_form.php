<?php
require __DIR__ . "/../config/db.php";
require __DIR__ . "/../includes/auth.php";
require_role('librarian');
require __DIR__ . "/../includes/header.php";

function ensure_qr_for_book(PDO $pdo, int $bookId): string {
  // QR payload
  $payload = "BOOK:" . $bookId;

  // Create qr folder if missing
  $qrDir = __DIR__ . "/../qr";
  if (!is_dir($qrDir)) {
    mkdir($qrDir, 0777, true);
  }

  $filename = "book_" . $bookId . ".png";
  $filePath = $qrDir . "/" . $filename;
  $publicPath = "/library_qr/qr/" . $filename;

  // Generate QR via Google Chart (prototype-friendly)
  // If no internet allowed, tell me and I’ll give offline QR library version.
  $url = "https://chart.googleapis.com/chart?cht=qr&chs=250x250&chl=" . urlencode($payload);

  // Download and save png
  $img = @file_get_contents($url);
  if ($img === false) {
    // fallback: no QR generated
    return "";
  }
  file_put_contents($filePath, $img);

  // Update db
  $stmt = $pdo->prepare("UPDATE books SET qr_path=? WHERE id=?");
  $stmt->execute([$publicPath, $bookId]);

  return $publicPath;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$book = null;

if ($id) {
  $stmt = $pdo->prepare("SELECT * FROM books WHERE id=?");
  $stmt->execute([$id]);
  $book = $stmt->fetch();
  if (!$book) { exit("Book not found."); }
}

$err = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $accession_no = trim($_POST['accession_no'] ?? "");
  $title = trim($_POST['title'] ?? "");
  $author = trim($_POST['author'] ?? "");
  $isbn = trim($_POST['isbn'] ?? "");
  $year = $_POST['year_published'] !== "" ? (int)$_POST['year_published'] : null;

  if ($accession_no === "" || $title === "") {
    $err = "Accession No and Title are required.";
  } else {
    try {
      if ($id) {
        $stmt = $pdo->prepare("UPDATE books SET accession_no=?, title=?, author=?, isbn=?, year_published=? WHERE id=?");
        $stmt->execute([$accession_no, $title, $author, $isbn, $year, $id]);
        // ensure QR exists
        $qr = ensure_qr_for_book($pdo, $id);
      } else {
        $stmt = $pdo->prepare("INSERT INTO books(accession_no, title, author, isbn, year_published) VALUES(?,?,?,?,?)");
        $stmt->execute([$accession_no, $title, $author, $isbn, $year]);
        $newId = (int)$pdo->lastInsertId();
        $qr = ensure_qr_for_book($pdo, $newId);
      }

      header("Location: /library_qr/admin/books.php");
      exit;
    } catch (PDOException $e) {
      $err = "Accession No must be unique or fields invalid.";
    }
  }
}
?>
<div class="card">
  <h2><?= $id ? "Edit Book" : "Add Book" ?></h2>
  <?php if ($err): ?><p style="color:#b00;"><?= htmlspecialchars($err) ?></p><?php endif; ?>

  <form method="post">
    <div class="row">
      <div>
        <label>Accession No</label>
        <input name="accession_no" value="<?= htmlspecialchars($book['accession_no'] ?? '') ?>" required>
      </div>
      <div>
        <label>Year Published</label>
        <input name="year_published" type="number" value="<?= htmlspecialchars((string)($book['year_published'] ?? '')) ?>">
      </div>
    </div>

    <label>Title</label>
    <input name="title" value="<?= htmlspecialchars($book['title'] ?? '') ?>" required>

    <div class="row">
      <div>
        <label>Author</label>
        <input name="author" value="<?= htmlspecialchars($book['author'] ?? '') ?>">
      </div>
      <div>
        <label>ISBN</label>
        <input name="isbn" value="<?= htmlspecialchars($book['isbn'] ?? '') ?>">
      </div>
    </div>

    <button type="submit"><?= $id ? "Save Changes" : "Add Book" ?></button>
  </form>

  <?php if (!empty($book['qr_path'])): ?>
    <p>QR: <a href="<?= htmlspecialchars($book['qr_path']) ?>" target="_blank">View</a></p>
  <?php endif; ?>
</div>
<?php require __DIR__ . "/../includes/footer.php"; ?>
