<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
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
     * @var array<string|int>
     */
    private array $cfg;

    /**
     *
     * @var Database
     */
    private Database $db;

    /**
     *
     * @var array<string>
     */
    private array $lng;

    /**
     *
     * @var array
     */
    private $user = [];

    /**
     *
     * @var array
     */
    private array $prefs = [];

    /**

     * @var array
     */
    private $categories_state = [];

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->db = $ctx->get('Mysql');
        $this->cfg = $ctx->get('cfg');
        $this->lng = $ctx->get('lng');

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

        $this->user['lang'] ??= $this->cfg['lang'];
        $this->user['theme'] ??= $this->cfg['theme'];
        $this->user['timezone'] ??= $this->cfg['timezone'];

        $this->user['id'] > 0 ? $this->loadPrefs() : null;
        $this->loadUserHostCatsState();
    }

    public function getId()
    {
        return $this->user['id'];
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getLang()
    {
        return $this->user['lang'];
    }

    public function getTheme()
    {
        return $this->user['theme'];
    }

    public function getEmail()
    {
        return $this->user['email'] ? $this->user['email'] : false;
    }

    public function getUsername()
    {
        return $this->user['username'] ? $this->user['username'] : false;
    }

    public function getPassword()
    {
        return $this->user['password'] ? $this->user['password'] : false;
    }

    public function isAdmin()
    {
        return empty($this->user['isAdmin']) ? false : true;
    }

    /**
     *
     * @param int $uid
     *
     * @return array
     */
    public function getProfile(int $uid)
    {
        $result = $this->db->select('users', '*', ['id' => $uid], 'LIMIT 1');
        $user = $this->db->fetch($result);

        return $user ?: [];
    }

    public function getTimezone()
    {
        return $this->user['timezone'];
    }

    public function getDateNow(string $format = null)
    {
        if (!$format) {
            $format = $this->cfg['datatime_format'];
        }

        return formatted_date_now($this->user['timezone'], $format);
    }

    public function checkUser(string $username, string $password)
    {

        $result = $this->db->select('users', '*', ['username' => $username], 'LIMIT 1');
        $user_check = $this->db->fetch($result);

        if (empty($user_check) || empty($user_check['id'])) {
            return false;
        }
        !empty($password) ? $password_hashed = $this->encryptPassword($password) : $password_hashed = '';

        if (($user_check['password'] == $password_hashed)) {
            //echo "LLEGO " . $user_check['id'];
            return $user_check['id'];
        }

        return false;
    }

    public function setUser(int $user_id)
    {
        $_SESSION['uid'] = $user_id;
        $this->user = $this->getProfile($user_id);
        $this->updateSessionId();

        return true;
    }

    public function getHostsCatState()
    {
        return $this->categories_state;
    }

    private function loadUserHostCatsState()
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

    public function getHostsCats()
    {
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

    public function toggleHostsCat(int $id)
    {
        $this->categories_state[$id] = (!$this->categories_state[$id]) ? 1 : 0;
        $this->saveHostsCatsState();
    }

    public function saveHostsCatsState()
    {
        $json_cats_state = json_encode($this->categories_state);
        if (mb_strlen($json_cats_state, 'UTF-8') > 255) {
            Log::err('Max cats state reached');
            return false;
        }
        $this->setPref('hosts_cats_state', $json_cats_state);

        return true;
    }

    public function turnHostsCatsOff()
    {
        foreach ($this->categories_state as $key => $_) {
            $this->categories_state[$key] = 0;
        }
        $this->saveHostsCatsState();
    }

    public function turnHostsCatsOn()
    {
        foreach ($this->categories_state as $key => $_) {
            $this->categories_state[$key] = 1;
        }
        $this->saveHostsCatsState();
    }

    private function updateSessionId()
    {
        if (defined('PHP_VERSION_ID') && PHP_VERSION_ID >= 70300) {
            setcookie('sid', session_id(), [
                'expires' => time() + $this->cfg['sid_expire'],
                'secure' => true,
                'samesite' => 'lax',
            ]);
            setcookie('uid', $this->getId(), [
                'expires' => time() + $this->cfg['sid_expire'],
                'secure' => true,
                'samesite' => 'lax',
            ]);
            setcookie('username', $this->getUsername(), [
                'expires' => time() + (10 * 365 * 24 * 120),
                'secure' => true,
                'samesite' => 'lax',
            ]);
        } else {
            setcookie("sid", session_id(), time() + $this->cfg['sid_expire'], $this->cfg['rel_path']);
            setcookie("uid", $this->getId(), time() + $this->cfg['sid_expire'], $this->cfg['rel_path']);
            setcookie("username", $this->getUsername(), time() + (10 * 365 * 24 * 120), $this->cfg['rel_path']);
        }
        $new_sid = session_id();

        $this->db->update('users', ['sid' => $new_sid], ['id' => $this->getId()], 'LIMIT 1');
        $this->user['sid'] = $new_sid;
    }

    private function encryptPassword(string $password)
    {
        return sha1($password);
    }

    private function loadPrefs()
    {

        $prefs = [];
        $query = 'SELECT * FROM prefs WHERE uid = ' . $this->getId();
        $results = $this->db->query($query);

        $prefs = $this->db->fetchAll($results);
        if ($prefs && is_array($prefs)) {
            foreach ($prefs as $pref) {
                if (!empty($pref['pref_name']) && $pref['uid'] == 0) {
                    $this->prefs[$pref['pref_name']] = $pref['pref_value'];
                } elseif (!empty($pref['pref_name'])) {
                    $this->prefs[$pref['pref_name']] = $pref['pref_value'];
                }
            }
        }
    }

    public function getPref(string $r_key)
    {
        return isset($this->prefs[$r_key]) ? $this->prefs[$r_key] : false;
    }

    public function setPref(string $key, string $value)
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
}
