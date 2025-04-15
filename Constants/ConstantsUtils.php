<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

trait ConstantsUtils
{
    /**
     *
     * @return array<string, int>
     */
    public static function getConstants(): array
    {
        return (new ReflectionClass(self::class))->getConstants();
    }

    /**
     *
     * @param int $value
     * @return string|null
     */
    public static function getName(int $value): ?string
    {
        $constants = (new ReflectionClass(self::class))->getConstants();
        $flipped = array_flip($constants);

        return $flipped[$value] ?? null;
    }
}
