DROP TABLE IF EXISTS uploaded_files;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS remember_tokens;
DROP TABLE IF EXISTS tutors;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       username VARCHAR(50) NOT NULL UNIQUE,
                       password_hash VARCHAR(255) NOT NULL,
                       full_name VARCHAR(100) NOT NULL,
                       email VARCHAR(100) NOT NULL,
                       role ENUM('student', 'tutor', 'admin') NOT NULL
);

CREATE TABLE tutors (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        bio TEXT,
                        languages VARCHAR(255),
                        levels VARCHAR(255),
                        price_per_hour DECIMAL(10,2),
                        availability TEXT,
                        status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
                        rejection_reason TEXT,
                        profile_picture VARCHAR(255),
                        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE bookings (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          student_id INT NOT NULL,
                          tutor_id INT NOT NULL,
                          language_name VARCHAR(100) NOT NULL,
                          level_name VARCHAR(50) NOT NULL,
                          lesson_date DATE NOT NULL,
                          lesson_time TIME NOT NULL,
                          lesson_type VARCHAR(50) NOT NULL,
                          message TEXT,
                          status ENUM('pending', 'accepted', 'rejected') NOT NULL DEFAULT 'pending',
                          FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
                          FOREIGN KEY (tutor_id) REFERENCES tutors(id) ON DELETE CASCADE
);

CREATE TABLE remember_tokens (
                                 id INT AUTO_INCREMENT PRIMARY KEY,
                                 user_id INT NOT NULL,
                                 token_hash VARCHAR(255) NOT NULL,
                                 expires_at DATETIME NOT NULL,
                                 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE uploaded_files (
                                id INT AUTO_INCREMENT PRIMARY KEY,
                                user_id INT NOT NULL,
                                file_name VARCHAR(255) NOT NULL,
                                file_path VARCHAR(255) NOT NULL,
                                uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO users (username, password_hash, full_name, email, role)
VALUES
    ('student1', '$2y$12$TY2T3HHKWzuG2XJr00JAYemylqOmS.YLVbIVSoHyyOUPQh0LiMqTu', 'Demo Student', 'student@test.com', 'student'),
    ('tutor1', '$2y$12$TY2T3HHKWzuG2XJr00JAYemylqOmS.YLVbIVSoHyyOUPQh0LiMqTu', 'Demo Tutor', 'tutor@test.com', 'tutor'),
    ('admin1', '$2y$12$TY2T3HHKWzuG2XJr00JAYemylqOmS.YLVbIVSoHyyOUPQh0LiMqTu', 'Demo Admin', 'admin@test.com', 'admin'),
    ('tutor2', '$2y$12$TY2T3HHKWzuG2XJr00JAYemylqOmS.YLVbIVSoHyyOUPQh0LiMqTu', 'Pending Tutor', 'pending@test.com', 'tutor');

INSERT INTO tutors (user_id, bio, languages, levels, price_per_hour, availability, status)
VALUES
    (2, 'I am an English tutor with experience in grammar, conversation and exam preparation.', 'English, Spanish', 'A1, A2, B1, B2', 80.00, 'Monday 10:00-14:00, Wednesday 16:00-18:00', 'approved'),
    (4, 'I want to become a tutor on TutorBloom.', 'English', 'A1, A2', 60.00, 'Friday 12:00-15:00', 'pending');