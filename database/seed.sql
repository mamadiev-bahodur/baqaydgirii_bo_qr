
USE student_attendance_qr;

SET NAMES utf8mb4;

-- =====================================
-- 1. ГУРӮҲҲО
-- =====================================

INSERT IGNORE INTO student_groups (id, group_name) VALUES
(1, 'Гурӯҳи 101'),
(2, 'Гурӯҳи 102'),
(3, 'Гурӯҳи 103');

-- =====================================
-- 2. ИСТИФОДАБАРАНДАГОН
-- =====================================
-- Пароли ҳамаи ҳисобҳои намунавӣ: password
-- Hash бо password_verify() мувофиқ аст.
-- Танҳо барои санҷиш истифода баред.

INSERT IGNORE INTO users
(id, full_name, username, password_hash, role, student_code, group_id)
VALUES
(1, 'Администратори система', 'admin',
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.',
'admin', NULL, NULL),

(2, 'Аҳмадов Фирдавс', 'teacher1',
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.',
'teacher', NULL, NULL),

(3, 'Каримов Ҷамшед', 'teacher2',
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.',
'teacher', NULL, NULL),

(4, 'Саидов Баҳром', 'student1',
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.',
'student', 'ST001', 1),

(5, 'Назаров Фарҳод', 'student2',
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.',
'student', 'ST002', 1),

(6, 'Раҳимова Мадина', 'student3',
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.',
'student', 'ST003', 1),

(7, 'Юсуфов Сино', 'student4',
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.',
'student', 'ST004', 1),

(8, 'Шарифзода Меҳрона', 'student5',
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.',
'student', 'ST005', 1),

(9, 'Абдуллоев Далер', 'student6',
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.',
'student', 'ST006', 2),

(10, 'Исмоилова Шаҳноза', 'student7',
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.',
'student', 'ST007', 2),

(11, 'Ҳасанов Фирӯз', 'student8',
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.',
'student', 'ST008', 2),

(12, 'Мирзоев Сомон', 'student9',
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.',
'student', 'ST009', 3),

(13, 'Бобоева Малика', 'student10',
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.',
'student', 'ST010', 3);

-- =====================================
-- 3. ДАРСҲО
-- =====================================

INSERT IGNORE INTO class_sessions
(id, teacher_id, group_id, lesson_title, status, started_at)
VALUES
(1, 2, 1, 'Асосҳои барномасозӣ', 'closed', NOW() - INTERVAL 2 DAY),
(2, 2, 1, 'Забони PHP', 'closed', NOW() - INTERVAL 1 DAY),
(3, 3, 2, 'Системаҳои базаи маълумот', 'active', NOW());

-- =====================================
-- 4. QR TOKEN-ҲО
-- =====================================

INSERT IGNORE INTO qr_tokens
(id, class_session_id, token_hash, expires_at)
VALUES
(1, 1, SHA2('qr-sample-token-001', 256), NOW() + INTERVAL 1 DAY),
(2, 2, SHA2('qr-sample-token-002', 256), NOW() + INTERVAL 1 DAY),
(3, 3, SHA2('qr-sample-token-003', 256), NOW() + INTERVAL 1 DAY);

-- =====================================
-- 5. ҲОЗИРШАВИИ ДОНИШҶӮЁН
-- =====================================

INSERT IGNORE INTO attendance
(class_session_id, student_id, scanned_at, status, source)
VALUES
(1, 4, NOW() - INTERVAL 2 DAY, 'present', 'qr'),
(1, 5, NOW() - INTERVAL 2 DAY, 'present', 'qr'),
(1, 6, NOW() - INTERVAL 2 DAY, 'late', 'qr'),
(1, 7, NOW() - INTERVAL 2 DAY, 'present', 'manual'),
(1, 8, NOW() - INTERVAL 2 DAY, 'present', 'qr'),
(2, 4, NOW() - INTERVAL 1 DAY, 'present', 'qr'),
(2, 5, NOW() - INTERVAL 1 DAY, 'late', 'qr'),
(2, 6, NOW() - INTERVAL 1 DAY, 'present', 'qr'),
(2, 7, NOW() - INTERVAL 1 DAY, 'excused', 'manual'),
(2, 8, NOW() - INTERVAL 1 DAY, 'present', 'qr');

-- =====================================
-- 6. САБТИ АМАЛҲО
-- =====================================

INSERT IGNORE INTO audit_logs
(id, user_id, action, details, ip_address)
VALUES
(1, 1, 'login', 'Воридшавии администратор', '127.0.0.1'),
(2, 2, 'create_session', 'Сохтани дарси нав', '127.0.0.1'),
(3, 4, 'scan_qr', 'Скан кардани QR-код', '127.0.0.1');

-- =====================================
-- 7. КӮШИШҲОИ ВОРИДШАВӢ
-- =====================================

INSERT IGNORE INTO login_attempts
(id, username, ip_address)
VALUES
(1, 'admin', '127.0.0.1'),
(2, 'teacher1', '127.0.0.1'),
(3, 'student1', '127.0.0.1');

-- =====================================
-- АНҶОМ
-- =====================================

SELECT 'Маълумоти намунавӣ илова шуд' AS result;
