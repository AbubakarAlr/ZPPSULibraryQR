<?php
require __DIR__ . "/config/db.php";
require __DIR__ . "/includes/header.php";

$err = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? "");
  $password = $_POST['password'] ?? "";

  $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
  $stmt->execute([$username]);
  $user = $stmt->fetch();

  if (!$user || !password_verify($password, $user['password_hash'])) {
    $err = "Invalid username or password.";
  } else {
    $_SESSION['user'] = [
      'id' => $user['id'],
      'full_name' => $user['full_name'],
      'role' => $user['role']
    ];
    if ($user['role'] === 'librarian') {
      header("Location: /library_qr/admin/dashboard.php");
    } else {
      header("Location: /library_qr/student/dashboard.php");
    }
    exit;
  }
}
?>
<div class="card">
  <h2>Login</h2>
  <?php if ($err): ?><p style="color:#b00;"><?= htmlspecialchars($err) ?></p><?php endif; ?>
  <form method="post">
    <label>Username</label>
    <input name="username" required>
    <label>Password</label>
    <input name="password" type="password" required>
    <button type="submit">Login</button>
  </form>
</div>
<?php require __DIR__ . "/includes/footer.php"; ?>
