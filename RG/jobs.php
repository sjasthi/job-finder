<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$resumeName = $_SESSION['resume_name'] ?? null;
$hasResume  = !empty($_SESSION['resume_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>RoleGenie — Job Search</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500&family=Inter:wght@400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css" />

  <style>
    /* Page */
    body { background: #f4f3f9; }

    .jobs-wrap {
      max-width: 820px;
      margin: 0 auto;
      padding: 32px 16px 60px;
    }

    /*Panel card  */
    .rg-panel {
      background: #fff;
      border: 1px solid #e8e8e8;
      border-radius: 14px;
      padding: 24px 28px;
      margin-bottom: 20px;
    }

    .rg-panel h5 {
      font-family: 'Playfair Display', serif;
      font-size: 17px;
      font-weight: 400;
      color: #1a1a1a;
      margin-bottom: 4px;
    }

    .rg-panel .panel-sub {
      font-size: 13px;
      color: #888;
      margin-bottom: 16px;
    }

    /* Resume status bar */
    .resume-status-bar {
      background: #EEEDFE;
      border: 1px solid #AFA9EC;
      border-radius: 8px;
      padding: 10px 16px;
      font-size: 13px;
      color: #3C3489;
      margin-bottom: 14px;
    }

    .resume-status-bar.no-resume {
      background: #fff8e6;
      border-color: #f0c040;
      color: #7a5800;
    }

    /* Upload zone */
    #upload-zone {
      border: 2px dashed #AFA9EC;
      border-radius: 10px;
      padding: 28px;
      text-align: center;
      cursor: pointer;
      transition: border-color 0.2s, background 0.2s;
    }

    #upload-zone:hover,
    #upload-zone.dragover { border-color: #534AB7; background: #f0effd; }
    #upload-zone.has-file { border-color: #27ae60; background: #f0fff4; }

    /* Platform filter buttons */
    .platform-btns {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin-bottom: 14px;
    }

    .platform-btn {
      height: 32px;
      padding: 0 14px;
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 500;
      font-family: 'Inter', sans-serif;
      color: #555;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: border-color 0.15s, background 0.15s, color 0.15s;
    }

    .platform-btn:hover {
      border-color: var(--purple-400);
      color: var(--purple-600);
      background: #f8f7fc;
    }

    .platform-btn.active {
      background: var(--purple-600);
      border-color: var(--purple-600);
      color: #fff;
    }

    .platform-btn.active svg { filter: brightness(10); }

    .platform-btn--clear {
      background: #fff5f5;
      border-color: #f5c6c6;
      color: #c0392b;
    }

    .platform-btn--clear:hover {
      background: #ffe0e0;
      border-color: #e74c3c;
      color: #c0392b;
    }

    .platform-hint {
      font-size: 12px;
      color: var(--purple-600);
      margin-top: 8px;
      margin-bottom: 0;
    }

    /* Search row */
    .search-row {
      display: flex;
      gap: 10px;
    }

    .search-row input {
      flex: 1;
      height: 42px;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 0 14px;
      font-size: 14px;
      font-family: 'Inter', sans-serif;
      outline: none;
      transition: border-color 0.15s;
    }

    .search-row input:focus { border-color: var(--purple-400); }

    .btn-search {
      height: 42px;
      padding: 0 24px;
      background: var(--purple-600);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 500;
      font-family: 'Inter', sans-serif;
      cursor: pointer;
      transition: background 0.15s;
      white-space: nowrap;
    }

    .btn-search:hover { background: var(--purple-800); }

    /* Job card */
    .rg-job-card {
      background: #fff;
      border: 1px solid #e8e8e8;
      border-radius: 12px;
      padding: 20px 22px 16px;
      margin-bottom: 14px;
      transition: box-shadow 0.2s;
    }

    .rg-job-card:hover { box-shadow: 0 4px 16px rgba(83,74,183,0.08); }

    .rg-job-title {
      font-size: 15px;
      font-weight: 500;
      color: #1a1a1a;
      margin-bottom: 3px;
    }

    .rg-job-meta {
      font-size: 12px;
      color: #777;
      margin-bottom: 8px;
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
    }

    .rg-source-badge {
      background: #EEEDFE;
      color: #534AB7;
      padding: 3px 10px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 500;
    }

    .rg-job-desc {
      font-size: 13px;
      color: #666;
      line-height: 1.55;
      margin-bottom: 14px;
    }

    .rg-job-actions { display: flex; gap: 8px; flex-wrap: wrap; }

    .btn-apply {
      height: 34px;
      padding: 0 16px;
      background: var(--purple-600);
      color: #fff;
      border: none;
      border-radius: 7px;
      font-size: 13px;
      font-weight: 500;
      font-family: 'Inter', sans-serif;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      transition: background 0.15s;
    }

    .btn-apply:hover { background: var(--purple-800); color: #fff; }

    .btn-generate-resume {
      height: 34px;
      padding: 0 16px;
      background: #fff;
      color: #27ae60;
      border: 1px solid #27ae60;
      border-radius: 7px;
      font-size: 13px;
      font-weight: 500;
      font-family: 'Inter', sans-serif;
      cursor: pointer;
      transition: background 0.15s, color 0.15s;
    }

    .btn-generate-resume:hover {
      background: #27ae60;
      color: #fff;
    }

    .btn-generate-resume:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }

    .btn-generate-cover {
      height: 34px;
      padding: 0 16px;
      background: #fff;
      color: #2d6cdf;
      border: 1px solid #2d6cdf;
      border-radius: 7px;
      font-size: 13px;
      font-weight: 500;
      font-family: 'Inter', sans-serif;
      cursor: pointer;
      transition: background 0.15s, color 0.15s;
      margin-left: 8px;
    }

    .btn-generate-cover:hover {
      background: #2d6cdf;
      color: #fff;
    }

    /* Resume modal output */
    #resumeOutput {
      white-space: pre-wrap;
      font-family: 'Courier New', monospace;
      font-size: 13px;
      background: #f8f7fc;
      border: 1px solid #e8e8e8;
      border-radius: 8px;
      padding: 16px;
      max-height: 60vh;
      overflow-y: auto;
    }
  </style>
</head>
<body>

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
        <a href="dashboard.php" class="btn-nav-login">Dashboard</a>
        <a href="logout.php" class="btn-nav-login">Log out</a>
      </div>
    </div>
  </nav>

  <div class="jobs-wrap">

    <!-- ── Resume Upload Panel  -->
    <div class="rg-panel">
      <h5>Your Resume</h5>
      <p class="panel-sub">Upload a PDF resume — we'll use it to generate a tailored version for any job below.</p>

      <?php if ($hasResume): ?>
        <div class="resume-status-bar">
          <strong>Active resume:</strong> <?= htmlspecialchars($resumeName) ?>
          &nbsp;&middot;&nbsp;
          <a href="#" id="changeResume" style="color:#534AB7;">Change</a>
        </div>
      <?php else: ?>
        <div class="resume-status-bar no-resume">
          No resume uploaded yet. Upload one to enable AI resume generation.
        </div>
      <?php endif; ?>

      <div id="upload-zone" class="<?= $hasResume ? 'd-none' : '' ?>">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#534AB7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mb-2" aria-hidden="true"><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/><polyline points="7 9 12 4 17 9"/><line x1="12" y1="4" x2="12" y2="16"/></svg>
        <p class="mb-1"><strong>Drop your PDF here</strong> or click to browse</p>
        <p class="text-muted" style="font-size:13px">PDF only · max 5 MB</p>
        <input type="file" id="resumeFile" accept=".pdf" style="display:none" />
      </div>

      <div id="uploadProgress" class="d-none mt-3">
        <div class="progress">
          <div class="progress-bar progress-bar-striped progress-bar-animated" style="width:100%; background:#534AB7;"></div>
        </div>
        <p class="text-muted mt-2" style="font-size:13px;">Uploading &amp; extracting text…</p>
      </div>

      <div id="uploadResult" class="mt-2"></div>
    </div>

    <!-- Job Search Panel -->
    <div class="rg-panel">
      <h5>Search Jobs</h5>
      <p class="panel-sub">Search real job listings from JSearch. Filter by platform or search freely.</p>

      <!-- Platform buttons -->
      <div class="platform-btns">
        <button class="platform-btn" data-platform="LinkedIn">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="#0A66C2" aria-hidden="true"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>
          LinkedIn
        </button>
        <button class="platform-btn" data-platform="Indeed">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="#2164f3" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8" stroke="#fff" stroke-width="2" stroke-linecap="round" fill="none"/></svg>
          Indeed
        </button>
        <button class="platform-btn" data-platform="Glassdoor">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="#0caa41" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M8 12h8M12 8v4" stroke="#fff" stroke-width="2" stroke-linecap="round" fill="none"/></svg>
          Glassdoor
        </button>
        <button class="platform-btn" data-platform="Monster">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="#6d1fd5" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M8 15l4-6 4 6" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
          Monster
        </button>
        <button class="platform-btn platform-btn--clear d-none" id="clearPlatform">✕ Clear filter</button>
      </div>

      <div class="search-row">
        <input
          type="text"
          id="jobQuery"
          placeholder="e.g. software developer in Minneapolis"
          value="software developer in Minneapolis"
        />
        <button id="searchBtn" class="btn-search">Search</button>
      </div>
      <p id="platformHint" class="platform-hint d-none"></p>
    </div>

    <div id="statusMessage"></div>
    <div id="jobResults"></div>

  </div>

  <!-- Generated Resume Modal -->
  <div class="modal fade" id="resumeModal" tabindex="-1" aria-labelledby="resumeModalLabel" aria-hidden="true">
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
            <div id="resumeOutput"></div>
          </div>
          <div id="resumeError" class="d-none alert alert-danger"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

        <!-- Generated Cover Letter Modal -->
        <div class="modal fade" id="coverModal" tabindex="-1" aria-labelledby="coverModalLabel" aria-hidden="true">
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
                  <div id="coverOutput"></div>
                </div>
                <div id="coverError" class="d-none alert alert-danger"></div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script>
  $(function () {

    /* Upload */
    const zone = $('#upload-zone');

    zone.on('click', function () { $('#resumeFile').trigger('click'); });
    zone.on('dragover', function (e) { e.preventDefault(); zone.addClass('dragover'); });
    zone.on('dragleave drop', function () { zone.removeClass('dragover'); });
    zone.on('drop', function (e) {
      e.preventDefault();
      const f = e.originalEvent.dataTransfer.files[0];
      if (f) uploadResume(f);
    });

    $('#resumeFile').on('change', function () {
      if (this.files[0]) uploadResume(this.files[0]);
    });

    $('#changeResume').on('click', function (e) {
      e.preventDefault();
      zone.removeClass('d-none');
    });

    function uploadResume(file) {
      if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
        $('#uploadResult').html('<div class="alert alert-warning mt-2">Only PDF files are supported.</div>');
        return;
      }
      if (file.size > 5 * 1024 * 1024) {
        $('#uploadResult').html('<div class="alert alert-warning mt-2">File must be under 5 MB.</div>');
        return;
      }

      zone.addClass('d-none');
      $('#uploadProgress').removeClass('d-none');
      $('#uploadResult').html('');

      const fd = new FormData();
      fd.append('resume', file);

      $.ajax({
        url: 'api/upload_resume.php',
        method: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        success: function (res) {
          $('#uploadProgress').addClass('d-none');
          if (res.success) {
            $('#uploadResult').html(
              `<div class="alert alert-success">
                <strong>${escHtml(file.name)}</strong> uploaded successfully.
                ${res.text_length > 0
                  ? `Text extracted (${res.text_length} chars).`
                  : '<em>Note: text could not be extracted — AI generation may be limited.</em>'}
              </div>`
            );
            $('.resume-status-bar')
              .removeClass('no-resume')
              .html(`<strong>Active resume:</strong> ${escHtml(file.name)} &nbsp;&middot;&nbsp; <a href="#" id="changeResume" style="color:#534AB7;">Change</a>`);
            $('#changeResume').on('click', function (e) { e.preventDefault(); zone.removeClass('d-none'); });
          } else {
            zone.removeClass('d-none');
            $('#uploadResult').html(`<div class="alert alert-danger">${escHtml(res.error)}</div>`);
          }
        },
        error: function () {
          $('#uploadProgress').addClass('d-none');
          zone.removeClass('d-none');
          $('#uploadResult').html('<div class="alert alert-danger">Upload failed. Check your server connection.</div>');
        }
      });
    }

    // Job platform buttons
    let activePlatform = '';

    $('.platform-btn[data-platform]').on('click', function () {
      const platform = $(this).data('platform');

      if (activePlatform === platform) {
        // Deselect button if pressed again
        activePlatform = '';
        $('.platform-btn[data-platform]').removeClass('active');
        $('#clearPlatform').addClass('d-none');
        $('#platformHint').addClass('d-none').text('');
      } else {
        activePlatform = platform;
        $('.platform-btn[data-platform]').removeClass('active');
        $(this).addClass('active');
        $('#clearPlatform').removeClass('d-none');
        $('#platformHint').removeClass('d-none')
          .text('Searching jobs on ' + platform);
      }
    });

    $('#clearPlatform').on('click', function () {
      activePlatform = '';
      $('.platform-btn[data-platform]').removeClass('active');
      $('#clearPlatform').addClass('d-none');
      $('#platformHint').addClass('d-none').text('');
    });

    /* Job Search func */
    $('#searchBtn').on('click', searchJobs);
    $('#jobQuery').on('keypress', function (e) { if (e.which === 13) searchJobs(); });

    function searchJobs() {
      let query = $('#jobQuery').val().trim();
      if (!query) { alert('Please enter a job search.'); return; }

      if (activePlatform) {
        query = query + ' ' + activePlatform;
      }

      $('#statusMessage').html('<div class="alert alert-info">Searching jobs' + (activePlatform ? ' on ' + activePlatform : '') + '…</div>');
      $('#jobResults').html('');

      $.ajax({
        url: 'api/search_jobs.php',
        method: 'GET',
        data: { query },
        dataType: 'json',
        success: function (res) {
          if (!res.success) {
            $('#statusMessage').html('<div class="alert alert-danger">No jobs found or API error.</div>');
            return;
          }
          $('#statusMessage').html(
            `<div class="alert alert-success">Found ${res.count} jobs for "${escHtml(res.query)}".</div>`
          );
          displayJobs(res.jobs);
        },
        error: function () {
          $('#statusMessage').html('<div class="alert alert-danger">Something went wrong while searching jobs.</div>');
        }
      });
    }

    function displayJobs(jobs) {
      let html = '';
      jobs.forEach(function (job) {
        const desc    = (job.description || 'No description available.').substring(0, 300) + '…';
        const remote  = job.is_remote ? 'Remote' : 'On-site / Hybrid';
        const jobData = escAttr(JSON.stringify(job));

        html += `
          <div class="rg-job-card">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
              <div>
                <p class="rg-job-title">${escHtml(job.title)}</p>
                <div class="rg-job-meta">
                  <span>${escHtml(job.company)}</span>
                  <span>${escHtml(job.location)}</span>
                  <span>${escHtml(job.employment_type || 'N/A')}</span>
                  <span>${remote}</span>
                </div>
              </div>
              <span class="rg-source-badge flex-shrink-0">${escHtml(job.source || 'Unknown')}</span>
            </div>
            <p class="rg-job-desc">${escHtml(desc)}</p>
            <div class="rg-job-actions">
              <a href="${escHtml(job.apply_url)}" target="_blank" class="btn-apply">Apply</a>
              <button class="btn-generate-resume generate-btn" data-job="${jobData}">
                Generate Tailored Resume
              </button>
              <button class="btn-generate-cover generate-cover-btn" data-job="${jobData}">
                Generate Cover Letter
              </button>
            </div>
          </div>`;
      });
      $('#jobResults').html(html);
    }

    /* Generate Resume Modal */
    const resumeModal = new bootstrap.Modal(document.getElementById('resumeModal'));

    $(document).on('click', '.generate-btn', function () {
      const jobData = $(this).data('job');
      const job = typeof jobData === 'string' ? JSON.parse(jobData) : jobData;
      openGenerateModal(job);
    });

    $(document).on('click', '.generate-cover-btn', function () {
      const jobData = $(this).data('job');
      const job = typeof jobData === 'string' ? JSON.parse(jobData) : jobData;
      openCoverModal(job);
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

    /* Cover Letter generation */
    const coverModal = new bootstrap.Modal(document.getElementById('coverModal'));

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

    $('#copyResumeBtn').on('click', function () {
      navigator.clipboard.writeText($('#resumeOutput').text()).then(function () {
        $('#copyResumeBtn').text('Copied!');
        setTimeout(function () { $('#copyResumeBtn').text('Copy to clipboard'); }, 2000);
      });
    });

    function escHtml(s) { return $('<div>').text(String(s ?? '')).html(); }
    function escAttr(s) { return String(s ?? '').replace(/"/g, '&quot;'); }

  });
  </script>

</body>
</html>