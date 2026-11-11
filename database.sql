CREATE DATABASE IF NOT EXISTS student_attendance_qr CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE student_attendance_qr;

CREATE TABLE IF NOT EXISTS student_groups (
 id INT AUTO_INCREMENT PRIMARY KEY,
 group_name VARCHAR(100) NOT NULL UNIQUE,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
 id INT AUTO_INCREMENT PRIMARY KEY,
 full_name VARCHAR(150) NOT NULL,
 username VARCHAR(80) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 role ENUM('admin','teacher','student') NOT NULL,
 student_code VARCHAR(50) NULL UNIQUE,
 group_id INT NULL,
 is_active TINYINT(1) NOT NULL DEFAULT 1,
 last_login DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_users_group FOREIGN KEY (group_id) REFERENCES student_groups(id) ON DELETE SET NULL,
 INDEX idx_users_role_group(role,group_id), INDEX idx_users_name(full_name)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS class_sessions (
 id INT AUTO_INCREMENT PRIMARY KEY,
 teacher_id INT NOT NULL,
 group_id INT NOT NULL,
 lesson_title VARCHAR(160) NOT NULL,
 status ENUM('active','closed') NOT NULL DEFAULT 'active',
 started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 closed_at DATETIME NULL,
 CONSTRAINT fk_session_teacher FOREIGN KEY (teacher_id) REFERENCES users(id),
 CONSTRAINT fk_session_group FOREIGN KEY (group_id) REFERENCES student_groups(id),
 INDEX idx_session_status(status,started_at), INDEX idx_session_teacher(teacher_id,started_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS qr_tokens (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 class_session_id INT NOT NULL,
 token_hash CHAR(64) NOT NULL UNIQUE,
 expires_at DATETIME NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_qr_session FOREIGN KEY (class_session_id) REFERENCES class_sessions(id) ON DELETE CASCADE,
 INDEX idx_qr_expiry(expires_at), INDEX idx_qr_session(class_session_id,expires_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS attendance (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 class_session_id INT NOT NULL,
 student_id INT NOT NULL,
 scanned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 status ENUM('present','late','excused') NOT NULL DEFAULT 'present',
 source ENUM('qr','manual') NOT NULL DEFAULT 'qr',
 note VARCHAR(255) NULL,
 CONSTRAINT uq_student_session UNIQUE(class_session_id,student_id),
 CONSTRAINT fk_att_session FOREIGN KEY (class_session_id) REFERENCES class_sessions(id) ON DELETE CASCADE,
 CONSTRAINT fk_att_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
 INDEX idx_attendance_date(scanned_at), INDEX idx_attendance_student(student_id,scanned_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS audit_logs (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NULL,
 action VARCHAR(80) NOT NULL,
 details VARCHAR(500) NULL,
 ip_address VARCHAR(45) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_audit_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL,
 INDEX idx_audit_created(created_at), INDEX idx_audit_user(user_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS login_attempts (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 username VARCHAR(80) NOT NULL,
 ip_address VARCHAR(45) NULL,
 attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_login_attempt(username,attempted_at), INDEX idx_login_ip(ip_address,attempted_at)
) ENGINE=InnoDB;

-- Администратори аввал тавассути setup_admin.php сохта мешавад.
