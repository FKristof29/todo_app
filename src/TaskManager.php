<?php

require_once __DIR__ . '/Task.php';
require_once __DIR__ . '/Database.php';

class TaskManager {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->initTable();
    }


    private function initTable(): void {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS tasks (
                id          INT AUTO_INCREMENT PRIMARY KEY,
                title       VARCHAR(255) NOT NULL,
                description TEXT,
                completed   TINYINT(1) DEFAULT 0,
                created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }


    public function getAll(bool $onlyPending = false): array {
        $sql = "SELECT * FROM tasks";
        if ($onlyPending) $sql .= " WHERE completed = 0";
        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->db->query($sql);
        return array_map([$this, 'rowToTask'], $stmt->fetchAll());
    }

    public function getById(int $id): ?Task {
        $stmt = $this->db->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? $this->rowToTask($row) : null;
    }

    public function create(string $title, string $description = ''): Task {
        $stmt = $this->db->prepare(
            "INSERT INTO tasks (title, description) VALUES (?, ?)"
        );
        $stmt->execute([trim($title), trim($description)]);
        $id = (int) $this->db->lastInsertId();
        return $this->getById($id);
    }

    public function update(int $id, string $title, string $description): bool {
        $stmt = $this->db->prepare(
            "UPDATE tasks SET title = ?, description = ? WHERE id = ?"
        );
        return $stmt->execute([trim($title), trim($description), $id]);
    }

    public function toggleComplete(int $id): bool {
        $task = $this->getById($id);
        if (!$task) return false;
        $stmt = $this->db->prepare("UPDATE tasks SET completed = ? WHERE id = ?");
        return $stmt->execute([$task->isCompleted() ? 0 : 1, $id]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM tasks WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function deleteCompleted(): int {
        $stmt = $this->db->prepare("DELETE FROM tasks WHERE completed = 1");
        $stmt->execute();
        return $stmt->rowCount();
    }


    public function getStats(): array {
        $row = $this->db->query("
            SELECT
                COUNT(*) AS total,
                SUM(completed = 1) AS done,
                SUM(completed = 0) AS pending
            FROM tasks
        ")->fetch();
        return $row;
    }


    private function rowToTask(array $row): Task {
        return new Task(
            (int) $row['id'],
            $row['title'],
            $row['description'] ?? '',
            (bool) $row['completed'],
            $row['created_at']
        );
    }
}
