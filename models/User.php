<?php
class User
{
    public ?int $id = null;
    public string $full_name = '';
    public string $username = '';
    public string $role = 'staff';
    public int $is_active = 1;
    public ?string $created_at = null;

    public static function fromArray(array $r): self
    {
        $u = new self();
        $u->id = (int)($r['id'] ?? 0);
        $u->full_name = $r['full_name'] ?? '';
        $u->username = $r['username'] ?? '';
        $u->role = $r['role'] ?? 'staff';
        $u->is_active = (int)($r['is_active'] ?? 1);
        $u->created_at = $r['created_at'] ?? null;
        return $u;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'username' => $this->username,
            'role' => $this->role,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}
