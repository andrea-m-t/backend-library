<?php
declare(strict_types=1);

final class User
{
    private int $id;
    private string $userName;
    private string $email;
    private string $role;
    private ?int $createdAt;
    private ?int $updateAt;

    public function __construct(
        int $id,
        string $userName,
        string $email,
        string $role,
        ?int $createdAt = null,
        ?int $updateAt = null
    ) {
        $this->id = $id;
        $this->userName = $userName;
        $this->email = $email;
        $this->role = $role === 'admin' ? 'admin' : 'user';
        $this->createdAt = $createdAt;
        $this->updateAt = $updateAt;
    }

    public static function fromDatabaseRow(array $row): self
    {
        $rawRole = strtolower(trim((string) ($row['userType'] ?? 'regular')));
        $role = $rawRole === 'admin' ? 'admin' : 'user';

        $createdAt = array_key_exists('createdAt', $row) ? (int) $row['createdAt'] : null;
        $updateAt = array_key_exists('updateAt', $row) ? (int) $row['updateAt'] : null;

        return new self(
            (int) ($row['userID'] ?? 0),
            (string) ($row['userName'] ?? ''),
            (string) ($row['email'] ?? ''),
            $role,
            $createdAt,
            $updateAt
        );
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function toArray(): array
    {
        return [
            'userID' => $this->id,
            'userName' => $this->userName,
            'email' => $this->email,
            'createdAt' => $this->createdAt,
            'updateAt' => $this->updateAt,
            'userType' => $this->role,
        ];
    }
}
