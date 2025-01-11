<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */

class LogLevel
{
    public const DEBUG = 7;
    public const INFO = 6;
    public const NOTICE = 5;
    public const WARNING = 4;
    public const ERROR = 3;
    public const CRITICAL = 2;
    public const ALERT = 1;
    public const EMERGENCY = 0;
}

class LogType
{
    public const DEFAULT = 0;
    public const EVENT = 1;
}