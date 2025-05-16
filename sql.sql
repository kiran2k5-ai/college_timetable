-- Create database
CREATE DATABASE college_timetable;
USE college_timetable;

-- Users (admin/teacher login)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    role ENUM('admin', 'teacher'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Teachers
CREATE TABLE teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    max_load INT DEFAULT 18 -- max periods per week
);

-- Subjects
CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    year INT,
    is_lab BOOLEAN DEFAULT FALSE
);

-- Mapping table: which teacher teaches which subject
CREATE TABLE teacher_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT,
    subject_id INT,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id)
);

-- Sections (e.g., 1A, 1B, ..., 4C)
CREATE TABLE sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year INT,
    section CHAR(1)
);

-- Timetable storage (final schedule)
CREATE TABLE timetable (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year INT,
    section CHAR(1),
    day VARCHAR(10),
    period INT,
    subject_id INT,
    teacher_id INT,
    type ENUM('theory', 'lab'),
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (teacher_id) REFERENCES teachers(id)
);
