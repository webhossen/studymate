<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title">Live Study Sessions</h1>
    <p class="sm-page-subtitle">Sessions your students have started. They control when they begin and stop.</p>
  </div>
</div>

<div class="sm-card">
  <div class="sm-card-header">Session Monitor</div>
  <div class="sm-card-body">
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label" for="class_subject_id">Class / Subject</label>
        <select name="class_subject_id" id="class_subject_id" class="form-select">
          <option value="">All</option>
          <?php foreach ($classSubjects ?? [] as $cs): ?>
            <option value="<?= e($cs['id']) ?>"><?= e($cs['class_name'] . ' — ' . $cs['subject_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label" for="date_from">Date</label>
        <input type="date" name="date_from" id="date_from" class="form-control" value="<?= date('Y-m-d') ?>">
      </div>
      <div class="col-md-4 d-flex align-items-end">
        <button class="btn btn-sm-navy" id="smLoadSessions" type="button">Load sessions</button>
      </div>
    </div>

    <div class="table-wrap mt-3">
      <table class="table table-hover align-middle mb-0" id="liveSessionsTable">
        <thead>
          <tr>
            <th>Student</th>
            <th>Subject</th>
            <th>Goal</th>
            <th>Status</th>
            <th>Last Update</th>
          </tr>
        </thead>
        <tbody id="liveSessionsBody">
          <tr><td colspan="5" class="text-center text-muted py-4">Choose a filter and select <strong>Load sessions</strong>.</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var button = document.getElementById('smLoadSessions');
  var tbody = document.getElementById('liveSessionsBody');
  var csrf = (document.querySelector('meta[name=csrf-token]') || {}).content || '';
  var endpoint = '<?= e(base_path('api/teacher/live-sessions')) ?>';

  function notify(message, type) {
    if (window.StudyMate && window.StudyMate.toast) window.StudyMate.toast(message, { type: type || 'info' });
  }

  function loadSessions(silent) {
    var cs = document.getElementById('class_subject_id').value;
    var df = document.getElementById('date_from').value;
    button.disabled = true;

    fetch(endpoint + '?class_subject_id=' + encodeURIComponent(cs) + '&date_from=' + encodeURIComponent(df) + '&date_to=' + encodeURIComponent(df),
      { headers: { 'X-CSRF-Token': csrf } })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        button.disabled = false;
        tbody.innerHTML = '';

        if (!data.ok) {
          tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-4">Could not load sessions.</td></tr>';
          if (!silent) notify(data.error || 'Could not load sessions.', 'error');
          return;
        }

        if (!data.sessions || !data.sessions.length) {
          tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No sessions found for this filter.</td></tr>';
          if (!silent) notify('No sessions found for this filter.', 'info');
          return;
        }

        data.sessions.forEach(function (s) {
          var stale = s.stale;
          var tr = document.createElement('tr');
          [
            (s.first_name || '') + ' ' + (s.last_name || ''),
            s.subject_name || '—',
            s.goal || '—'
          ].forEach(function (text) {
            var td = document.createElement('td');
            td.textContent = text;
            tr.appendChild(td);
          });

          var status = document.createElement('td');
          var badge = document.createElement('span');
          badge.className = 'fw-bold ' + (stale ? 'text-danger' : 'text-success');
          badge.textContent = stale ? 'No recent update' : (s.display_status || '—');
          status.appendChild(badge);
          tr.appendChild(status);

          var updated = document.createElement('td');
          updated.className = 'text-muted small';
          updated.textContent = s.last_heartbeat_at || s.started_at || '—';
          tr.appendChild(updated);

          tbody.appendChild(tr);
        });

        if (!silent) notify(data.sessions.length + ' session' + (data.sessions.length === 1 ? '' : 's') + ' loaded.', 'success');
      })
      .catch(function () {
        button.disabled = false;
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-4">Network error while loading sessions.</td></tr>';
        if (!silent) notify('Network error while loading sessions.', 'error');
      });
  }

  button.addEventListener('click', function () { loadSessions(false); });

  /* Refresh silently so the teacher is not interrupted by toasts. */
  window.setInterval(function () { loadSessions(true); }, 60000);
});
</script>
