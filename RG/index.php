<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>RoleGenie — AI Agents that find your perfect role</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500&family=Inter:wght@400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>

  <!-- -- Navbar -------------------------------------------- -->
  <nav id="main-nav" class="navbar navbar-expand-lg">
    <div class="container">

      <a class="nav-logo" href="#">
        <div class="logo-mark">
          <svg width="22" height="22" viewBox="40 40 200 190" fill="none" xmlns="http://www.w3.org/2000/svg">
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

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav mx-auto">
          <li class="nav-item"><a class="nav-link" href="#">About us</a></li>
          <li class="nav-item"><a class="nav-link" href="#">AI info</a></li>
          <li class="nav-item"><a class="nav-link" href="#">How it works</a></li>
          <li class="nav-item"><a class="nav-link" href="#">Why use this?</a></li>
        </ul>
        <div class="d-flex gap-2 align-items-center">
          <button class="btn-nav-login" id="btn-login">Log in</button>
          <button class="btn-nav-signup" id="btn-signup">Sign up</button>
        </div>
      </div>

    </div>
  </nav>

  <!-- -- Hero ---------------------------------------------- -->
  <section id="hero">
    <div class="container">
      <div class="row align-items-center g-0">

        <div class="col-lg-6 pe-lg-5 mb-5 mb-lg-0">
          <p class="hero-eyebrow">AI-powered job search</p>
          <h1 class="hero-headline">
            Your wish for the<br>
            <em>perfect role</em><br>
            — granted.
          </h1>
          <p class="hero-subtext">
            Upload your Resume or connect LinkedIn. Our AI agents scan thousands of jobs, score them against your profile, and surface only what truly fits.
          </p>
          <div class="d-flex flex-wrap gap-3">
            <button class="btn-cta-primary" id="btn-upload">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/><polyline points="7 9 12 4 17 9"/><line x1="12" y1="4" x2="12" y2="16"/></svg>
              Upload Resume
            </button>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="hero-illustration">
            <svg width="280" height="280" viewBox="0 0 280 280" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Genie lamp illustration">
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
        </div>

      </div>
    </div>
  </section>

  <!-- -- How It Works ---------------------------------------- -->
  <section id="how-it-works">
    <div class="container text-center">

      <p class="section-eyebrow">How it works</p>
      <h2 class="section-title">Your AI role searching partner</h2>
      <p class="section-sub">Four steps from profile to perfect match.</p>

      <div class="row g-3">
        <div class="col-sm-6 col-lg-3">
          <div class="step-card text-start">
            <div class="step-number">01</div>
            <div class="step-icon-wrap"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#534AB7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg></div>
            <p class="step-title">Connect your profile</p>
            <p class="step-desc">Upload a PDF Resume — takes under a minute.</p>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="step-card text-start">
            <div class="step-number">02</div>
            <div class="step-icon-wrap"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#534AB7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96-.46 2.5 2.5 0 0 1-2.96-3.08 3 3 0 0 1-.34-5.58 2.5 2.5 0 0 1 1.32-4.88A2.5 2.5 0 0 1 9.5 2"/><path d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96-.46 2.5 2.5 0 0 0 2.96-3.08 3 3 0 0 0 .34-5.58 2.5 2.5 0 0 0-1.32-4.88A2.5 2.5 0 0 0 14.5 2"/></svg></div>
            <p class="step-title">AI agents analyze you</p>
            <p class="step-desc">Skills, experience, and preferences are extracted automatically.</p>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="step-card text-start">
            <div class="step-number">03</div>
            <div class="step-icon-wrap"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#534AB7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><path d="M11 8a3 3 0 0 0-3 3"/></svg></div>
            <p class="step-title">Search thousands of roles</p>
            <p class="step-desc">Major job sites are scanned across the web for matching openings.</p>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="step-card text-start">
            <div class="step-number">04</div>
            <div class="step-icon-wrap"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#534AB7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg></div>
            <p class="step-title">Get Job matches</p>
            <p class="step-desc">Pick through the various options — decide what truly fits!</p>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- -- Footer ---------------------------------------------- -->
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

  <!-- Bootstrap 5 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- jQuery -->
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script src="assets/js/app.js"></script>

</body>
</html>
