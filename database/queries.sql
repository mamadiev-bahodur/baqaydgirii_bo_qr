
USE student_attendance_qr;

-- ==========================================
-- 1. JOIN: Намоиши донишҷӯён ва гурӯҳҳо
-- ТФ-01: Намоиши маълумоти донишҷӯён
-- ==========================================

SELECT
    u.id,
    u.full_name,
    u.username,
    g.group_name
FROM users u
INNER JOIN student_groups g
    ON u.group_id = g.id
WHERE u.role = 'student';


-- ==========================================
-- 2. LIKE: Ҷустуҷӯи донишҷӯ аз рӯйи ном
-- ТФ-02: Ҷустуҷӯи донишҷӯён
-- ==========================================

SELECT
    id,
    full_name,
    username
FROM users
WHERE full_name LIKE '%Саид%';


-- ==========================================
-- 3. GROUP BY ва COUNT:
-- Ҳисоб кардани ҳозиршавӣ
-- ТФ-03: Ҳисоботи ҳозиршавии донишҷӯён
-- ==========================================

SELECT
    u.full_name,
    COUNT(a.id) AS attendance_count
FROM users u
LEFT JOIN attendance a
    ON u.id = a.student_id
WHERE u.role = 'student'
GROUP BY u.id, u.full_name;


-- ==========================================
-- 4. LEFT JOIN:
-- Намоиши донишҷӯён бо ҳозиршавӣ
-- ТФ-04: Намоиши донишҷӯёни ҳозирнашуда низ
-- ==========================================

SELECT
    u.full_name,
    a.scanned_at,
    a.status
FROM users u
LEFT JOIN attendance a
    ON u.id = a.student_id
WHERE u.role = 'student';


-- ==========================================
-- 5. ФИЛТР АЗ РӮЙИ STATUS
-- ТФ-05: Ҷудо кардани донишҷӯёни ҳозир
-- ==========================================

SELECT
    u.full_name,
    a.status,
    a.source,
    a.scanned_at
FROM attendance a
INNER JOIN users u
    ON a.student_id = u.id
WHERE a.status = 'present'
AND a.source = 'qr';


-- ==========================================
-- 6. LIMIT ва OFFSET:
-- Намоиши рӯйхати донишҷӯён
-- ТФ-06: Саҳифабандии рӯйхат
-- ==========================================

SELECT
    id,
    full_name,
    username
FROM users
WHERE role = 'student'
ORDER BY id
LIMIT 5 OFFSET 0;
