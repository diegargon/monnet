<?php
/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 * v1.0
 */

namespace App\Constants;

class LogLevel
{
    use ConstantsUtils;

    public const DEBUG = 7;
    public const INFO = 6;
    public const NOTICE = 5;
    public const WARNING = 4;
    public const ERROR = 3;
    public const CRITICAL = 2;
    public const ALERT = 1;
    public const EMERGENCY = 0;
}
