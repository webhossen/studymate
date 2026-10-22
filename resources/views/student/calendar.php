<link rel="stylesheet" href="<?= asset('css/fullcalendar.min.css') ?>">
<script src="<?= asset('js/fullcalendar.min.js') ?>"></script>

<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title">Academic Calendar</h1>
    <p class="sm-page-subtitle">Upcoming examinations and assignment deadlines for your enrolled class.</p>
  </div>
</div>

<div class="sm-card">
  <div class="sm-card-header">Schedule</div>
  <div class="sm-card-body">
    <div class="d-flex flex-wrap gap-3 mb-3 small">
      <span class="d-inline-flex align-items-center gap-2"><span style="width:.7rem;height:.7rem;border-radius:50%;background:#ef4444;display:inline-block;"></span> Examination</span>
      <span class="d-inline-flex align-items-center gap-2"><span style="width:.7rem;height:.7rem;border-radius:50%;background:#6366f1;display:inline-block;"></span> Assignment deadline</span>
    </div>
    <div id="calendar"></div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var calendarEl = document.getElementById('calendar');
  if (!calendarEl || typeof FullCalendar === 'undefined') return;
  var events = <?= json_encode($events ?? []) ?>;
  var calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    height: 'auto',
    dayMaxEvents: 3,
    headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listMonth' },
    events: events.map(function (e) {
      return {
        title: e.title + ' (' + (e.type === 'exam' ? 'Exam' : 'Assignment') + ')',
        start: e.start,
        allDay: true,
        backgroundColor: e.type === 'exam' ? '#ef4444' : '#6366f1',
        borderColor: e.type === 'exam' ? '#ef4444' : '#6366f1'
      };
    })
  });
  calendar.render();
});
</script>
