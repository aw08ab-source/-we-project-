CREATE DATABASE IF NOT EXISTS university_system;
USE university_system;

CREATE TABLE users (
    id VARCHAR(20) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('admin', 'student', 'teacher') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE courses (
    code VARCHAR(10) PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    instructor VARCHAR(100) NOT NULL,
    credits INT NOT NULL,
    seats INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE student_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL,
    course_code VARCHAR(10) NOT NULL,
    grade VARCHAR(3) NOT NULL,
    semester VARCHAR(20),
    year INT,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_code) REFERENCES courses(code) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (student_id, course_code)
);

CREATE TABLE course_enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL,
    course_code VARCHAR(10) NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_code) REFERENCES courses(code) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (student_id, course_code)
);

-- Insert users
INSERT INTO users (name, password, id, email, role) VALUES
('admin', 'admin123', 'x000000', 'unitrack@admin.com', 'admin'),
('Almustafa Alamri', 'password123', 's151920', 's151920@student.squ.edu.om', 'student'),
('Awab Alshukairi', 'mypassword', 's142364', 's142364@student.squ.edu.om', 'student'),
('Ahmed Soleimani', 'teachpass', 'i121212', 'a.soleimani@squ.edu.om', 'teacher');

-- Insert courses
INSERT INTO courses (code, title, instructor, credits, seats) VALUES
('COMP3700', 'Web Development', 'Ahmed Soleimani', 3, 30),
('MATH1010', 'Calculus I', 'Dr. Sebti Kerbal', 4, 25),
('COMP3501', 'Computer organization and Assembly language', 'Dr. Amjad Altobi', 3, 20),
('COMP2202', 'Introduction to object oriented programming', 'Dr. Donald Trump', 3, 35);

-- Insert student records (grades)
INSERT INTO student_records (student_id, course_code, grade) VALUES
('s151920', 'COMP3700', 'A'),
('s151920', 'MATH1010', 'B+'),
('s151920', 'COMP2202', 'A-'),
('s142364', 'COMP3700', 'A'),
('s142364', 'MATH1010', 'B+'),
('s142364', 'COMP3501', 'C-');