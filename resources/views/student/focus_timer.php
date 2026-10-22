<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title">Focus Timer</h1>
    <p class="sm-page-subtitle">Run a 25-minute pomodoro session and track your focus time by subject.</p>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="sm-card h-100">
      <div class="sm-card-header">Focus Timer (Pomodoro)</div>
      <div class="sm-card-body text-center">
        <p class="text-muted small mb-4">25-minute focus sessions. Track your concentration time per subject.</p>

        <div class="timer-ring" id="timerDisplay">
          <span id="timerMinutes">25</span>:<span id="timerSeconds">00</span>
        </div>

        <div class="d-flex justify-content-center gap-2 mb-4 flex-wrap">
          <button class="btn btn-sm-navy px-4" id="startBtn">Start</button>
          <button class="btn btn-sm btn-outline-secondary px-4" id="pauseBtn" disabled>Pause</button>
          <button class="btn btn-sm btn-outline-danger px-4" id="resetBtn">Reset</button>
        </div>

        <div class="row g-3 justify-content-center">
          <div class="col-md-5 col-sm-6">
            <label class="form-label small" for="timerSubject">Subject</label>
            <select class="form-select form-select-sm" id="timerSubject">
              <option value="">General</option>
              <?php foreach ($subjects ?? [] as $s): ?>
                <option value="<?= e($s['name']) ?>"><?= e($s['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-5 col-sm-6">
            <label class="form-label small" for="timerGoal">Goal (optional)</label>
            <input type="text" class="form-control form-control-sm" id="timerGoal" placeholder="Study goal">
          </div>
        </div>

        <div class="mt-4">
          <button class="btn btn-sm btn-outline-navy w-100" id="sessionBtn">Start Study Session</button>
          <p class="text-muted small mt-3 mb-0" id="timerStatus" role="status" aria-live="polite"></p>
        </div>      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="sm-card mb-4">
      <div class="sm-card-header">Your Focus</div>
      <div class="sm-card-body">
        <div class="row g-3 text-center">
          <div class="col-4">
            <div class="h4 fw-bold mb-0 text-navy"><?= e($stats['total_sessions'] ?? 0) ?></div>
            <div class="text-muted small">Sessions</div>
          </div>
          <div class="col-4">
            <div class="h4 fw-bold mb-0 text-navy"><?= e(round(($stats['total_seconds'] ?? 0) / 60)) ?></div>
            <div class="text-muted small">Minutes</div>
          </div>
          <div class="col-4">
            <div class="h4 fw-bold mb-0 text-navy"><?= e($stats['active_days'] ?? 0) ?></div>
            <div class="text-muted small">Active days</div>
          </div>
        </div>
      </div>
    </div>

    <div class="sm-card">
      <div class="sm-card-header">How it works</div>
      <div class="sm-card-body">
        <ul class="list-unstyled mb-0 small text-muted">
          <li class="mb-2">▶ Start the timer when you begin studying.</li>
          <li class="mb-2">⏸ Pause whenever you need a short break.</li>
          <li class="mb-2">✅ Register the session so it appears in your study history.</li>
          <li>🔒 Teachers only ever see the start time, subject and goal — never your screen.</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  var TOTAL = 25 * 60;
  var timeLeft = TOTAL;
  var timerInterval = null;

  var display   = document.getElementById('timerDisplay');
  var minutesEl = document.getElementById('timerMinutes');
  var secondsEl = document.getElementById('timerSeconds');
  var startBtn  = document.getElementById('startBtn');
  var pauseBtn  = document.getElementById('pauseBtn');
  var resetBtn  = document.getElementById('resetBtn');
  var sessionBtn= document.getElementById('sessionBtn');
  var statusEl  = document.getElementById('timerStatus');

  function setStatus(message, tone) {
    if (!statusEl) return;
    statusEl.textContent = message || '';
    statusEl.className = 'text-muted small mt-3 mb-0';
    if (tone === 'success') statusEl.className = 'text-success small mt-3 mb-0';
    if (tone === 'danger')  statusEl.className = 'text-danger small mt-3 mb-0';
    if (window.StudyMate && window.StudyMate.toast) {
      window.StudyMate.toast(message, { type: tone === 'danger' ? 'warning' : (tone || 'info') });
    }
  }

  function paint() {
    var m = Math.floor(timeLeft / 60);
    var s = timeLeft % 60;
    if (minutesEl) minutesEl.textContent = String(m).padStart(2, '0');
    if (secondsEl) secondsEl.textContent = String(s).padStart(2, '0');
    if (display) {
      var pct = (timeLeft / TOTAL) * 360;
      display.style.background = 'conic-gradient(var(--sm-primary) ' + pct + 'deg, rgba(148,163,184,.18) ' + pct + 'deg)';
    }
  }

  function stop() {
    if (timerInterval) { clearInterval(timerInterval); timerInterval = null; }
  }

  startBtn?.addEventListener('click', function () {
    if (timerInterval) return;
    this.disabled = true;
    pauseBtn.disabled = false;
    display?.classList.add('active');
    setStatus('Focus session in progress — you have got this.', 'success');
    timerInterval = setInterval(function () {
      timeLeft--;
      paint();
      if (timeLeft <= 0) {
        stop();
        display?.classList.remove('active');
        startBtn.disabled = false;
        pauseBtn.disabled = true;
        setStatus('Focus session complete. Take a well-earned break.', 'success');
      }
    }, 1000);
  });

  pauseBtn?.addEventListener('click', function () {
    stop();
    startBtn.disabled = false;
    this.disabled = true;
    display?.classList.remove('active');
    setStatus('Timer paused.', 'danger');
  });

  resetBtn?.addEventListener('click', function () {
    stop();
    timeLeft = TOTAL;
    paint();
    startBtn.disabled = false;
    pauseBtn.disabled = true;
    display?.classList.remove('active');
    setStatus('Timer reset.', 'danger');
  });

  sessionBtn?.addEventListener('click', function () {
    var subject = document.getElementById('timerSubject').value || 'General';
    var goal = document.getElementById('timerGoal').value;
    var token = document.querySelector('meta[name=csrf-token]')?.content || '';
    sessionBtn.disabled = true;

    fetch('<?= e(base_path('api/study/start')) ?>', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': token },
      body: new URLSearchParams({ subject: subject, goal: goal, planned_duration_minutes: 25 })
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        sessionBtn.disabled = false;
        setStatus(data.ok ? 'Study session started and tracked in your history.' : (data.error || 'Could not start the session.'), data.ok ? 'success' : 'danger');
      })
      .catch(function () {
        sessionBtn.disabled = false;
        setStatus('Network error — the session could not be started.', 'danger');
      });
  });

  paint();
})();
</script>
