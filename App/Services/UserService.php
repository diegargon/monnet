<?php
/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 *
*/

namespace App\Services;

use App\Core\AppContext;
use App\Core\DBManager;

use App\Models\UserModel;
use App\Services\LogSystemService;
use App\Services\UserSession;

class UserService
{
    private AppContext $ctx;
    private UserModel $userModel;
    private UserSession $userSession;
    private LogSystemService $logSystem;

    private \Config $ncfg;
    private int $session_expire;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $db = new DBManager($ctx);
        $this->userModel = new UserModel($db);
        $this->userSession = new UserSession($ctx);
        $this->logSystem = new LogSystemService($ctx);
        $this->ncfg = $ctx->get('Config');
        $this->session_expire = (int) $this->ncfg->get('sid_expire');
        $this->userSession->AutoLogin();
    }

    public function login(string $username, string $password, bool $remember = true): array
    {
        $user = $this->userModel->getByUsername($username);

        if (!$user || !$this->verify_password($password, $user['password'])) {
            throw new \RuntimeException("Credenciales invalidas");
        }
        $this->userSession->set($user);


        #if ($remember) {
        #    Create DB Session
        #}

        unset($user['password']);

        return $user;
    }

    public function checkCurrentUser(): bool
    {
        $user = $this->userSession->getCurrentUser();

        return $user ? true : false;
    }

    public function getCurrentUser(): array
    {
        return $this->userSession->getCurrentUser();
    }

    public function getUser(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }
        $user = $this->userModel->getById($userId);

        if (!$user) {
            return [];
        }
        unset($user['password']);
        unset($user['sid']);

        return $user;
    }

    public function register(array $userData): array
    {
        if (empty($userData['email']) || !filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Email inválido");
        }

        if (empty($userData['password']) || strlen($userData['password']) < 8) {
            throw new \InvalidArgumentException("La contraseña debe tener al menos 8 caracteres");
        }

        if ($this->userModel->getByEmail($userData['email'])) {
            throw new \RuntimeException("El email ya está registrado");
        }

        $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        $userId = $this->userModel->create($userData);

        return $this->getUser($userId);
    }

    public function updateUser(int $userId, array $userData): string|bool
    {
        $user = $this->getUser($userId);

        if (empty($user)) {
            return 'User not exists';
        }

        if (!empty($userData['email'])) {
            $existingUser = $this->userModel->getByEmail($userData['email']);
            if ($existingUser && $existingUser['id'] != $userId) {
                return 'Email is already in use';
            }
        }

        if (!empty($userData['password'])) {
            $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        }
        $this->userModel->update($userId, $userData);

        return  true;
    }

    public function deleteUser(int $userId): bool
    {
        $user = $this->getUser($userId);
        if (empty($user)) {
            return false;
        }

        return $this->userModel->delete($userId);
    }

    public function listUsers(int $page = 1, int $perPage = 10): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        return [
            'data' => $this->userModel->list($perPage, $offset),
            'total' => $this->userModel->count(),
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($this->userModel->count() / $perPage)
        ];
    }

    /**
     * Autentica un usuario
     */
    /*
    public function authenticate(string $email, string $password): array
    {
        $user = $this->userModel->getByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            throw new \RuntimeException("Credenciales inválidas");
        }

        // Eliminar datos sensibles antes de retornar
        unset($user['password']);
        unset($user['sid']);

        return $user;
    }
    */

    public function getDateFormat(): string
    {
        $user = $this->userSession->getCurrentUser();

        if (empty($user['dateformat'])) {
            $dateformat = $this->ncfg->get('datetime_format');
        }

        return isset($dateformat) ? $dateformat : '';
    }

    public function getTimezone(): string
    {
        $user = $this->userSession->getCurrentUser();

        if (empty($user['timezone'])) {
            $timezone = $this->ncfg->get('default_timezone');
        }
        return is_string($timezone) ? $timezone : 'UTC';
    }

    public function verify_password(string $password, string $db_password): bool
    {
        # return password_verify($password, $db_password);
        return $this->encryptPassword($password) === $db_password;
    }

    /**
     *
     * @param string $password
     * @return string
     */
    private function encryptPassword(string $password): string
    {
        return sha1($password);
    }


    /**
     *
     * @return void
     */
    public function logout(): void
    {
        $this->userSession->logout();
    }

    /**
     * Obtiene el ID del usuario autenticado actual.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        $user = $this->userSession->getCurrentUser();
        return isset($user['id']) ? (int)$user['id'] : null;
    }
}
