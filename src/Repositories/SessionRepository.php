<?php
declare(strict_types=1);

class SessionRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query(
            "SELECT cs.id, cs.lesson_title, cs.status,
                    cs.created_at, g.group_name,
                    u.full_name AS teacher_name
             FROM class_sessions cs
             INNER JOIN student_groups g ON g.id = cs.group_id
             INNER JOIN users u ON u.id = cs.teacher_id
             ORDER BY cs.id DESC"
        );

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT cs.*, g.group_name,
                    u.full_name AS teacher_name
             FROM class_sessions cs
             INNER JOIN student_groups g ON g.id = cs.group_id
             INNER JOIN users u ON u.id = cs.teacher_id
             WHERE cs.id = :id"
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function create(
        int $teacherId,
        int $groupId,
        string $lessonTitle
    ): int {
        $stmt = $this->pdo->prepare(
            "INSERT INTO class_sessions
             (teacher_id, group_id, lesson_title, status)
             VALUES (:teacher_id, :group_id, :lesson_title, 'active')"
        );

        $stmt->execute([
            'teacher_id' => $teacherId,
            'group_id' => $groupId,
            'lesson_title' => trim($lessonTitle),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE class_sessions
             SET status = :status
             WHERE id = :id"
        );

        return $stmt->execute([
            'status' => $status,
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM class_sessions WHERE id = :id"
        );

        return $stmt->execute(['id' => $id]);
    }

    public function getAttendanceReport(): array
    {
        $stmt = $this->pdo->query(
            "SELECT cs.id AS session_id,
                    cs.lesson_title,
                    g.group_name,
                    a.status,
                    COUNT(a.id) AS total_students
             FROM class_sessions cs
             INNER JOIN student_groups g ON g.id = cs.group_id
             LEFT JOIN attendance a ON a.class_session_id = cs.id
             GROUP BY cs.id, cs.lesson_title, g.group_name, a.status
             ORDER BY cs.id DESC"
        );

        return $stmt->fetchAll();
    }
}