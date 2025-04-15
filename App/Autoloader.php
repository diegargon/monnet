<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App;

class Autoloader
{
    public static function register(): void
    {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    /**
     *
     * @param string $class
     * @return void
     */
    private static function autoload(string $class): void
    {
        $class = str_replace(__NAMESPACE__ . '\\', '', $class);
        $file = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
}
