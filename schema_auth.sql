-- ============================================================
--  schema_auth.sql — Users Table for EduTrack Login System
--  Run this AFTER schema.sql, using the same student_db
-- ============================================================

USE student_db;

CREATE TABLE IF NOT EXISTS users (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    username    VARCHAR(50)     NOT NULL UNIQUE,
    email       VARCHAR(150)    NOT NULL UNIQUE,
    password    VARCHAR(255)    NOT NULL COMMENT 'bcrypt hash via password_hash()',
    full_name   VARCHAR(100)    NOT NULL,
    role        ENUM('admin','staff') NOT NULL DEFAULT 'staff',
    is_active   TINYINT(1)      NOT NULL DEFAULT 1,
    last_login  TIMESTAMP       NULL DEFAULT NULL,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── NOTE ─────────────────────────────────────────────────────
--  Do NOT insert users directly here with plain-text passwords.
--  Instead, visit setup.php ONCE in your browser to create the
--  default admin account securely. Delete setup.php afterward.
-- ─────────────────────────────────────────────────────────────
