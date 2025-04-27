<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

class User
{
    /**
     * @var AppContext
     */
    private AppContext $ctx;

    /**
     *
     * @var \Config
     */
    private \Config $ncfg;
    /**
     *
     * @var Database
     */
    private Database $db;

    /**
     *
     * @var array<string,string|int|null>
     */
    private $user = [];

    /**
     *
     * @var array<string,string>
     */
    private array $prefs = [];

    /**

     * @var array<int,int>
     */
    private $categories_state = [];

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->db = $ctx->get('Mysql');
        $this->ncfg = $ctx->get('Config');

        if (isset($_SESSION['uid']) && $_SESSION['uid'] > 0) {
            $this->user = $this->getProfile($_SESSION['uid']);
            if (empty($this->user['sid']) || $this->user['sid'] != session_id()) {
                $this->user = [];
                $this->user['id'] = -1;
            }
        } elseif (!empty($_COOKIE['uid']) && !empty($_COOKIE['sid'])) {
            $this->user = $this->getProfile($_COOKIE['uid']);
            if (!empty($this->user['sid']) && $this->user['sid'] == $_COOKIE['sid']) {
                $_SESSION['uid'] = $_COOKIE['uid'];
                $this->updateSessionId();
            } else {
                $this->user = [];
                $this->user['id'] = -1;
            }
        } else {
            $this->user = [];
            $this->user['id'] = -1;
        }

        $this->user['lang'] ??= $this->ncfg->get('lang');
        $this->user['theme'] ??= $this->ncfg->get('theme');
        $this->user['timezone'] ??= $this->ncfg->get('default_timezone');

        $this->user['id'] > 0 ? $this->loadPrefs() : null;
        $this->loadUserHostCatsState();
    }

    /**
     *
     * @return int
     */
    public function getId(): int
    {
        return (int) $this->user['id'];
    }

    /**
     *
     * @return array<string,string|int|null>
     */
    public function getUser(): array
    {
        return $this->user;
    }

    /**
     *
     * @return string|null
     */
    public function getLang(): ?string
    {
        return is_string($this->user['lang']) ? $this->user['lang'] : null;
    }

    /**
     *
     * @return string|null
     */
    public function getTheme(): string
    {
        return is_string($this->user['theme']) ? $this->user['theme'] : null;
    }
    /**
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return is_string($this->user['email']) ? $this->user['email'] : null;
    }
    /**
     *
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return is_string($this->user['username']) ? $this->user['username'] : null;
    }
    /**
     *
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return is_string($this->user['password']) ? $this->user['password'] : null;
    }
    /**
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return empty($this->user['isAdmin']) ? false : true;
    }

    /**
     *
     * @param int $uid
     *
     * @return array<string,string>
     */
    public function getProfile(int $uid): array
    {
        $result = $this->db->select('users', '*', ['id' => $uid], 'LIMIT 1');
        $user = $this->db->fetch($result);

        return $user ?: [];
    }
    /**
     *
     * @return string|null
     */
    public function getTimeZone(): ?string
    {

        $timezone = $this->user['timezone'];
        if (empty($timezone)) {
            $timezone = $this->ncfg->get('default_timezone');
        }
        return is_string($timezone) ? $timezone : null;
    }

    /**
     *
     * @param string $format
     * @return string|bool
     */
    public function getDateNow(string $format = null): string|bool
    {
        if (!$format) {
            $format = $this->ncfg->get('datatime_format');
        }

        return format_date_now($this->user['timezone'], $format);
    }

    /**
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function checkUser(string $username, string $password): int|bool
    {

        $result = $this->db->select('users', '*', ['username' => $username], 'LIMIT 1');
        $user_check = $this->db->fetch($result);

        if (empty($user_check) || empty($user_check['id'])) {
            return false;
        }
        !empty($password) ? $password_hashed = $this->encryptPassword($password) : $password_hashed = '';

        if (($user_check['password'] == $password_hashed)) {
            return (int) $user_check['id'];
        }

        return false;
    }

    /**
     *
     * @param int $user_id
     * @return bool
     */
    public function setUser(int $user_id): bool
    {
        $_SESSION['uid'] = $user_id;
        $this->user = $this->getProfile($user_id);
        $this->updateSessionId();

        return true;
    }

    /**
     *
     * @return array<int,int>
     */
    public function getHostsCatState(): array
    {
        return $this->categories_state;
    }

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


    /**
     *
     * @return void
     */
    private function loadUserHostCatsState(): void
    {
        $prefs_cats = $this->getPref('hosts_cats_state');
        $h_prefs_cats = json_decode($prefs_cats, true);

        $hosts_categories = $this->ctx->get('Categories')->getByType(1);
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
        /** @var array<int, array <string,string>> $categories */
        $categories = $this->ctx->get('Categories')->prepareCats(1);
        foreach ($categories as $key => $cat) {
            $id = $cat['id'];
            if (isset($this->categories_state[$id])) {
                $categories[$key]['on'] = $this->categories_state[$id];
            } else {
                $categories[$key]['on'] = 1;
            }
        }

        return $categories;
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
            Log::error('Max cats state reached');
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
     *
     * @return bool
     */
    private function updateSessionId(): bool
    {
        if (defined('PHP_VERSION_ID') && PHP_VERSION_ID >= 70300) {
            setcookie('sid', session_id(), [
                'expires' => time() + $this->ncfg->get('sid_expire'),
                'secure' => true,
                'samesite' => 'lax',
            ]);
            setcookie('uid', (string) $this->getId(), [
                'expires' => time() + $this->ncfg->get('sid_expire'),
                'secure' => true,
                'samesite' => 'lax',
            ]);
            setcookie('username', $this->getUsername(), [
                'expires' => time() + (10 * 365 * 24 * 120),
                'secure' => true,
                'samesite' => 'lax',
            ]);
        } else {
            setcookie(
                'sid',
                session_id(),
                time() + $this->ncfg->get('sid_expire'),
                $this->ncfg->get('rel_path')
            );
            setcookie(
                'uid',
                (string)$this->getId(),
                time() + $this->ncfg->get('sid_expire'),
                $this->ncfg->get('rel_path')
            );
            setcookie(
                'username',
                $this->getUsername(),
                time() + (10 * 365 * 24 * 120),
                $this->ncfg->get('rel_path')
            );
        }
        $new_sid = session_id();

        $this->db->update('users', ['sid' => $new_sid], ['id' => $this->getId()], 'LIMIT 1');
        $this->user['sid'] = $new_sid;

        return true;
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
    private function loadPrefs(): void
    {

        $prefs = [];
        $query = 'SELECT * FROM prefs WHERE uid = ' . $this->getId();
        $results = $this->db->query($query);

        $prefs = $this->db->fetchAll($results);
        if (!empty($prefs)) {
            foreach ($prefs as $pref) {
                if (!empty($pref['pref_name'])) {
                    $this->prefs[$pref['pref_name']] = $pref['pref_value'];
                }
            }
        }
    }

    /**
     *
     * @param string $r_key
     * @return string|false
     */
    public function getPref(string $r_key): string|false
    {
        return isset($this->prefs[$r_key]) ? $this->prefs[$r_key] : false;
    }

    /**
     *
     * @param string $key
     * @param mixed $value
     */
    public function setPref(string $key, mixed $value): void
    {

        if (isset($this->prefs[$key])) {
            if ($this->prefs[$key] !== $value) {
                $where['uid'] = ['value' => $this->getId()];
                $where['pref_name'] = ['value' => $key];
                $set['pref_value'] = $value;
                $this->db->update('prefs', $set, $where, 'LIMIT 1');
            }
        } else {
            $new_item = [
                'uid' => $this->getId(),
                'pref_name' => $key,
                'pref_value' => $value,
            ];
            $this->db->insert('prefs', $new_item);
        }
        $this->prefs[$key] = $value;
    }

    /**
     * Get selected network IDs for current host.
     *
     * @return int[] Array of network IDs
     */
    public function getSelectedNetworks(): array
    {
        $query = "SELECT * FROM prefs WHERE `pref_name` LIKE 'network_select_%' AND uid=". $this->getId();
        $result = $this->db->query($query);
        $rows = $this->db->fetchAll($result);

        return array_map('intval', array_column($rows, 'pref_value'));
    }
}
