<?php
declare(strict_types=1);

class UserRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function getAll(int $limit = 20, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT u.id, u.full_name, u.username, u.role,
                    u.student_code, u.is_active,
                    g.group_name
             FROM users u
             LEFT JOIN student_groups g ON g.id = u.group_id
             WHERE u.role IN ('teacher', 'student')
             ORDER BY u.id DESC
             LIMIT :limit OFFSET :offset"
        );

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT u.*, g.group_name
             FROM users u
             LEFT JOIN student_groups g ON g.id = u.group_id
             WHERE u.id = :id"
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, username
             FROM users
             WHERE username = :username"
        );
        $stmt->execute(['username' => $username]);

        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO users
             (full_name, username, password_hash, role,
              student_code, group_id, is_active)
             VALUES
             (:full_name, :username, :password_hash, :role,
              :student_code, :group_id, :is_active)"
        );

        $stmt->execute([
            'full_name' => trim($data['full_name']),
            'username' => trim($data['username']),
            'password_hash' => $data['password_hash'],
            'role' => $data['role'],
            'student_code' => $data['student_code'] ?: null,
            'group_id' => $data['group_id'] ?: null,
            'is_active' => $data['is_active'] ?? 1,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE users
             SET full_name = :full_name,
                 username = :username,
                 student_code = :student_code,
                 group_id = :group_id
             WHERE id = :id"
        );

        return $stmt->execute([
            'full_name' => trim($data['full_name']),
            'username' => trim($data['username']),
            'student_code' => $data['student_code'] ?: null,
            'group_id' => $data['group_id'] ?: null,
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM users WHERE id = :id AND role IN ('teacher', 'student')"
        );

        return $stmt->execute(['id' => $id]);
    }
    public function search(
        string $term,
        ?string $role = null,
        ?int $groupId = null
    ): array {
        $term = str_replace(
            ['!', '%', '_'],
            ['!!', '!%', '!_'],
            trim($term)
        );

        $sql = "SELECT u.id, u.full_name, u.username, u.role,
                       u.student_code, u.is_active, g.group_name
                FROM users u
                LEFT JOIN student_groups g ON g.id = u.group_id
                WHERE u.role IN ('teacher', 'student') AND (u.full_name LIKE :term_name ESCAPE '!'
                    OR u.username LIKE :term_username ESCAPE '!')";

        $params = [
            'term_name' => '%' . $term . '%',
            'term_username' => '%' . $term . '%',
        ];

        if ($role !== null && $role !== '') {
            $sql .= " AND u.role = :role";
            $params['role'] = $role;
        }

        if ($groupId !== null && $groupId > 0) {
            $sql .= " AND u.group_id = :group_id";
            $params['group_id'] = $groupId;
        }

        $sql .= " ORDER BY u.id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }
    public function searchPaginated(
        string $term,
        ?string $role,
        ?int $groupId,
        int $limit,
        int $offset
    ): array {
        $term = str_replace(
            ['!', '%', '_'],
            ['!!', '!%', '!_'],
            trim($term)
        );

        $sql = "SELECT u.id, u.full_name, u.username, u.role,
                       u.student_code, u.is_active, g.group_name
                FROM users u
                LEFT JOIN student_groups g ON g.id = u.group_id
                WHERE u.role IN ('teacher', 'student') AND (u.full_name LIKE :term_name ESCAPE '!'
                    OR u.username LIKE :term_username ESCAPE '!')";

        $params = [
            'term_name' => '%' . $term . '%',
            'term_username' => '%' . $term . '%',
        ];

        if ($role !== null && $role !== '') {
            $sql .= " AND u.role = :role";
            $params['role'] = $role;
        }

        if ($groupId !== null && $groupId > 0) {
            $sql .= " AND u.group_id = :group_id";
            $params['group_id'] = $groupId;
        }

        $sql .= " ORDER BY u.id DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countSearch(
        string $term,
        ?string $role = null,
        ?int $groupId = null
    ): int {
        $term = str_replace(
            ['!', '%', '_'],
            ['!!', '!%', '!_'],
            trim($term)
        );

        $sql = "SELECT COUNT(*)
                FROM users u
                WHERE u.role IN ('teacher', 'student') AND (u.full_name LIKE :term_name ESCAPE '!'
                    OR u.username LIKE :term_username ESCAPE '!')";

        $params = [
            'term_name' => '%' . $term . '%',
            'term_username' => '%' . $term . '%',
        ];

        if ($role !== null && $role !== '') {
            $sql .= " AND u.role = :role";
            $params['role'] = $role;
        }

        if ($groupId !== null && $groupId > 0) {
            $sql .= " AND u.group_id = :group_id";
            $params['group_id'] = $groupId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }
    public function toggleActive(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE users
             SET is_active = 1 - is_active
             WHERE id = :id AND role IN ('teacher', 'student')"
        );

        return $stmt->execute(['id' => $id]);
    }

    public function countUsers(): int
    {
        return (int) $this->pdo->query(
            "SELECT COUNT(*) FROM users
             WHERE role IN ('teacher', 'student')"
        )->fetchColumn();
    }
}