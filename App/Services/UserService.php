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
use App\Core\ConfigService;

use App\Services\CategoriesService;

use App\Models\UserModel;
use App\Models\PrefsModel;

class UserService
{
    private AppContext $ctx;
    private UserModel $userModel;
    private UserSession $userSession;
    private LogSystemService $logSystem;
    private PrefsModel $prefsModel;
    private ConfigService $ncfg;
    private int $session_expire;

    /** @var <string, mixed> */
    private array $prefs = [];
    /** @var <string, mixed> */
    private array $categories_state = [];

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $db = new DBManager($ctx);
        $this->userModel = new UserModel($db);
        $this->userSession = new UserSession($ctx);
        $this->logSystem = new LogSystemService($ctx);
        $this->prefsModel = new PrefsModel($db);
        $this->ncfg = $ctx->get(ConfigService::class);
        $this->session_expire = (int) $this->ncfg->get('sid_expire');
        $this->userSession->AutoLogin();
        $this->loadPrefs();
        $this->loadUserHostCatsState();
    }

    /**
     *
     * @param string $username
     * @param string $password
     * @param bool $remember
     * @return array<string, mixed>
     * @throws \RuntimeException
     */
    public function login(string $username, string $password, bool $remember = true): array
    {
        $user = $this->userModel->getByUsername($username);

        if (!$user || !$this->verify_password($password, $user['password'])) {
            throw new \RuntimeException("Credenciales invalidas");
        }
        $this->userSession->set($user);

        if ($remember) {
            $this->userSession->createDBSession($user['id']);
        }

        unset($user['password']);

        return $user;
    }

    /**
     *
     * @return bool
     */
    public function checkCurrentUser(): bool
    {
        $user = $this->userSession->getCurrentUser();

        return $user ? true : false;
    }

    /**
     *
     * @return array<string, mixed>
     */
    public function getCurrentUser(): array
    {
        return $this->userSession->getCurrentUser();
    }

    /**
     *
     * @param int $userId
     * @return array<string, mixed>
     */
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

    /**
     *
     * @param array<string, mixed> $userData
     * @return array<string, mixed>
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
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
        try {
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

            return true;
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
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

        if (!empty($user['timezone'])) {
            # TODO: Validate
            $timezone = $user['timezone'];
        } else {
            $timezone = $this->ncfg->get('default_timezone', 'UTC');
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

    /**
     * Get the language of the authenticated user.
     *
     * @return int|null
     */
    public function getLang(): string
    {
        $user = $this->userSession->getCurrentUser();

        if ($user && !empty($user['lang'])) {
            $lang = $user['lang'];
        } else {
            $lang = $this->ncfg->get('default_lang', 'es');
        }
        return $lang ?? 'es';
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername(): string
    {
        $user = $this->getCurrentUser();
        return isset($user['username']) ? $user['username'] : '';
    }

    /**
     *
     * @return string
     */
    public function getTheme(): string
    {
        $user = $this->getCurrentUser();
        if (empty($user)) {
            $theme = $this->getCurrentUser();
        } else {
            $theme = $this->ncfg->get('default_theme', 'default');
        }

        return $theme;
    }
    /**
     * Check if user is authorized
     *
     * @return bool
     */
    public function isAuthorized(): bool
    {
        return $this->checkCurrentUser();
    }

    /**
     * Load user prefs
     * @return void
     */
    public function loadPrefs(): void
    {
        $user = $this->getCurrentUser();
        $this->prefs = [];
        if (!empty($user['id'])) {
            $this->prefs = $this->prefsModel->loadPrefs((int)$user['id']);
        }
    }

    /**
     * Get User Pref
     * @param string $key
     * @return mixed
     */
    public function getPref(string $key): mixed
    {
        $user = $this->getCurrentUser();
        if (empty($user['id'])) {
            return false;
        }
        // Prefetch cache
        if (isset($this->prefs[$key])) {
            return $this->prefs[$key];
        }
        $value = $this->prefsModel->getPref((int)$user['id'], $key);
        if ($value !== false) {
            $this->prefs[$key] = $value;
        }
        return $value;
    }

    /**
     * Set User Pref
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setPref(string $key, mixed $value): void
    {
        $user = $this->getCurrentUser();
        if (empty($user['id'])) {
            return;
        }
        $this->prefsModel->setPref((int)$user['id'], $key, $value);
        $this->prefs[$key] = $value;
    }

    /**
     * Load the user cats data
     * @return void
     */
    private function loadUserHostCatsState(): void
    {
        $prefs_cats = $this->getPref('hosts_cats_state');
        $h_prefs_cats = json_decode($prefs_cats, true);
        $categories = $this->ctx->get(CategoriesService::class);

        $hosts_categories = $categories->getByType(1);
        foreach ($hosts_categories as $hcats) {
            $id = $hcats['id'];
            //if not set to then is set
            if (isset($h_prefs_cats[$id]) && $h_prefs_cats[$id] == 0) {
                $this->categories_state[$id] = 0;
            } else {
                $this->categories_state[$id] = 1;
            }
        }
    }

    /**
     *
     * @return array<int, array<string, int|string>>
     */
    public function getHostsCats(): array
    {
        $categories = $this->ctx->get(CategoriesService::class);
        $result = $categories->prepareCats(1);
        foreach ($result as $key => $cat) {
            $id = $cat['id'];
            if (isset($this->categories_state[$id])) {
                $result[$key]['on'] = $this->categories_state[$id];
            } else {
                $result[$key]['on'] = 1;
            }
        }

        return $result;
    }

    /**
     *
     * @param int $id
     * @return int
     */
    public function toggleHostsCat(int $id): int
    {
        $this->categories_state[$id] = (!$this->categories_state[$id]) ? 1 : 0;
        $this->saveHostsCatsState();

        return $this->categories_state[$id];
    }

    /**
     *
     * @return bool
     */
    public function saveHostsCatsState(): bool
    {
        $json_cats_state = json_encode($this->categories_state);
        if ($json_cats_state === false) {
            return false;
        }
        if (mb_strlen($json_cats_state, 'UTF-8') > 255) {
            $logSys = new LogSystemService($this->ctx);
            $logSys->error('Max cats state reached');
            return false;
        }
        $this->setPref('hosts_cats_state', $json_cats_state);

        return true;
    }

    /**
     *
     * @return void
     */
    public function turnHostsCatsOff(): void
    {
        foreach (array_keys($this->categories_state) as $key) {
            $this->categories_state[$key] = 0;
        }
        $this->saveHostsCatsState();
    }
    /**
     *
     * @return void
     */
    public function turnHostsCatsOn(): void
    {
        foreach (array_keys($this->categories_state) as $key) {
            $this->categories_state[$key] = 1;
        }
        $this->saveHostsCatsState();
    }

    /**
     * Get selected network IDs for current host.
     *
     * @return int[] Array of network IDs
     */
    public function getSelNetworks(): array
    {
        $result = $this->prefsModel->getUserSelNetworks($this->getId());

        return array_map('intval', array_column($result, 'pref_value'));
    }

    /**
     *
     * @return array<string, mixed>
     */
    public function getEnabledHostCatId(): array
    {
        $enabled_cats = [];
        foreach ($this->categories_state  as $cat_id => $cat_state) {
            if ($cat_state == 1) {
                $enabled_cats[] = $cat_id;
            }
        }

        return $enabled_cats;
    }
}

