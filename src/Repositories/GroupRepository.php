<?php
declare(strict_types=1);

class GroupRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query(
            "SELECT id, group_name, created_at
             FROM student_groups
             ORDER BY group_name"
        );

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, group_name, created_at
             FROM student_groups
             WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function create(string $groupName): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO student_groups (group_name)
             VALUES (:group_name)"
        );
        $stmt->execute(['group_name' => trim($groupName)]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, string $groupName): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE student_groups
             SET group_name = :group_name
             WHERE id = :id"
        );

        return $stmt->execute([
            'group_name' => trim($groupName),
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM student_groups WHERE id = :id"
        );

        return $stmt->execute(['id' => $id]);
    }
    public function existsByName(string $name, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM student_groups
                WHERE group_name = :name";

        $params = ['name' => trim($name)];

        if ($excludeId !== null) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }
}