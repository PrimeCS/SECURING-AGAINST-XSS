<?php
require 'config/db.php';
require 'config/layout.php';
require 'config/security.php';
if (is_logged_in()) { header('Location: dashboard.php'); exit; }

$error = '';

// values to refill the form with (everything except password/confirm,
// which are cleared on any error so the user retypes them)
$first_name = '';
$last_name  = '';
$address    = '';
$age        = '';
$email      = '';
$username   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // sanitize everything the user typed (but NEVER the password)
  $first_name = sanitize_input($_POST['first_name']);
  $last_name  = sanitize_input($_POST['last_name']);
  $address    = sanitize_input($_POST['address']);
  $age        = (int)$_POST['age'];
  $email      = sanitize_input($_POST['email']);
  $username   = sanitize_input($_POST['username']);

  // validate the password
  $pw_error = password_error($_POST['password']);
  if ($_POST['password'] !== $_POST['confirm']) {
    $error = 'Passwords do not match.';
  } elseif ($pw_error !== '') {
    $error = $pw_error;
  } else {
    $check = $pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
    $check->execute([$username, $email]);
    if ($check->fetch()) {
      $error = 'Username or email is already taken.';
      $username = ''; // only the username is cleared for this error
    } else {
      $pdo->beginTransaction();
      // everyone who registers here is a customer
      $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, address, age, email, username, password, role) VALUES (?, ?, ?, ?, ?, ?, ?, 'customer')");
      $stmt->execute([
        $first_name, $last_name, $address, $age,
        $email, $username, password_hash($_POST['password'], PASSWORD_DEFAULT)
      ]);
      $uid = $pdo->lastInsertId();

      // a client record is created for this customer so their tickets can be linked to it
      $stmt = $pdo->prepare("INSERT INTO clients (full_name, email, added_by, updated_by, last_updated, user_id) VALUES (?, ?, ?, ?, NOW(), ?)");
      $stmt->execute([$first_name . ' ' . $last_name, $email, $uid, $uid, $uid]);
      $pdo->commit();

      header('Location: login.php?registered=1');
      exit;
    }
  }
}
head('Register');
if ($error) { echo "<p class='error'>" . e($error) . "</p>"; }
?>
<form method="post">
  <label>First name</label><br><input name="first_name" value="<?= e($first_name) ?>" required><br>
  <label>Last name</label><br><input name="last_name" value="<?= e($last_name) ?>" required><br>
  <label>Address</label><br><input name="address" value="<?= e($address) ?>"><br>
  <label>Age</label><br><input name="age" type="number" min="1" max="120" value="<?= e($age) ?>"><br>
  <label>Email</label><br><input name="email" type="email" value="<?= e($email) ?>" required><br>
  <label>Username</label><br><input name="username" value="<?= e($username) ?>" required><br>
  <label>Password</label><br><input id="password" name="password" type="password" required><br>
  <small>At least 8 characters, with an uppercase letter, a lowercase letter, and a number.</small><br><br>
  <label>Confirm password</label><br><input id="confirm" name="confirm" type="password" required><br>
  <label><input type="checkbox" onclick="togglePw()" style="width:auto"> Show password</label><br><br>
  <button>Register</button>
</form>
<p>Already have an account? <a href="login.php">Login</a></p>
<script>
function togglePw() {
  var type = document.getElementById('password').type === 'password' ? 'text' : 'password';
  document.getElementById('password').type = type;
  document.getElementById('confirm').type = type;
}
</script>
<?php foot();