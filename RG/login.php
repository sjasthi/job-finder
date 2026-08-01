<?php
session_start();
require_once __DIR__ . '/config/db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
  header('Location: dashboard.php');
  exit;
}

$error = '';
$old_email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email    = trim($_POST['email']    ?? '');
  $password =      $_POST['password'] ?? '';
  $old_email = $email;

  if (empty($email) || empty($password)) {
    $error = 'Please enter your email and password.';
  } else {
    $stmt = $pdo->prepare("SELECT id, full_name, password_hash FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
      $_SESSION['user_id']   = $user['id'];
      $_SESSION['user_name'] = $user['full_name'];
      header('Location: dashboard.php');
      exit;
    } else {
      $error = 'No account found with those details. Please try again.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>RoleGenie — Log in</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500&family=Inter:wght@400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="stylesheet" href="assets/css/login.css" />
</head>
<body class="login-page">

  <!-- Navbar -->
  <nav id="main-nav" class="navbar navbar-expand-lg">
    <div class="container">
      <a class="nav-logo" href="index.php">
        <div class="logo-mark">
          <svg width="22" height="22" viewBox="40 40 200 190" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <ellipse cx="140" cy="220" rx="88" ry="13" fill="#EEEDFE"/>
            <path d="M88 183 Q69 162 71 143 Q73 124 93 119 Q113 114 123 124 L157 124 Q167 114 187 119 Q207 124 209 143 Q211 162 192 183 Z" fill="#7F77DD"/>
            <path d="M123 124 Q140 109 157 124 L162 178 Q152 188 140 188 Q128 188 118 178 Z" fill="#534AB7"/>
            <ellipse cx="140" cy="122" rx="18" ry="7" fill="#AFA9EC"/>
            <path d="M140 115 Q148 96 152 81 Q156 66 148 56 Q142 47 135 52 Q129 57 134 65 Q138 73 136 80 Q134 87 128 90" stroke="#EF9F27" stroke-width="2.5" stroke-linecap="round" fill="none"/>
            <circle cx="127" cy="91" r="6" fill="#FAC775"/>
            <circle cx="127" cy="91" r="3" fill="#BA7517"/>
            <path d="M192 183 Q211 178 220 172 Q234 165 229 156 Q224 148 215 153 Q206 158 201 153" stroke="#7F77DD" stroke-width="3" stroke-linecap="round" fill="none"/>
            <circle cx="201" cy="151" r="5" fill="#534AB7"/>
            <path d="M68 183 Q51 180 46 174 Q41 167 49 161 Q57 155 65 161" stroke="#7F77DD" stroke-width="3" stroke-linecap="round" fill="none"/>
            <circle cx="65" cy="181" r="4" fill="#534AB7"/>
          </svg>
        </div>
        RoleGenie
      </a>
      <div class="d-flex gap-2 align-items-center ms-auto">
        <span class="login-nav-hint">Don't have an account?</span>
        <a href="register.php" class="btn-nav-signup">Sign up</a>
      </div>
    </div>
  </nav>

  <!-- Login main -->
  <main class="login-main">
    <div class="login-card">

      <!-- Lamp mark -->
      <div class="login-lamp">
        <svg width="64" height="64" viewBox="0 0 280 280" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <ellipse cx="140" cy="220" rx="88" ry="13" fill="#EEEDFE"/>
          <path d="M88 183 Q69 162 71 143 Q73 124 93 119 Q113 114 123 124 L157 124 Q167 114 187 119 Q207 124 209 143 Q211 162 192 183 Z" fill="#7F77DD"/>
          <path d="M123 124 Q140 109 157 124 L162 178 Q152 188 140 188 Q128 188 118 178 Z" fill="#534AB7"/>
          <ellipse cx="140" cy="122" rx="18" ry="7" fill="#AFA9EC"/>
          <path d="M140 115 Q148 96 152 81 Q156 66 148 56 Q142 47 135 52 Q129 57 134 65 Q138 73 136 80 Q134 87 128 90" stroke="#EF9F27" stroke-width="2.5" stroke-linecap="round" fill="none"/>
          <circle cx="127" cy="91" r="6" fill="#FAC775"/>
          <circle cx="127" cy="91" r="3" fill="#BA7517"/>
          <path d="M122 88 Q112 78 118 70" stroke="#FAC775" stroke-width="1.5" stroke-linecap="round" fill="none" opacity="0.7"/>
          <path d="M122 86 Q108 84 110 75" stroke="#FAC775" stroke-width="1.5" stroke-linecap="round" fill="none" opacity="0.5"/>
          <path d="M192 183 Q211 178 220 172 Q234 165 229 156 Q224 148 215 153 Q206 158 201 153" stroke="#7F77DD" stroke-width="3" stroke-linecap="round" fill="none"/>
          <circle cx="201" cy="151" r="5" fill="#534AB7"/>
          <path d="M68 183 Q51 180 46 174 Q41 167 49 161 Q57 155 65 161" stroke="#7F77DD" stroke-width="3" stroke-linecap="round" fill="none"/>
          <circle cx="65" cy="181" r="4" fill="#534AB7"/>
          <circle cx="168" cy="78" r="3" fill="#FAC775" opacity="0.7"/>
          <circle cx="104" cy="63" r="2" fill="#FAC775" opacity="0.5"/>
          <circle cx="183" cy="99" r="2.5" fill="#AFA9EC" opacity="0.6"/>
          <circle cx="97" cy="103" r="2" fill="#AFA9EC" opacity="0.5"/>
        </svg>
      </div>

      <h1 class="login-title">Welcome back</h1>
      <p class="login-sub">Log in to see your job matches.</p>

      <?php if ($error): ?>
        <div class="login-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="login.php" novalidate>

        <div class="lg-field">
          <label for="email">Email address</label>
          <input
            type="email"
            id="email"
            name="email"
            class="lg-input"
            placeholder= "login@email.com"
            value="<?= htmlspecialchars($old_email) ?>"
            autocomplete="email"
          />
        </div>

          <div class="lg-field">
            <label for="password">Password</label>
            <div class="lg-input-wrap">
              <input
                type="password"
                id="password"
                name="password"
                class="lg-input"
                placeholder="••••••••"
                autocomplete="current-password"
              />
              <button type="button" class="lg-toggle-pw" aria-label="Show password" data-target="password">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
            </div>
          </div>

          <button type="submit" class="btn-login-submit">Log in</button>

        </form>

      <div class="login-divider"><span>or</span></div>

      <p class="login-register-hint">
        Don't have an account? <a href="register.php">Create one free</a>
      </p>

    </div>
  </main>

  <!-- Footer -->
  <footer id="footer">
    <div class="container d-flex justify-content-between align-items-center flex-wrap gap-2">
      <span class="footer-logo">RoleGenie</span>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script>
  $(function () {
    $('.lg-toggle-pw').on('click', function () {
      var $input  = $('#' + $(this).data('target'));
      var showing = $input.attr('type') === 'text';
      $input.attr('type', showing ? 'password' : 'text');
      $(this).attr('aria-label', showing ? 'Show password' : 'Hide password');
    });
  });
  </script>

</body>
</html>