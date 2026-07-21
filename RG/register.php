<?php
session_start();
require_once __DIR__ . '/config/db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
  header('Location: dashboard.php');
  exit;
}

$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $old['name']  = trim($_POST['name']  ?? '');
  $old['email'] = trim($_POST['email'] ?? '');

  // Validation
  if (empty($old['name']))             $errors['name']     = 'Please enter your full name.';
  if (empty($old['email']))            $errors['email']    = 'Please enter your email address.';
  elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL))
                                       $errors['email']    = 'Please enter a valid email address.';
  if (empty($_POST['password']))       $errors['password'] = 'Please create a password.';
  elseif (strlen($_POST['password']) < 8)
                                       $errors['password'] = 'Password must be at least 8 characters.';
  elseif ($_POST['password'] !== ($_POST['confirm'] ?? ''))
                                       $errors['confirm']  = 'Passwords do not match.';

  // Check if email is already taken
  if (empty($errors)) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$old['email']]);
    if ($stmt->fetch()) {
      $errors['email'] = 'An account with that email already exists.';
    }
  }

  // All good — insert user
  if (empty($errors)) {
    $hashed = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Insert into users
    $stmt = $pdo->prepare("
      INSERT INTO users (email, password_hash, full_name)
      VALUES (?, ?, ?)
    ");
    $stmt->execute([$old['email'], $hashed, $old['name']]);
    $user_id = $pdo->lastInsertId();

    // Auto-create an empty user_profiles row
    $stmt = $pdo->prepare("INSERT INTO user_profiles (user_id) VALUES (?)");
    $stmt->execute([$user_id]);

    // Log the user in immediately
    $_SESSION['user_id']   = $user_id;
    $_SESSION['user_name'] = $old['name'];

    header('Location: dashboard.php');
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>RoleGenie — Create your account</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500&family=Inter:wght@400;500&display=swap" rel="stylesheet" />

  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="stylesheet" href="assets/css/register.css" />
</head>
<body class="register-page">

  <!-- Navbar ------------------------------------------------- -->
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
        <span class="register-nav-hint">Already have an account?</span>
        <a href="login.php" class="btn-nav-login">Log in</a>
      </div>
    </div>
  </nav>

  <!-- Register Panel ----------------------------------------- -->
  <main class="register-main">
    <div class="container">
      <div class="register-card">

        <!-- Left: brand hero -->
        <div class="register-hero">
          <div class="register-hero-inner">
            <div class="register-lamp">
              <svg width="120" height="120" viewBox="0 0 280 280" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Genie lamp illustration">
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
            <h2 class="register-tagline">Your wish is a<br><em>great job</em></h2>
            <p class="register-hero-sub">RoleGenie's AI agents match you with roles that fit your real skills — not just keywords.</p>

            <ul class="register-perks">
              <li>
                <span class="perk-dot"></span>
                <div>
                  <strong>AI resume analysis</strong>
                  <span>Skills extracted automatically from your PDF</span>
                </div>
              </li>
              <li>
                <span class="perk-dot"></span>
                <div>
                  <strong>Smart job matching</strong>
                  <span>Roles scored against your profile across thousands of listings</span>
                </div>
              </li>
              <li>
                <span class="perk-dot"></span>
                <div>
                  <strong>Tailored cover letters</strong>
                  <span>AI drafts a fresh letter for every application you start</span>
                </div>
              </li>
            </ul>
          </div>
        </div>

        <!-- Right: form -->
        <div class="register-form-panel">
          <h1 class="register-form-title">Create your account</h1>
          <p class="register-form-sub">Join now and get matched to your future career!</p>

          <!-- Social shortcuts -->
          <div class="register-social-row">
            <button type="button" class="btn-social" id="btn-google">
              <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path fill="#EA4335" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/><path fill="#4285F4" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
              Continue with Google
            </button>
            <button type="button" class="btn-social" id="btn-linkedin">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="#0A66C2" aria-hidden="true"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>
              Continue with LinkedIn
            </button>
          </div>

          <div class="register-divider"><span>or</span></div>

          <!-- Registration form -->
          <form method="POST" action="register.php" novalidate>

            <div class="rg-field">
              <label for="name">Full name</label>
              <input
                type="text"
                id="name"
                name="name"
                class="rg-input<?= isset($errors['name']) ? ' rg-input--error' : '' ?>"
                placeholder="Jane Doe"
                value="<?= htmlspecialchars($old['name'] ?? '') ?>"
                autocomplete="name"
              />
              <?php if (isset($errors['name'])): ?>
                <span class="rg-error"><?= $errors['name'] ?></span>
              <?php endif; ?>
            </div>

            <div class="rg-field">
              <label for="email">Email address</label>
              <input
                type="email"
                id="email"
                name="email"
                class="rg-input<?= isset($errors['email']) ? ' rg-input--error' : '' ?>"
                placeholder="login@email.com"
                value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                autocomplete="email"
              />
              <?php if (isset($errors['email'])): ?>
                <span class="rg-error"><?= $errors['email'] ?></span>
              <?php endif; ?>
            </div>

            <div class="rg-field">
              <label for="password">Password</label>
              <div class="rg-input-wrap">
                <input
                  type="password"
                  id="password"
                  name="password"
                  class="rg-input<?= isset($errors['password']) ? ' rg-input--error' : '' ?>"
                  placeholder="Min. 8 characters"
                  autocomplete="new-password"
                />
                <button type="button" class="rg-toggle-pw" aria-label="Show password" data-target="password">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </div>
              <?php if (isset($errors['password'])): ?>
                <span class="rg-error"><?= $errors['password'] ?></span>
              <?php endif; ?>
            </div>

            <div class="rg-field">
              <label for="confirm">Confirm password</label>
              <div class="rg-input-wrap">
                <input
                  type="password"
                  id="confirm"
                  name="confirm"
                  class="rg-input<?= isset($errors['confirm']) ? ' rg-input--error' : '' ?>"
                  placeholder="Repeat your password"
                  autocomplete="new-password"
                />
                <button type="button" class="rg-toggle-pw" aria-label="Show password" data-target="confirm">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </div>
              <?php if (isset($errors['confirm'])): ?>
                <span class="rg-error"><?= $errors['confirm'] ?></span>
              <?php endif; ?>
            </div>

            <?php if (!empty($errors)): ?>
              <div class="rg-alert-error">Please fix the errors above and try again.</div>
            <?php endif; ?>

            <button type="submit" class="btn-register-submit">Create free account</button>

          </form>

          <p class="register-legal">
            By creating an account you agree to the
            <a href="terms.php">terms of service</a> and
            <a href="privacy.php">privacy policy</a>.
          </p>
        </div>

      </div>
    </div>
  </main>

  <!-- Footer ------------------------------------------------- -->
  <footer id="footer">
    <div class="container d-flex justify-content-between align-items-center flex-wrap gap-2">
      <span class="footer-logo">RoleGenie</span>
      <div class="footer-links">
        <a href="#">Privacy</a>
        <a href="#">Terms</a>
        <a href="#">Contact</a>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>

  <script>
  $(function () {
    // Toggle password visibility
    $('.rg-toggle-pw').on('click', function () {
      var targetId = $(this).data('target');
      var $input   = $('#' + targetId);
      var showing  = $input.attr('type') === 'text';
      $input.attr('type', showing ? 'password' : 'text');
      $(this).attr('aria-label', showing ? 'Show password' : 'Hide password');
    });

    // Inline confirm-password match feedback
    $('#confirm').on('input', function () {
      var match = $(this).val() === $('#password').val();
      $(this).toggleClass('rg-input--error', !match && $(this).val().length > 0);
    });

    // Social placeholders
    $('#btn-google').on('click', function () {
      alert('Google OAuth goes here.');
    });
    $('#btn-linkedin').on('click', function () {
      alert('LinkedIn OAuth goes here.');
    });
  });
  </script>

</body>
</html>