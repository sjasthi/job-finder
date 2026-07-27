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

  <style>
  .rg-job-card {
    background: #fff;
    border: 1px solid #e8e8e8;
    border-radius: 12px;
    padding: 18px 20px 14px;
    margin-bottom: 14px;
    transition: box-shadow 0.2s;
  }
  .rg-job-card:hover { box-shadow: 0 4px 16px rgba(83,74,183,0.08); }
  .rg-job-title { font-size: 15px; font-weight: 500; color: #1a1a1a; margin-bottom: 4px; }
  .rg-job-meta { display: flex; flex-wrap: wrap; gap: 10px; font-size: 12px; color: #777; margin-bottom: 8px; }
  .rg-job-desc { font-size: 13px; color: #666; line-height: 1.55; margin-bottom: 12px; }
  .rg-source-badge { background: #EEEDFE; color: #534AB7; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 500; }
  .rg-job-actions { display: flex; gap: 8px; flex-wrap: wrap; border-top: 1px solid #f0f0f0; padding-top: 12px; }
  .btn-apply { height: 34px; padding: 0 16px; background: #534AB7; color: #fff; border: none; border-radius: 7px; font-size: 13px; font-weight: 500; font-family: 'Inter', sans-serif; text-decoration: none; display: inline-flex; align-items: center; transition: background 0.15s; }
  .btn-apply:hover { background: #3C3489; color: #fff; }
  .btn-generate-resume { height: 34px; padding: 0 14px; background: #fff; color: #27ae60; border: 1px solid #27ae60; border-radius: 7px; font-size: 13px; font-weight: 500; font-family: 'Inter', sans-serif; cursor: pointer; transition: background 0.15s; }
  .btn-generate-resume:hover { background: #27ae60; color: #fff; }
  .btn-generate-cover { height: 34px; padding: 0 14px; background: #fff; color: #534AB7; border: 1px solid #534AB7; border-radius: 7px; font-size: 13px; font-weight: 500; font-family: 'Inter', sans-serif; cursor: pointer; transition: background 0.15s; }
  .btn-generate-cover:hover { background: #534AB7; color: #fff; }

  .btn-desc-toggle {
  background: none;
  border: none;
  color: #534AB7;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  padding: 0;
  font-family: 'Inter', sans-serif;
}
.btn-desc-toggle:hover { text-decoration: underline; }
</style>
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
        <?php foreach ($job_listings as $job):
          $jobJson = htmlspecialchars(json_encode([
            'title'           => $job['title'],
            'company'         => $job['company'],
            'location'        => $job['location'],
            'description'     => $job['description'],
            'employment_type' => $job['employment_type'],
            'apply_url'       => $job['url'],
            'source'          => $job['source_platform'],
            'is_remote'       => $job['is_remote'],
          ]), ENT_QUOTES);
          $remote = $job['is_remote'] ? 'Remote' : 'On-site / Hybrid';
        ?>
          <div class="rg-job-card">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
              <div>
                <p class="rg-job-title"><?= htmlspecialchars($job['title']) ?></p>
                <div class="rg-job-meta">
                  <span><?= htmlspecialchars($job['company']) ?></span>
                  <?php if ($job['location']): ?>
                    <span><?= htmlspecialchars($job['location']) ?></span>
                  <?php endif; ?>
                  <?php if ($job['employment_type']): ?>
                    <span><?= htmlspecialchars($job['employment_type']) ?></span>
                  <?php endif; ?>
                  <span><?= $remote ?></span>
                </div>
              </div>
              <?php if ($job['source_platform']): ?>
                <span class="rg-source-badge flex-shrink-0"><?= htmlspecialchars($job['source_platform']) ?></span>
              <?php endif; ?>
            </div>
            <?php if ($job['description']): ?>
              <div class="rg-job-desc-wrap">
                <p class="rg-job-desc">
                  <span class="desc-short"><?= nl2br(htmlspecialchars(substr($job['description'], 0, 300))) ?>… <button class="btn-desc-toggle" data-action="more">Show more</button></span>
                  <span class="desc-full d-none"><?= nl2br(htmlspecialchars($job['description'])) ?> <button class="btn-desc-toggle" data-action="less">Show less</button></span>
                </p>
              </div>
            <?php endif; ?>
            <div class="rg-job-actions">
              <?php if ($job['url']): ?>
                <a href="<?= htmlspecialchars($job['url']) ?>" target="_blank" class="btn-apply">Apply</a>
              <?php endif; ?>
              <button class="btn-generate-resume generate-btn" data-job="<?= $jobJson ?>">
                Generate Tailored Resume
              </button>
              <button class="btn-generate-cover generate-cover-btn" data-job="<?= $jobJson ?>">
                Generate Cover letter
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

  // Generate Resume
  const resumeModal = new bootstrap.Modal(document.getElementById('resumeModal'));

  $(document).on('click', '.generate-btn', function () {
    const jobData = $(this).data('job');
    const job = typeof jobData === 'string' ? JSON.parse(jobData) : jobData;
    openGenerateModal(job);
  });

  $(document).on('click', '.generate-btn', function () {
    const jobData = $(this).data('job');
    const job = typeof jobData === 'string' ? JSON.parse(jobData) : jobData;
    openGenerateModal(job);
  });

  // Show more / show less description
  $(document).on('click', '.btn-desc-toggle', function () {
    const action = $(this).data('action');
    const wrap = $(this).closest('.rg-job-desc-wrap');
    if (action === 'more') {
      wrap.find('.desc-short').addClass('d-none');
      wrap.find('.desc-full').removeClass('d-none');
    } else {
      wrap.find('.desc-full').addClass('d-none');
      wrap.find('.desc-short').removeClass('d-none');
    }
  });

  function openGenerateModal(job) {
    $('#resumeLoading').removeClass('d-none');
    $('#resumeContent').addClass('d-none');
    $('#resumeError').addClass('d-none');
    $('#resumeModalLabel').text('Generating resume for: ' + job.title);
    resumeModal.show();

    $.ajax({
      url: 'api/generate_resume.php',
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({
        title:           job.title,
        company:         job.company,
        location:        job.location,
        description:     job.description,
        employment_type: job.employment_type,
      }),
      dataType: 'json',
      success: function (res) {
        $('#resumeLoading').addClass('d-none');
        if (res.success) {
          $('#resumeJobMeta').text(res.job_title + ' @ ' + res.company);
          $('#resumeOutput').text(res.generated_resume);
          $('#resumeContent').removeClass('d-none');
        } else {
          $('#resumeError').text(res.error).removeClass('d-none');
        }
      },
      error: function () {
        $('#resumeLoading').addClass('d-none');
        $('#resumeError').text('Request failed. Please try again.').removeClass('d-none');
      }
    });
  }

  $('#copyResumeBtn').on('click', function () {
    navigator.clipboard.writeText($('#resumeOutput').text()).then(function () {
      $('#copyResumeBtn').text('Copied!');
      setTimeout(function () { $('#copyResumeBtn').text('Copy to clipboard'); }, 2000);
    });
  });

  // Generate Cover Letter
  const coverModal = new bootstrap.Modal(document.getElementById('coverModal'));

  $(document).on('click', '.generate-cover-btn', function () {
    const jobData = $(this).data('job');
    const job = typeof jobData === 'string' ? JSON.parse(jobData) : jobData;
    openCoverModal(job);
  });

  function openCoverModal(job) {
    $('#coverLoading').removeClass('d-none');
    $('#coverContent').addClass('d-none');
    $('#coverError').addClass('d-none');
    $('#coverModalLabel').text('Generating cover letter for: ' + job.title);
    coverModal.show();

    $.ajax({
      url: 'api/generate_cover_letter.php',
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({
        title:           job.title,
        company:         job.company,
        location:        job.location,
        description:     job.description,
        employment_type: job.employment_type,
      }),
      dataType: 'json',
      success: function (res) {
        $('#coverLoading').addClass('d-none');
        if (res.success) {
          $('#coverJobMeta').text(res.job_title + ' @ ' + res.company);
          $('#coverOutput').text(res.generated_cover_letter);
          $('#coverContent').removeClass('d-none');
        } else {
          $('#coverError').text(res.error).removeClass('d-none');
        }
      },
      error: function () {
        $('#coverLoading').addClass('d-none');
        $('#coverError').text('Request failed. Please try again.').removeClass('d-none');
      }
    });
  }

  $('#copyCoverBtn').on('click', function () {
    navigator.clipboard.writeText($('#coverOutput').text()).then(function () {
      $('#copyCoverBtn').text('Copied!');
      setTimeout(function () { $('#copyCoverBtn').text('Copy to clipboard'); }, 2000);
    });
  });

});
</script>

<!-- Resume Modal -->
<div class="modal fade" id="resumeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="resumeModalLabel" style="font-family:'Playfair Display',serif; font-weight:400;">AI-Generated Resume</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="resumeLoading" class="text-center py-5">
          <div class="spinner-border" style="color:#534AB7;"></div>
          <p class="mt-3 text-muted">Claude is tailoring your resume…</p>
        </div>
        <div id="resumeContent" class="d-none">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div id="resumeJobMeta" class="text-muted" style="font-size:13px;"></div>
            <button class="btn btn-sm btn-outline-secondary" id="copyResumeBtn">Copy to clipboard</button>
          </div>
          <div id="resumeOutput" style="white-space:pre-wrap; font-family:'Courier New',monospace; font-size:13px; background:#f8f7fc; border:1px solid #e8e8e8; border-radius:8px; padding:16px; max-height:60vh; overflow-y:auto;"></div>
        </div>
        <div id="resumeError" class="d-none alert alert-danger"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Cover Letter Modal -->
<div class="modal fade" id="coverModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="coverModalLabel" style="font-family:'Playfair Display',serif; font-weight:400;">AI-Generated Cover Letter</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="coverLoading" class="text-center py-5">
          <div class="spinner-border" style="color:#534AB7;"></div>
          <p class="mt-3 text-muted">Claude is crafting your cover letter…</p>
        </div>
        <div id="coverContent" class="d-none">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div id="coverJobMeta" class="text-muted" style="font-size:13px;"></div>
            <button class="btn btn-sm btn-outline-secondary" id="copyCoverBtn">Copy to clipboard</button>
          </div>
          <div id="coverOutput" style="white-space:pre-wrap; font-family:'Inter',sans-serif; font-size:13px; background:#f8f7fc; border:1px solid #e8e8e8; border-radius:8px; padding:16px; max-height:60vh; overflow-y:auto;"></div>
        </div>
        <div id="coverError" class="d-none alert alert-danger"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

</body>
</html>