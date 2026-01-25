<?php
declare(strict_types=1);

require_once __DIR__ . "/config/db.php";       // MUST be first
require_once __DIR__ . "/includes/header.php"; // header output after db is okay

// Hard check: if $pdo is missing, stop with clear message
if (!isset($pdo)) {
  die("DB connection not loaded. Check config/db.php path and contents.");
}

$err = "";
$ok = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $full_name = trim($_POST['full_name'] ?? "");
  $username  = trim($_POST['username'] ?? "");
  $password  = $_POST['password'] ?? "";
  $role      = $_POST['role'] ?? "student";

  if ($full_name === "" || $username === "" || $password === "") {
    $err = "Please fill in all fields.";
  } elseif (!in_array($role, ['student','librarian'], true)) {
    $err = "Invalid role.";
  } else {
    try {
      // Check if username already exists (cleaner than catching insert error)
      $check = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
      $check->execute([$username]);
      if ($check->fetch()) {
        $err = "Username already exists.";
      } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users(full_name, username, password_hash, role) VALUES(?,?,?,?)");
        $stmt->execute([$full_name, $username, $hash, $role]);

        // Redirect to login after successful register
        header("Location: /library_qr/login.php");
        exit;
      }
    } catch (Throwable $e) {
      $err = "Registration failed. " . $e->getMessage();
    }
  }
}
?>

<div class="card">
  <h2>Register</h2>

  <?php if ($err): ?>
    <div class="alert error"><?= htmlspecialchars($err) ?></div>
  <?php endif; ?>

  <form method="post" action="/library_qr/register.php">
    <label>Full Name</label>
    <input name="full_name" required>

    <label>Username</label>
    <input name="username" required>

    <label>Password</label>
    <input name="password" type="password" required>

    <label>Role</label>
    <select name="role">
      <option value="student">Student</option>
      <option value="librarian">Librarian</option>
    </select>

    <button type="submit">Create Account</button>
  </form>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
