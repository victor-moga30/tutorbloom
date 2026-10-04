CREATE DATABASE IF NOT EXISTS tutorbloom
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE tutorbloom;

DROP TABLE IF EXISTS uploaded_files;
DROP TABLE IF EXISTS remember_tokens;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS availability;
DROP TABLE IF EXISTS tutor_subjects;
DROP TABLE IF EXISTS subjects;
DROP TABLE IF EXISTS tutor_profiles;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       username VARCHAR(50) NOT NULL UNIQUE,
                       password_hash VARCHAR(255) NOT NULL,
                       full_name VARCHAR(100) NOT NULL,
                       email VARCHAR(120) NOT NULL UNIQUE,
                       role ENUM('student', 'tutor', 'admin') NOT NULL,
                       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE tutor_profiles (
                                id INT AUTO_INCREMENT PRIMARY KEY,
                                user_id INT NOT NULL UNIQUE,
                                description TEXT,
                                experience_years INT DEFAULT 0,
                                price_per_hour DECIMAL(10,2) DEFAULT 0,
                                lesson_type ENUM('individual', 'grup', 'ambele') DEFAULT 'individual',
                                location_type ENUM('online', 'fizic', 'ambele') DEFAULT 'online',
                                profile_photo VARCHAR(255),
                                status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE subjects (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          name VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE tutor_subjects (
                                tutor_profile_id INT NOT NULL,
                                subject_id INT NOT NULL,
                                level ENUM('incepator', 'mediu', 'avansat') NOT NULL,
                                PRIMARY KEY (tutor_profile_id, subject_id, level),
                                FOREIGN KEY (tutor_profile_id) REFERENCES tutor_profiles(id) ON DELETE CASCADE,
                                FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE availability (
                              id INT AUTO_INCREMENT PRIMARY KEY,
                              tutor_profile_id INT NOT NULL,
                              available_date DATE NOT NULL,
                              start_time TIME NOT NULL,
                              end_time TIME NOT NULL,
                              is_booked TINYINT(1) DEFAULT 0,
                              FOREIGN KEY (tutor_profile_id) REFERENCES tutor_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE bookings (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          student_id INT NOT NULL,
                          tutor_profile_id INT NOT NULL,
                          availability_id INT NOT NULL,
                          subject_id INT NOT NULL,
                          message TEXT,
                          status ENUM('pending', 'accepted', 'rejected', 'cancelled') DEFAULT 'pending',
                          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                          FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
                          FOREIGN KEY (tutor_profile_id) REFERENCES tutor_profiles(id) ON DELETE CASCADE,
                          FOREIGN KEY (availability_id) REFERENCES availability(id) ON DELETE CASCADE,
                          FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE remember_tokens (
                                 id INT AUTO_INCREMENT PRIMARY KEY,
                                 user_id INT NOT NULL,
                                 selector VARCHAR(64) NOT NULL UNIQUE,
                                 token_hash VARCHAR(255) NOT NULL,
                                 expires_at DATETIME NOT NULL,
                                 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE uploaded_files (
                                id INT AUTO_INCREMENT PRIMARY KEY,
                                user_id INT NOT NULL,
                                file_name VARCHAR(255) NOT NULL,
                                file_path VARCHAR(255) NOT NULL,
                                file_type VARCHAR(100),
                                uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO users (username, password_hash, full_name, email, role) VALUES
                                                                        ('student1', '$2y$12$3KAJufiz44hdQ0i1SZeILO6zGf41MrJovRnhR/PfzUjHCLhOGLHrS', 'Maria Student', 'student1@test.ro', 'student'),
                                                                        ('tutor1',   '$2y$12$3KAJufiz44hdQ0i1SZeILO6zGf41MrJovRnhR/PfzUjHCLhOGLHrS', 'Andrei Tutor', 'tutor1@test.ro', 'tutor'),
                                                                        ('tutor2',   '$2y$12$3KAJufiz44hdQ0i1SZeILO6zGf41MrJovRnhR/PfzUjHCLhOGLHrS', 'Ioana Tutor', 'tutor2@test.ro', 'tutor'),
                                                                        ('admin1',   '$2y$12$3KAJufiz44hdQ0i1SZeILO6zGf41MrJovRnhR/PfzUjHCLhOGLHrS', 'Admin TutorBloom', 'admin@test.ro', 'admin');

INSERT INTO subjects (name) VALUES
                                ('engleza'),
                                ('spaniola'),
                                ('franceza'),
                                ('germana'),
                                ('matematica'),
                                ('informatica');

INSERT INTO tutor_profiles
(user_id, description, experience_years, price_per_hour, lesson_type, location_type, profile_photo, status)
VALUES
    (2, 'Tutor prietenos pentru conversatie, gramatica si pregatire examene.', 5, 100.00, 'individual', 'online', 'assets/images/tutor1.jpg', 'approved'),
    (3, 'Lectii interactive pentru incepatori si nivel mediu.', 3, 85.00, 'ambele', 'ambele', 'assets/images/tutor2.jpg', 'pending');

INSERT INTO tutor_subjects (tutor_profile_id, subject_id, level) VALUES
                                                                     (1, 1, 'incepator'),
                                                                     (1, 1, 'mediu'),
                                                                     (1, 1, 'avansat'),
                                                                     (1, 6, 'mediu'),
                                                                     (2, 2, 'incepator'),
                                                                     (2, 2, 'mediu'),
                                                                     (2, 3, 'incepator');

INSERT INTO availability (tutor_profile_id, available_date, start_time, end_time, is_booked) VALUES
                                                                                                 (1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '18:00:00', '19:00:00', 0),
                                                                                                 (1, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '17:00:00', '18:00:00', 0),
                                                                                                 (1, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '19:00:00', '20:00:00', 0),
                                                                                                 (2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '16:00:00', '17:00:00', 0);