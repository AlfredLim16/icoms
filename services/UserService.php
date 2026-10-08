<?php
class UserService extends BaseService
{
    private UserRepository $repo;

    public function __construct()
    {
        $this->repo = new UserRepository();
    }

    public function list(): array
    {
        return array_map(fn($r) => User::fromArray($r), $this->repo->all());
    }

    public function create(array $d): User
    {
        $this->validateProfile($d);

        if (strlen($d['password'] ?? '') < 8)
            throw new Exception('Password must be at least 8 characters.');

        if ($this->repo->findByUsername($d['username']))
            throw new Exception('Username is already taken.');

        $id = $this->repo->create([
            'full_name' => trim($d['full_name']),
            'username' => trim($d['username']),
            'password_hash' => password_hash($d['password'], PASSWORD_DEFAULT),
            'role' => $d['role'],
            'is_active' => (int)($d['is_active'] ?? 1),
        ]);

        return User::fromArray($this->repo->findById($id));
    }

    public function update(int $id, array $d): User
    {
        $existing = $this->repo->findById($id);
        if (!$existing) throw new Exception('User not found.');

        $this->validateProfile($d);

        $byUname = $this->repo->findByUsername($d['username']);
        if ($byUname && (int)$byUname['id'] !== $id)
            throw new Exception('Username is already taken.');

        if ($existing['role'] === 'owner' && $d['role'] !== 'owner') {
            if ($this->repo->countOwnersExcept($id) === 0)
                throw new Exception('Cannot demote the last owner.');
        }

        $this->repo->update($id, [
            'full_name' => trim($d['full_name']),
            'username' => trim($d['username']),
            'role' => $d['role'],
            'is_active' => (int)($d['is_active'] ?? 1),
        ]);

        return User::fromArray($this->repo->findById($id));
    }

    public function resetPassword(int $id, string $newPassword): void
    {
        if (!$this->repo->findById($id)) throw new Exception('User not found.');
        if (strlen($newPassword) < 8)
            throw new Exception('Password must be at least 8 characters.');

        $this->repo->updatePassword($id, password_hash($newPassword, PASSWORD_DEFAULT));
    }

    public function changeOwnPassword(int $id, string $current, string $new): void
    {
        $user = $this->repo->findByUsername(Session::get('username'));
        if (!$user || (int)$user['id'] !== $id)
            throw new Exception('User mismatch.');

        if (!password_verify($current, $user['password_hash']))
            throw new Exception('Current password is incorrect.');

        if (strlen($new) < 8)
            throw new Exception('New password must be at least 8 characters.');

        if ($current === $new)
            throw new Exception('New password must be different from the current one.');

        $this->repo->updatePassword($id, password_hash($new, PASSWORD_DEFAULT));
    }

    public function delete(int $id, int $currentUserId): void
    {
        $user = $this->repo->findById($id);
        if (!$user) throw new Exception('User not found.');

        if ($id === $currentUserId)
            throw new Exception('You cannot delete your own account.');

        if ($user['role'] === 'owner' && $this->repo->countOwnersExcept($id) === 0)
            throw new Exception('Cannot delete the last owner.');

        $this->repo->delete($id);
    }

    private function validateProfile(array $d): void
    {
        if (empty(trim($d['full_name'] ?? '')))
            throw new Exception('Full name is required.');
        if (empty(trim($d['username'] ?? '')))
            throw new Exception('Username is required.');
        if (!preg_match('/^[a-zA-Z0-9_.]{3,50}$/', $d['username']))
            throw new Exception('Username must be 3–50 chars (letters, numbers, dot, underscore).');
        if (!in_array($d['role'] ?? '', ['owner', 'staff'], true))
            throw new Exception('Invalid role.');
    }
}
