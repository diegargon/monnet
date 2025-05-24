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

use App\Services\LogSystemService;
use App\Services\Filter;
use App\Services\DateTimeService;

use App\Models\UserModel;
use App\Models\SessionsModel;

class UserSession
{

    private UserModel $userModel;
    private SessionsModel $sessionModel;
    private const REMEMBER_COOKIE = 'sid';
    private ConfigService $ncfg;
    private array $user = [];
    private int $session_expire;
    private AppContext $ctx;
    private LogSystemService $logSystem;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $db = new DBManager($ctx);
        $this->ncfg = $ctx->get(ConfigService::class);
        $this->session_expire = (int) $this->ncfg->get('sid_expire');
        $this->userModel = new UserModel($db);
        $this->logSystem = new LogSystemService($ctx);
        $this->sessionModel = new SessionsModel($db);
    }

    public function set(array $user): void
    {
        $user['logged_in'] = true;
        $_SESSION['uid'] = $user['id'];
        $this->user = $user;
        $this->rememberSession();
    }

    public function isLoggedIn(): bool
    {
        if (isset($_COOKIE['PHPSESSID']) && $_COOKIE['PHPSESSID'] === session_id()) {
            return true;
        } else {
            return false;
        }

        return false;
    }

    public function getCurrentUser(): array
    {
        if (!$this->user) {
            return [];
        }

        return $this->user;
    }

    public function getCurrentUserId(): int|bool
    {
        if ($this->user && isset($this->user['id'])) {
            return $this->user['id'];
        }

        return false;
    }

    public function AutoLogin(): bool
    {
        # Check if user is logged in with PHPSESSID
        if ($this->isLoggedIn() and isset($_SESSION['uid']) and is_numeric($_SESSION['uid'])) {
            $user = $this->userModel->getById($_SESSION['uid']);
            if ($user) {
                $this->set($user);
                return true;
            }
        }

        # Remember me: Check cookies and compare with DB
        $sid = Filter::cookieSid(self::REMEMBER_COOKIE);
        $uid = Filter::cookieInt('uid');
        //print("sid: $sid, uid: $uid <br>/>");
        if ($sid && $uid) {
            if ($this->sessionModel->sidExists($uid, $sid)) {
                $user = $this->userModel->getById($uid);
                if ($user) {
                    $this->set($user);
                    return true;
                }
            }
            $this->logout();
        }

        return false;
    }

    public function createDBSession(int $userId): string
    {
        $sid = session_id();

        $date_now = DateTimeService::dateNow();
        $sessionData = [
            'user_id' => $userId,
            'sid' => $sid,
            'created' => $date_now,
            'expire' => DateTimeService::formatTimestamp(time() + $this->session_expire, 'UTC'),
            'last_active' => $date_now,
        ];

        $this->sessionModel->create($sessionData);

        return $sid;
    }

    public function updateSession(int $userId, bool $renew = false): string
    {
        if ($renew) {
            $sid = session_regenerate_id(true);
        } else {
            $sid = session_id();
        }
        $sessionData = [
            'last_active' => DateTimeService::dateNow(),
            'expire' => DateTimeService::formatTimestamp(time() + $this->session_expire, 'UTC'),
            'sid' => $sid
        ];

        $this->sessionModel->update($userId, $sessionData);

        return $sid;
    }

    public function logout(): void
    {
        $clear_data = [];
        session_destroy();

        if (isset($_COOKIE[self::REMEMBER_COOKIE])) {
            $clear_data['sid'] = $_COOKIE[self::REMEMBER_COOKIE];
            setcookie(self::REMEMBER_COOKIE, '', time() - 3600, '/');
        }
        if (isset($_COOKIE['uid'])) {
            $clear_data['uid'] = $_COOKIE['uid'];
            setcookie('uid', '', time() - 3600, '/');
        }
        if(!empty($clear_data)) {
            $this->sessionModel->clearSession($clear_data);
        }
        $this->user = [];
    }

    public function rememberSession(): void
    {
        $sId = session_id();
        $uId = $this->user['id'];

        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath !== '/' && substr($basePath, -1) !== '/') {
            $basePath .= '/';
        }
        $rel_path = $basePath;

        if (defined('PHP_VERSION_ID') && PHP_VERSION_ID >= 70300) {
            setcookie('uid', $uId, [
                'expires' => time() + $this->session_expire,
                'secure' => true,
                'samesite' => 'lax',
            ]);
            setcookie(self::REMEMBER_COOKIE, $sId, [
                'expires' => time() + $this->session_expire,
                'secure' => true,
                'samesite' => 'lax',
            ]);
        } else {
            setcookie(
                'uid',
                $uId,
                time() + $this->session_expire,
                $rel_path,
            );
            setcookie(
                self::REMEMBER_COOKIE,
                $sId,
                time() + $this->session_expire,
                $rel_path,
            );
        }
    }
}
