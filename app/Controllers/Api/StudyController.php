<?php
declare(strict_types=1);
namespace App\Controllers\Api;
use App\Core\Controller;

class StudyController extends Controller {
    public function start(): void {
        $this->requireAuth();
        $studentId = $this->studentProfileId();
        $subject = trim($_POST['subject'] ?? '');
        $goal = trim($_POST['goal'] ?? '');
        $plannedDuration = (int) ($_POST['planned_duration_minutes'] ?? 0);
        if ($subject === '') { $this->json(['ok' => false, 'error' => 'Subject required.'], 400); }
        $this->db->update('UPDATE study_sessions SET state = "cancelled", finished_at = NOW() WHERE student_id = ? AND state = "active"', [$studentId]);
        $sessionId = $this->db->insert('INSERT INTO study_sessions (student_id, subject, goal, planned_duration_minutes, state, started_at, last_heartbeat_at, ip_address, user_agent)
            VALUES (?, ?, ?, ?, "active", NOW(), NOW(), ?, ?)',
            [$studentId, $subject, $goal, $plannedDuration, $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', $_SERVER['HTTP_USER_AGENT'] ?? 'unknown']);
        $this->json(['ok' => true, 'session_id' => $sessionId, 'state' => 'active']);
    }
    public function heartbeat(): void {
        $this->requireAuth();
        $studentId = $this->studentProfileId();
        $sessionId = (int) ($_POST['session_id'] ?? 0);
        $session = $this->db->first('SELECT id FROM study_sessions WHERE id = ? AND student_id = ? AND state = "active" LIMIT 1', [$sessionId, $studentId]);
        if (!$session) { $this->json(['ok' => false, 'error' => 'No active session.'], 404); }
        $this->db->update('UPDATE study_sessions SET last_heartbeat_at = NOW() WHERE id = ?', [$sessionId]);
        $this->json(['ok' => true, 'last_heartbeat_at' => date('Y-m-d H:i:s')]);
    }
    public function pause(): void {
        $this->requireAuth();
        $studentId = $this->studentProfileId();
        $sessionId = (int) ($_POST['session_id'] ?? 0);
        $session = $this->db->first('SELECT id FROM study_sessions WHERE id = ? AND student_id = ? AND state = "active" LIMIT 1', [$sessionId, $studentId]);
        if (!$session) { $this->json(['ok' => false, 'error' => 'No active session.'], 404); }
        $this->db->update('UPDATE study_sessions SET state = "paused", paused_at = NOW() WHERE id = ?', [$sessionId]);
        $this->json(['ok' => true, 'state' => 'paused']);
    }
    public function resume(): void {
        $this->requireAuth();
        $studentId = $this->studentProfileId();
        $sessionId = (int) ($_POST['session_id'] ?? 0);
        $session = $this->db->first('SELECT id FROM study_sessions WHERE id = ? AND student_id = ? AND state = "paused" LIMIT 1', [$sessionId, $studentId]);
        if (!$session) { $this->json(['ok' => false, 'error' => 'No paused session.'], 404); }
        $this->db->update('UPDATE study_sessions SET state = "active", resumed_at = NOW() WHERE id = ?', [$sessionId]);
        $this->json(['ok' => true, 'state' => 'active']);
    }
    public function checkin(): void {
        $this->requireAuth();
        $studentId = $this->studentProfileId();
        $sessionId = (int) ($_POST['session_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');
        $session = $this->db->first('SELECT id FROM study_sessions WHERE id = ? AND student_id = ? AND state IN ("active", "paused") LIMIT 1', [$sessionId, $studentId]);
        if (!$session) { $this->json(['ok' => false, 'error' => 'Session not found.'], 404); }
        $this->db->insert('INSERT INTO study_checkins (study_session_id, student_id, message) VALUES (?, ?, ?)', [$sessionId, $studentId, $message ?: null]);
        $this->json(['ok' => true]);
    }
    public function finish(): void {
        $this->requireAuth();
        $studentId = $this->studentProfileId();
        $sessionId = (int) ($_POST['session_id'] ?? 0);
        $session = $this->db->first('SELECT * FROM study_sessions WHERE id = ? AND student_id = ? AND state IN ("active", "paused") LIMIT 1', [$sessionId, $studentId]);
        if (!$session) { $this->json(['ok' => false, 'error' => 'No session.'], 404); }
        $started = new \DateTime($session['started_at']);
        $now = new \DateTime();
        $durationSeconds = (int) $started->diff($now)->format('%h') * 3600 + (int) $started->diff($now)->format('%i') * 60 + (int) $started->diff($now)->format('%s');
        if ($session['paused_at']) {
            $paused = new \DateTime($session['paused_at']);
            $pauseSeconds = (int) $paused->diff($now)->format('%h') * 3600 + (int) $paused->diff($now)->format('%i') * 60 + (int) $paused->diff($now)->format('%s');
            $durationSeconds = max(0, $durationSeconds - $pauseSeconds);
        }
        $this->db->update('UPDATE study_sessions SET state = "completed", finished_at = NOW(), recorded_duration_seconds = ? WHERE id = ?',
            [$durationSeconds, $sessionId]);
        $this->json(['ok' => true, 'recorded_duration_seconds' => $durationSeconds]);
    }
    public function history(): void {
        $this->requireAuth();
        $studentId = $this->studentProfileId();
        $sessions = $this->db->get('SELECT * FROM study_sessions WHERE student_id = ? ORDER BY started_at DESC LIMIT 100', [$studentId]);
        foreach ($sessions as &$s) {
            $s['duration_minutes'] = $s['recorded_duration_seconds'] ? round($s['recorded_duration_seconds'] / 60, 1) : 0;
        }
        $this->json(['ok' => true, 'sessions' => $sessions]);
    }
}
