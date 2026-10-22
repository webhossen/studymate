-- StudyMate Schema (additions)
-- Attachment metadata for assignments and submissions.
-- These columns are written by the upload controllers but were absent from
-- the original schema, which made any file upload fail with a SQL error.

ALTER TABLE assignments
    ADD COLUMN file_path VARCHAR(500) NULL AFTER max_marks,
    ADD COLUMN file_original_name VARCHAR(255) NULL AFTER file_path,
    ADD COLUMN file_mime VARCHAR(120) NULL AFTER file_original_name,
    ADD COLUMN file_size BIGINT UNSIGNED NULL AFTER file_mime;

ALTER TABLE submissions
    ADD COLUMN file_original_name VARCHAR(255) NULL AFTER file_path,
    ADD COLUMN file_mime VARCHAR(120) NULL AFTER file_original_name,
    ADD COLUMN file_size BIGINT UNSIGNED NULL AFTER file_mime,
    ADD COLUMN submission_attempt INT UNSIGNED NOT NULL DEFAULT 1 AFTER submitted_at;
