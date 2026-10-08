<?php
class AuthService extends BaseService
{
    private UserRepository $repo;

    public function __construct()
    {
        $this->repo = new UserRepository();
    }

    public function login(string $username, string $password): array
    {
        if (empty($username) || empty($password))
            throw new Exception('Username and password are required.');

        $user = $this->repo->findByUsername(trim($username));

        if (!$user || !password_verify($password, $user['password_hash']))
            throw new Exception('Invalid username or password.');

        if ((int)$user['is_active'] === 0)
            throw new Exception('Your account is disabled.');

        Session::start();
        session_regenerate_id(true);
        Session::set('user_id', (int)$user['id']);
        Session::set('username', $user['username']);
        Session::set('full_name', $user['full_name']);
        Session::set('role', $user['role']);

        return [
            'id' => (int)$user['id'],
            'name' => $user['full_name'],
            'role' => $user['role'],
        ];
    }

    public function logout(): void
    {
        Session::destroy();
    }
}
