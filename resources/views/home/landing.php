<?php
$canRegister = env('APP_ALLOW_REGISTRATION', 'false') === 'true';
?>
<!-- ===== HERO SECTION ===== -->
<section class="sm-hero-section">
  <div class="container">
    <div class="sm-hero-content">
      <span class="sm-hero-badge">✨ StudyMate</span>
      <h1 class="sm-hero-title">StudyMate</h1>
      <p class="sm-hero-subtitle">Student productivity, academic management &amp; teacher monitoring for schools and colleges.</p>
      <div class="sm-hero-actions">
        <a href="<?= e(url('login')) ?>" class="sm-btn sm-btn-primary">Sign in</a>
        <?php if ($canRegister): ?>
          <a href="<?= e(url('register')) ?>" class="sm-btn sm-btn-secondary">Create account</a>
        <?php endif; ?>
      </div>
      <div class="sm-hero-trust">
        <span class="sm-trust-item"><span class="sm-trust-icon" aria-hidden="true">🔒</span> Secure &amp; Private</span>
        <span class="sm-trust-item"><span class="sm-trust-icon" aria-hidden="true">📱</span> No Installation Required</span>
        <span class="sm-trust-item"><span class="sm-trust-icon" aria-hidden="true">⚡</span> Works Offline</span>
      </div>
    </div>
    <div class="sm-hero-visual">
      <div class="sm-hero-card">
        <div class="sm-hero-icon" aria-hidden="true">🎓</div>
        <div class="sm-hero-stat">
          <span class="sm-stat-number">25</span>
          <span class="sm-stat-label">min focus sessions</span>
        </div>
      </div>
      <div class="sm-hero-card">
        <div class="sm-hero-icon" aria-hidden="true">📚</div>
        <div class="sm-hero-stat">
          <span class="sm-stat-number">100%</span>
          <span class="sm-stat-label">student data private</span>
        </div>
      </div>
      <div class="sm-hero-card sm-hero-card-2">
        <div class="sm-hero-icon" aria-hidden="true">📈</div>
        <div class="sm-hero-stat">
          <span class="sm-stat-number">3</span>
          <span class="sm-stat-label">roles, one workspace</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== STATS SECTION ===== -->
<section class="sm-stats-section">
  <div class="container">
    <div class="sm-stats-grid">
      <div class="sm-stat-card">
        <div class="sm-stat-icon" aria-hidden="true">👨‍🎓</div>
        <span class="sm-stat-number">Students</span>
        <span class="sm-stat-value">Manage tasks, notes &amp; sessions</span>
      </div>
      <div class="sm-stat-card">
        <div class="sm-stat-icon" aria-hidden="true">👩‍🏫</div>
        <span class="sm-stat-number">Teachers</span>
        <span class="sm-stat-value">Attendance, grades &amp; monitoring</span>
      </div>
      <div class="sm-stat-card">
        <div class="sm-stat-icon" aria-hidden="true">🏛️</div>
        <span class="sm-stat-number">Admins</span>
        <span class="sm-stat-value">Institution &amp; audit management</span>
      </div>
    </div>
  </div>
</section>

<!-- ===== FEATURES SECTION ===== -->
<section class="sm-features-section">
  <div class="container">
    <div class="sm-section-header">
      <span class="sm-section-badge">Features</span>
      <h2 class="sm-section-title">Everything you need for academic success</h2>
      <p class="sm-section-subtitle">From planning to productivity, we&rsquo;ve got you covered.</p>
    </div>
    <div class="sm-feature-grid">
      <div class="sm-feature-card">
        <div class="sm-feature-icon" aria-hidden="true">✅</div>
        <h3 class="sm-feature-title">Study Planner</h3>
        <p class="sm-feature-text">Daily and weekly schedules with subject-based planning, priorities, and due dates.</p>
      </div>
      <div class="sm-feature-card">
        <div class="sm-feature-icon" aria-hidden="true">⏱️</div>
        <h3 class="sm-feature-title">Focus Timer</h3>
        <p class="sm-feature-text">Pomodoro timer with configurable sessions, breaks, and focus summaries.</p>
      </div>
      <div class="sm-feature-card">
        <div class="sm-feature-icon" aria-hidden="true">📝</div>
        <h3 class="sm-feature-title">Personal Notes</h3>
        <p class="sm-feature-text">Create, edit, search, and organize notes with subject associations.</p>
      </div>
      <div class="sm-feature-card">
        <div class="sm-feature-icon" aria-hidden="true">📊</div>
        <h3 class="sm-feature-title">Analytics</h3>
        <p class="sm-feature-text">Track focus time, task completion, and study streaks with charts.</p>
      </div>
      <div class="sm-feature-card">
        <div class="sm-feature-icon" aria-hidden="true">🎓</div>
        <h3 class="sm-feature-title">Grades &amp; Exams</h3>
        <p class="sm-feature-text">View published grades, teacher feedback, and examination countdowns.</p>
      </div>
      <div class="sm-feature-card">
        <div class="sm-feature-icon" aria-hidden="true">🔴</div>
        <h3 class="sm-feature-title">Live Sessions</h3>
        <p class="sm-feature-text">Student-controlled study session monitoring with transparent status.</p>
      </div>
    </div>
  </div>
</section>

<!-- ===== CTA SECTION ===== -->
<section class="sm-cta-section">
  <div class="container">
    <div class="sm-cta-card">
      <div class="sm-cta-content">
        <h2 class="sm-cta-title">Ready to level up your campus?</h2>
        <p class="sm-cta-text">One workspace for students, teachers and administrators — with privacy controls that keep students in charge of their own study data.</p>
        <div class="sm-cta-actions">
          <a href="<?= e(url('login')) ?>" class="sm-btn sm-btn-primary">Sign in</a>
          <?php if ($canRegister): ?>
            <a href="<?= e(url('register')) ?>" class="sm-btn sm-btn-secondary">Create account</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>
