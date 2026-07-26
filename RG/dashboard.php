<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$user_id    = $_SESSION['user_id'];
$user_name  = $_SESSION['user_name'] ?? 'User';
$has_resume = !empty($_SESSION['resume_id']);

// Pull latest 10 matched jobs for this user
$stmt = $pdo->prepare("
  SELECT * FROM job_listings
  WHERE user_id = ?
  ORDER BY fetched_at DESC
  LIMIT 10
");
$stmt->execute([$user_id]);
$job_listings = $stmt->fetchAll();

// Activity stats
$total_matches = $pdo->prepare("SELECT COUNT(*) FROM job_listings WHERE user_id = ?");
$total_matches->execute([$user_id]);
$match_count = $total_matches->fetchColumn();

$total_applied = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE user_id = ?");
$total_applied->execute([$user_id]);
$applied_count = $total_applied->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>RoleGenie — Dashboard</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500&family=Inter:wght@400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="stylesheet" href="assets/css/dashboard.css" />
</head>
<body class="dashboard-page">

  <!-- ── Top Navbar ─────────────────────────────────────────── -->
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
      <a href="#" class="dash-tab active" data-tab="matched">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
        Matched
      </a>
    </div>

    <div class="dash-nav-right">
      <a href="logout.php" class="dash-logout">Log out</a>
      <div class="dash-avatar"><?= strtoupper(substr($user_name, 0, 1)) ?></div>
    </div>

  </nav>

  <!-- ── Body ───────────────────────────────────────────────── -->
  <div class="dash-body">

    <!-- Left sidebar -->
    <aside class="dash-sidebar">
      <p class="sidebar-label">Navigation</p>
      <nav class="sidebar-nav">
        <a href="dashboard.php" class="sidebar-link active">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
          Matched Jobs
        </a>
      </nav>
    </aside>

    <!-- Center: job cards -->
    <main class="dash-main">

      <!-- Search bar -->
      <div class="dash-search-row">
        <div class="dash-search-field">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" placeholder="Job title, keywords..." />
        </div>
        <div class="dash-search-field">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          <input type="text" placeholder="All locations" />
        </div>
        <a href="jobs.php" class="btn-find-jobs">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          Find Jobs
        </a>
      </div>

      <?php if (empty($job_listings)): ?>
        <!-- Empty state -->
        <div class="dash-empty">
          <svg width="48" height="48" viewBox="0 0 280 280" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M88 183 Q69 162 71 143 Q73 124 93 119 Q113 114 123 124 L157 124 Q167 114 187 119 Q207 124 209 143 Q211 162 192 183 Z" fill="#AFA9EC"/>
            <path d="M123 124 Q140 109 157 124 L162 178 Q152 188 140 188 Q128 188 118 178 Z" fill="#7F77DD"/>
            <ellipse cx="140" cy="122" rx="18" ry="7" fill="#EEEDFE"/>
            <path d="M140 115 Q148 96 152 81 Q156 66 148 56 Q142 47 135 52 Q129 57 134 65 Q138 73 136 80 Q134 87 128 90" stroke="#EF9F27" stroke-width="2.5" stroke-linecap="round" fill="none"/>
            <circle cx="127" cy="91" r="6" fill="#FAC775"/>
            <circle cx="127" cy="91" r="3" fill="#BA7517"/>
          </svg>
          <h3>No matched jobs yet</h3>
          <p>Upload your resume and search for jobs to see your matches here.</p>
          <a href="jobs.php" class="btn-find-jobs">Find Jobs</a>
        </div>
      <?php else: ?>
        <?php foreach ($job_listings as $job): ?>
          <div class="job-card">
            <div class="job-card-top">
              <div class="job-info">
                <h3 class="job-title"><?= htmlspecialchars($job['title']) ?></h3>
                <p class="job-dept"><?= htmlspecialchars($job['company']) ?></p>
                <div class="job-meta">
                  <?php if ($job['location']): ?>
                    <span>
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                      <?= htmlspecialchars($job['location']) ?>
                    </span>
                  <?php endif; ?>
                  <span>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    <?= $job['is_remote'] ? 'Remote' : 'On-site' ?>
                  </span>
                  <?php if ($job['employment_type']): ?>
                    <span><?= htmlspecialchars($job['employment_type']) ?></span>
                  <?php endif; ?>
                </div>
                <div class="job-tags">
                  <?php if ($job['source_platform']): ?>
                    <span class="tag tag-blue"><?= htmlspecialchars($job['source_platform']) ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <div class="job-card-actions">
              <button class="btn-ai-apply" data-id="<?= $job['id'] ?>">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96-.46 2.5 2.5 0 0 1-2.96-3.08 3 3 0 0 1-.34-5.58 2.5 2.5 0 0 1 1.32-4.88A2.5 2.5 0 0 1 9.5 2"/><path d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96-.46 2.5 2.5 0 0 0 2.96-3.08 3 3 0 0 0 .34-5.58 2.5 2.5 0 0 0-1.32-4.88A2.5 2.5 0 0 0 14.5 2"/></svg>
                Have AI agent apply
              </button>
              <?php if ($job['url']): ?>
                <a href="<?= htmlspecialchars($job['url']) ?>" target="_blank" class="btn-calibrate">View Job</a>
              <?php endif; ?>
              <button class="btn-save" aria-label="Save job">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

    </main>

    <!-- Right sidebar -->
    <aside class="dash-right">

      <!-- Genie Tips -->
      <div class="right-widget">
        <h4 class="widget-title">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#EF9F27" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          Genie Tips
        </h4>
        <div class="tip">
          <p class="tip-title">Calibrate before applying</p>
          <p class="tip-body">Let the AI agent tailor your resume to each job's keywords!</p>
        </div>
        <div class="tip">
          <p class="tip-title">Review before send</p>
          <p class="tip-body">Approve the cover letter draft!</p>
        </div>
      </div>

      <!-- Your Activity -->
      <div class="right-widget">
        <h4 class="widget-title">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#534AB7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
          Your Activity
        </h4>
        <div class="activity-grid">
          <div class="activity-stat">
            <span class="activity-num"><?= $match_count ?></span>
            <span class="activity-label">Matches</span>
          </div>
          <div class="activity-stat">
            <span class="activity-num"><?= $applied_count ?></span>
            <span class="activity-label">AI Applied</span>
          </div>
        </div>
      </div>

    </aside>

  </div><!-- /.dash-body -->

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script>
  $(function () {
    // Tab switching
    $('.dash-tab').on('click', function (e) {
      e.preventDefault();
      $('.dash-tab').removeClass('active');
      $(this).addClass('active');
    });

    // Sidebar link switching
    $('.sidebar-link').on('click', function (e) {
      e.preventDefault();
      $('.sidebar-link').removeClass('active');
      $(this).addClass('active');
    });

    // Save toggle
    $('.btn-save').on('click', function () {
      $(this).toggleClass('saved');
    });

    // AI apply placeholder
    $('.btn-ai-apply').on('click', function () {
      var title = $(this).closest('.job-card').find('.job-title').text();
      alert('AI agent queued to apply for:\n' + title + '\n\n(Wire up to applications table.)');
    });

    // Calibrate placeholder
    $('.btn-calibrate').on('click', function () {
      var title = $(this).closest('.job-card').find('.job-title').text();
      alert('Resume calibration for:\n' + title + '\n\n(Wire up to Claude API.)');
    });
  });
  </script>

</body>
</html>