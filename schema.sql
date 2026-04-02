-- ============================================================
--  Student Information System — Database Schema
--  Run this file in your MySQL client or phpMyAdmin
-- ============================================================

CREATE DATABASE IF NOT EXISTS student_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE student_db;

CREATE TABLE IF NOT EXISTS students (
    id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    student_id    VARCHAR(20)     NOT NULL UNIQUE COMMENT 'e.g. 2024-0001',
    full_name     VARCHAR(100)    NOT NULL,
    email         VARCHAR(150)    NOT NULL UNIQUE,
    course        VARCHAR(100)    NOT NULL,
    year_level    TINYINT(1)      NOT NULL COMMENT '1 = 1st Year … 4 = 4th Year',
    contact_no    VARCHAR(20)     NOT NULL,
    address       TEXT            NOT NULL,
    gpa           DECIMAL(3,2)    NOT NULL DEFAULT 0.00 COMMENT '1.00 (highest) – 5.00 (lowest)',
    created_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                           ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_course     (course),
    INDEX idx_year_level (year_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Sample data ────────────────────────────────────────────
INSERT INTO students
    (student_id, full_name, email, course, year_level, contact_no, address, gpa)
VALUES
    ('2024-0001', 'Maria Santos',    'maria.santos@school.edu',    'BS Computer Science',      2, '09171234567', '123 Rizal St., Manila',        1.50),
    ('2024-0002', 'Juan dela Cruz',  'juan.delacruz@school.edu',   'BS Information Technology', 1, '09281234567', '456 Mabini Ave., Quezon City', 1.75),
    ('2023-0045', 'Ana Reyes',       'ana.reyes@school.edu',       'BS Computer Engineering',  3, '09391234567', '789 Luna Rd., Makati',         1.25),
    ('2022-0078', 'Carlo Mendoza',   'carlo.mendoza@school.edu',   'BS Computer Science',      4, '09501234567', '321 Bonifacio Blvd., Pasig',   2.00),
    ('2023-0099', 'Sofia Lim',       'sofia.lim@school.edu',       'BS Information Systems',   3, '09611234567', '654 Aguinaldo Hwy., Cavite',   1.00),
    ('2024-0033', 'Miguel Torres',   'miguel.torres@school.edu',   'BS Information Technology', 2, '09721234567', '987 Quezon Ave., QC',          2.25),
    ('2022-0011', 'Isabella Garcia', 'isabella.garcia@school.edu', 'BS Computer Engineering',  4, '09831234567', '135 Taft Ave., Manila',         1.50),
    ('2024-0055', 'Rafael Aquino',   'rafael.aquino@school.edu',   'BS Computer Science',      1, '09941234567', '246 España Blvd., Manila',     1.75);
