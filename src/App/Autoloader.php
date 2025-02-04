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
        spl_autoload_register([self::class, 'autoload']);
    }

    private static function autoload(string $class_name): void
    {
        // Convertir el namespace en una ruta de archivo
        $file_path = __DIR__ . '/' . str_replace('\\', '/', $class_name) . '.php';

        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }
}
