<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT id, email, full_name, password_hash FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
  session_destroy();
  header('Location: login.php');
  exit;
}

$profile_old      = ['name' => $user['full_name'], 'email' => $user['email']];
$profile_errors   = [];
$password_errors  = [];
$delete_errors    = [];
$success          = $_GET['success'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $form_action = $_POST['form_action'] ?? '';

  // ---- Update name / email ----------------------------------
  if ($form_action === 'update_profile') {
    $profile_old['name']  = trim($_POST['name']  ?? '');
    $profile_old['email'] = trim($_POST['email'] ?? '');

    if (empty($profile_old['name']))  $profile_errors['name']  = 'Please enter your full name.';
    if (empty($profile_old['email'])) $profile_errors['email'] = 'Please enter your email address.';
    elseif (!filter_var($profile_old['email'], FILTER_VALIDATE_EMAIL))
                                       $profile_errors['email'] = 'Please enter a valid email address.';

    if (empty($profile_errors)) {
      $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
      $stmt->execute([$profile_old['email'], $user_id]);
      if ($stmt->fetch()) {
        $profile_errors['email'] = 'That email is already in use by another account.';
      }
    }

    if (empty($profile_errors)) {
      $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
      $stmt->execute([$profile_old['name'], $profile_old['email'], $user_id]);
      $_SESSION['user_name'] = $profile_old['name'];
      header('Location: account.php?success=profile');
      exit;
    }
  }

  // ---- Change password ----------------------------------------
  if ($form_action === 'update_password') {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password']     ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($current) || !password_verify($current, $user['password_hash'])) {
      $password_errors['current_password'] = 'Current password is incorrect.';
    }
    if (empty($new) || strlen($new) < 8) {
      $password_errors['new_password'] = 'New password must be at least 8 characters.';
    } elseif ($new !== $confirm) {
      $password_errors['confirm_password'] = 'Passwords do not match.';
    }

    if (empty($password_errors)) {
      $hashed = password_hash($new, PASSWORD_BCRYPT);
      $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
      $stmt->execute([$hashed, $user_id]);
      header('Location: account.php?success=password');
      exit;
    }
  }

  // ---- Delete account -------------------------------------------
  if ($form_action === 'delete_account') {
    $delete_password = $_POST['delete_password'] ?? '';

    if (empty($delete_password) || !password_verify($delete_password, $user['password_hash'])) {
      $delete_errors['delete_password'] = 'Password is incorrect.';
    }

    if (empty($delete_errors)) {
      // Cascades to user_profiles, resumes, job_listings, applications
      $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
      $stmt->execute([$user_id]);
      session_destroy();
      header('Location: login.php?deleted=1');
      exit;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>RoleGenie — Account Settings</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500&family=Inter:wght@400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="stylesheet" href="assets/css/dashboard.css" />
  <link rel="stylesheet" href="assets/css/register.css" />

  <style>
  .account-main {
    display: flex;
    flex-direction: column;
    gap: 18px;
    max-width: 620px;
  }

  .account-card {
    background: #fff;
    border: 1px solid #e8e8e8;
    border-radius: 12px;
    padding: 22px 24px;
  }

  .account-card h3 {
    font-family: 'Playfair Display', serif;
    font-size: 16px;
    font-weight: 400;
    color: #1a1a1a;
    margin-bottom: 2px;
  }

  .account-card .panel-sub {
    font-size: 12px;
    color: #888;
    margin-bottom: 16px;
  }

  .account-card .rg-field { margin-bottom: 12px; }

  .btn-account-submit {
    height: 38px;
    padding: 0 18px;
    background: var(--purple-600);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    font-family: 'Inter', sans-serif;
    cursor: pointer;
    transition: background 0.15s;
  }

  .btn-account-submit:hover { background: var(--purple-800); }

  .rg-alert-success {
    background: #eafaf1;
    border: 1px solid #b7ebc9;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 13px;
    color: #1a7a46;
    margin-bottom: 14px;
  }

  .account-card--danger {
    border-color: #f5c6c6;
  }

  .account-card--danger h3 { color: #c0392b; }

  .btn-account-danger {
    height: 38px;
    padding: 0 18px;
    background: #fff;
    color: #c0392b;
    border: 1px solid #e24b4a;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    font-family: 'Inter', sans-serif;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
  }

  .btn-account-danger:hover { background: #e24b4a; color: #fff; }
  </style>
</head>
<body class="dashboard-page">

  <!-- Top Navbar -->
  <nav class="dash-nav">
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

    <div class="dash-tabs">
      <a href="dashboard.php" class="dash-tab">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
        Matched
      </a>
    </div>

    <div class="dash-nav-right">
      <a href="logout.php" class="dash-logout">Log out</a>
      <a href="account.php" class="dash-avatar" title="Account Settings"><?= strtoupper(substr($user['full_name'] ?: 'U', 0, 1)) ?></a>
    </div>
  </nav>

  <!-- Body -->
  <div class="dash-body">

    <!-- Left sidebar -->
    <aside class="dash-sidebar">
      <div class="sidebar-welcome">
        <div class="sidebar-welcome-avatar"><?= strtoupper(substr($user['full_name'] ?: 'U', 0, 1)) ?></div>
        <p class="sidebar-welcome-text">Welcome back,</p>
        <p class="sidebar-welcome-name"><?= htmlspecialchars($user['full_name']) ?></p>
      </div>
      <p class="sidebar-label">Navigation</p>
      <nav class="sidebar-nav">
        <a href="dashboard.php" class="sidebar-link">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
          Matched Jobs
        </a>
        <a href="activity.php" class="sidebar-link">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
          Your Activity
        </a>
        <a href="account.php" class="sidebar-link active">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Account Settings
        </a>
      </nav>
    </aside>

    <!-- Center: account forms -->
    <main class="account-main">

      <?php if ($success === 'profile'): ?>
        <div class="rg-alert-success">Your profile has been updated.</div>
      <?php elseif ($success === 'password'): ?>
        <div class="rg-alert-success">Your password has been changed.</div>
      <?php endif; ?>

      <!-- Profile info -->
      <div class="account-card">
        <h3>Profile Information</h3>
        <p class="panel-sub">Update your name and email address.</p>

        <form method="POST" action="account.php" novalidate>
          <input type="hidden" name="form_action" value="update_profile" />

          <div class="rg-field">
            <label for="name">Full name</label>
            <input
              type="text"
              id="name"
              name="name"
              class="rg-input<?= isset($profile_errors['name']) ? ' rg-input--error' : '' ?>"
              value="<?= htmlspecialchars($profile_old['name']) ?>"
              autocomplete="name"
            />
            <?php if (isset($profile_errors['name'])): ?>
              <span class="rg-error"><?= $profile_errors['name'] ?></span>
            <?php endif; ?>
          </div>

          <div class="rg-field">
            <label for="email">Email address</label>
            <input
              type="email"
              id="email"
              name="email"
              class="rg-input<?= isset($profile_errors['email']) ? ' rg-input--error' : '' ?>"
              value="<?= htmlspecialchars($profile_old['email']) ?>"
              autocomplete="email"
            />
            <?php if (isset($profile_errors['email'])): ?>
              <span class="rg-error"><?= $profile_errors['email'] ?></span>
            <?php endif; ?>
          </div>

          <button type="submit" class="btn-account-submit">Save changes</button>
        </form>
      </div>

      <!-- Change password -->
      <div class="account-card">
        <h3>Change Password</h3>
        <p class="panel-sub">Choose a strong password you don't use elsewhere.</p>

        <form method="POST" action="account.php" novalidate>
          <input type="hidden" name="form_action" value="update_password" />

          <div class="rg-field">
            <label for="current_password">Current password</label>
            <input
              type="password"
              id="current_password"
              name="current_password"
              class="rg-input<?= isset($password_errors['current_password']) ? ' rg-input--error' : '' ?>"
              autocomplete="current-password"
            />
            <?php if (isset($password_errors['current_password'])): ?>
              <span class="rg-error"><?= $password_errors['current_password'] ?></span>
            <?php endif; ?>
          </div>

          <div class="rg-field">
            <label for="new_password">New password</label>
            <input
              type="password"
              id="new_password"
              name="new_password"
              class="rg-input<?= isset($password_errors['new_password']) ? ' rg-input--error' : '' ?>"
              placeholder="Min. 8 characters"
              autocomplete="new-password"
            />
            <?php if (isset($password_errors['new_password'])): ?>
              <span class="rg-error"><?= $password_errors['new_password'] ?></span>
            <?php endif; ?>
          </div>

          <div class="rg-field">
            <label for="confirm_password">Confirm new password</label>
            <input
              type="password"
              id="confirm_password"
              name="confirm_password"
              class="rg-input<?= isset($password_errors['confirm_password']) ? ' rg-input--error' : '' ?>"
              autocomplete="new-password"
            />
            <?php if (isset($password_errors['confirm_password'])): ?>
              <span class="rg-error"><?= $password_errors['confirm_password'] ?></span>
            <?php endif; ?>
          </div>

          <button type="submit" class="btn-account-submit">Update password</button>
        </form>
      </div>

      <!-- Danger zone -->
      <div class="account-card account-card--danger">
        <h3>Delete Account</h3>
        <p class="panel-sub">This permanently deletes your account, resumes, job matches, and generated documents. This cannot be undone.</p>
        <button type="button" class="btn-account-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
          Delete my account
        </button>
      </div>

    </main>

  </div><!-- /.dash-body -->

  <!-- Delete Account Modal -->
  <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" style="font-family:'Playfair Display',serif; font-weight:400;">Delete your account?</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST" action="account.php">
          <input type="hidden" name="form_action" value="delete_account" />
          <div class="modal-body">
            <p style="font-size:13px; color:#555;">
              This is permanent. Enter your password to confirm.
            </p>
            <div class="rg-field">
              <label for="delete_password">Password</label>
              <input
                type="password"
                id="delete_password"
                name="delete_password"
                class="rg-input<?= isset($delete_errors['delete_password']) ? ' rg-input--error' : '' ?>"
                autocomplete="current-password"
              />
              <?php if (isset($delete_errors['delete_password'])): ?>
                <span class="rg-error"><?= $delete_errors['delete_password'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn-account-danger">Delete permanently</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <?php if (!empty($delete_errors)): ?>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      new bootstrap.Modal(document.getElementById('deleteAccountModal')).show();
    });
  </script>
  <?php endif; ?>

</body>
</html>
