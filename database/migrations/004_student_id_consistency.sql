-- StudyMate Schema (consistency fix)
--
-- Every student-facing table now references student_profiles(id) for
-- "student_id", matching enrollments and the way the application reads
-- these columns. Previously the foreign keys pointed at users(id) while the
-- code used student_profiles(id), which silently mixed the two id spaces.

-- 1. study_sessions / study_checkins were written with users(id): re-map them.
UPDATE study_sessions ss
    JOIN student_profiles sp ON sp.user_id = ss.student_id
    SET ss.student_id = sp.id
    WHERE ss.student_id NOT IN (SELECT id FROM student_profiles);

UPDATE study_checkins sc
    JOIN study_sessions ss ON ss.id = sc.study_session_id
    SET sc.student_id = ss.student_id;

-- 2. Point the foreign keys at student_profiles.
ALTER TABLE study_sessions   DROP FOREIGN KEY study_sessions_ibfk_1;
ALTER TABLE study_sessions   ADD CONSTRAINT fk_study_sessions_student   FOREIGN KEY (student_id) REFERENCES student_profiles(id) ON DELETE CASCADE;

ALTER TABLE study_checkins  DROP FOREIGN KEY study_checkins_ibfk_2;
ALTER TABLE study_checkins  ADD CONSTRAINT fk_study_checkins_student   FOREIGN KEY (student_id) REFERENCES student_profiles(id) ON DELETE CASCADE;

ALTER TABLE attendance_records DROP FOREIGN KEY attendance_records_ibfk_2;
ALTER TABLE attendance_records ADD CONSTRAINT fk_attendance_records_student FOREIGN KEY (student_id) REFERENCES student_profiles(id) ON DELETE CASCADE;

ALTER TABLE submissions      DROP FOREIGN KEY submissions_ibfk_2;
ALTER TABLE submissions      ADD CONSTRAINT fk_submissions_student      FOREIGN KEY (student_id) REFERENCES student_profiles(id) ON DELETE CASCADE;

ALTER TABLE grades           DROP FOREIGN KEY grades_ibfk_2;
ALTER TABLE grades           ADD CONSTRAINT fk_grades_student           FOREIGN KEY (student_id) REFERENCES student_profiles(id) ON DELETE CASCADE;
