<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$user_id   = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';

// Stat: total matched job listings
$stmt = $pdo->prepare("SELECT COUNT(*) FROM job_listings WHERE user_id = ?");
$stmt->execute([$user_id]);
$match_count = $stmt->fetchColumn();

// Stat: total "Apply" clicks
$stmt = $pdo->prepare("SELECT COUNT(*) FROM job_applies WHERE user_id = ?");
$stmt->execute([$user_id]);
$applied_count = $stmt->fetchColumn();

// Recent searches
$stmt = $pdo->prepare("
  SELECT query, results_count, searched_at
  FROM search_history
  WHERE user_id = ?
  ORDER BY searched_at DESC
  LIMIT 15
");
$stmt->execute([$user_id]);
$recent_searches = $stmt->fetchAll();

// Recently applied jobs
$stmt = $pdo->prepare("
  SELECT ja.applied_at, jl.title, jl.company, jl.url
  FROM job_applies ja
  JOIN job_listings jl ON jl.id = ja.listing_id
  WHERE ja.user_id = ?
  ORDER BY ja.applied_at DESC
  LIMIT 10
");
$stmt->execute([$user_id]);
$recent_applies = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>RoleGenie — Your Activity</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500&family=Inter:wght@400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="stylesheet" href="assets/css/dashboard.css" />

  <style>
  .activity-main {
    display: flex;
    flex-direction: column;
    gap: 18px;
    max-width: 700px;
  }

  .activity-stats-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
  }

  .activity-stat-card {
    background: #fff;
    border: 1px solid #e8e8e8;
    border-radius: 12px;
    padding: 18px 20px;
    text-align: center;
  }

  .activity-stat-card .stat-num {
    font-family: 'Playfair Display', serif;
    font-size: 30px;
    font-weight: 500;
    color: var(--purple-600);
    line-height: 1;
    display: block;
    margin-bottom: 6px;
  }

  .activity-stat-card .stat-label {
    font-size: 12px;
    color: #888;
  }

  .activity-card {
    background: #fff;
    border: 1px solid #e8e8e8;
    border-radius: 12px;
    padding: 20px 22px;
  }

  .activity-card h3 {
    font-family: 'Playfair Display', serif;
    font-size: 16px;
    font-weight: 400;
    color: #1a1a1a;
    margin-bottom: 2px;
  }

  .activity-card .panel-sub {
    font-size: 12px;
    color: #888;
    margin-bottom: 14px;
  }

  .activity-list {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .activity-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
  }

  .activity-row:last-child { border-bottom: none; }

  .activity-row-main {
    min-width: 0;
  }

  .activity-row-title {
    font-size: 13px;
    color: #1a1a1a;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .activity-row-sub {
    font-size: 11px;
    color: #999;
    margin-top: 2px;
  }

  .activity-row-time {
    font-size: 11px;
    color: #aaa;
    white-space: nowrap;
    flex-shrink: 0;
  }

  .activity-row-count {
    font-size: 11px;
    font-weight: 500;
    color: var(--purple-600);
    background: var(--purple-50);
    padding: 2px 9px;
    border-radius: 20px;
    flex-shrink: 0;
  }

  .activity-empty {
    font-size: 13px;
    color: #999;
    text-align: center;
    padding: 20px 0;
  }
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
      <a href="account.php" class="dash-avatar" title="Account Settings"><?= strtoupper(substr($user_name, 0, 1)) ?></a>
    </div>
  </nav>

  <!-- Body -->
  <div class="dash-body">

    <!-- Left sidebar -->
    <aside class="dash-sidebar">
      <div class="sidebar-welcome">
        <div class="sidebar-welcome-avatar"><?= strtoupper(substr($user_name, 0, 1)) ?></div>
        <p class="sidebar-welcome-text">Welcome back,</p>
        <p class="sidebar-welcome-name"><?= htmlspecialchars($user_name) ?></p>
      </div>
      <p class="sidebar-label">Navigation</p>
      <nav class="sidebar-nav">
        <a href="dashboard.php" class="sidebar-link">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
          Matched Jobs
        </a>
        <a href="activity.php" class="sidebar-link active">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
          Your Activity
        </a>
        <a href="account.php" class="sidebar-link">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Account Settings
        </a>
      </nav>
    </aside>

    <!-- Center: activity -->
    <main class="activity-main">

      <div class="activity-stats-row">
        <div class="activity-stat-card">
          <span class="stat-num"><?= $match_count ?></span>
          <span class="stat-label">Total Matches</span>
        </div>
        <div class="activity-stat-card">
          <span class="stat-num"><?= $applied_count ?></span>
          <span class="stat-label">Jobs Applied To</span>
        </div>
      </div>

      <!-- Recent searches -->
      <div class="activity-card">
        <h3>Recent Searches</h3>
        <p class="panel-sub">Your most recent job searches.</p>

        <?php if (empty($recent_searches)): ?>
          <p class="activity-empty">No searches yet. Try searching for jobs from the Jobs page.</p>
        <?php else: ?>
          <div class="activity-list">
            <?php foreach ($recent_searches as $search): ?>
              <div class="activity-row">
                <div class="activity-row-main">
                  <div class="activity-row-title"><?= htmlspecialchars($search['query']) ?></div>
                  <div class="activity-row-sub"><?= (int) $search['results_count'] ?> results</div>
                </div>
                <span class="activity-row-time"><?= htmlspecialchars(date('M j, g:i a', strtotime($search['searched_at']))) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Recently applied -->
      <div class="activity-card">
        <h3>Recently Applied</h3>
        <p class="panel-sub">Jobs you clicked "Apply" on.</p>

        <?php if (empty($recent_applies)): ?>
          <p class="activity-empty">No applications yet. Click "Apply" on a job to track it here.</p>
        <?php else: ?>
          <div class="activity-list">
            <?php foreach ($recent_applies as $apply): ?>
              <div class="activity-row">
                <div class="activity-row-main">
                  <div class="activity-row-title"><?= htmlspecialchars($apply['title']) ?></div>
                  <div class="activity-row-sub"><?= htmlspecialchars($apply['company'] ?: 'Unknown company') ?></div>
                </div>
                <span class="activity-row-time"><?= htmlspecialchars(date('M j, g:i a', strtotime($apply['applied_at']))) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

    </main>

  </div><!-- /.dash-body -->

</body>
</html>
