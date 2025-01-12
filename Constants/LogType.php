<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */

class LogType
{
    public const DEFAULT = 0;
    public const EVENT = 1;
    public const EVENT_WARN = 2;
    public const EVENT_ALERT = 3;

    public static function getName(int $value): ?string
    {
        $constants = (new ReflectionClass(self::class))->getConstants();
        $flipped = array_flip($constants);

        return $flipped[$value] ?? null;
    }
}