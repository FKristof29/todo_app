<?php

class Task {
    private int $id;
    private string $title;
    private string $description;
    private bool $completed;
    private string $createdAt;

    public function __construct(int $id, string $title, string $description, bool $completed = false, string $createdAt = '') {
        $this->id          = $id;
        $this->title       = $title;
        $this->description = $description;
        $this->completed   = $completed;
        $this->createdAt   = $createdAt ?: date('Y-m-d H:i:s');
    }

    public function getId(): int          { return $this->id; }
    public function getTitle(): string    { return $this->title; }
    public function getDescription(): string { return $this->description; }
    public function isCompleted(): bool   { return $this->completed; }
    public function getCreatedAt(): string { return $this->createdAt; }

    public function setTitle(string $title): void          { $this->title = $title; }
    public function setDescription(string $desc): void     { $this->description = $desc; }
    public function setCompleted(bool $completed): void    { $this->completed = $completed; }

    public function toArray(): array {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'completed'   => $this->completed,
            'created_at'  => $this->createdAt,
        ];
    }
}
