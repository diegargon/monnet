<?php

/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Core;

class ModuleManager
{
    private array $modules = [];
    private array $hooks = [];
    // TODO: To DB
    private array $activeModules = [
        'weather_widget'
    ];

    public function __construct()
    {
        foreach ($this->getActiveModules() as $mod) {
            $modPath = dirname(__DIR__, 2) . "/modules/$mod/hooks.php";
            if (file_exists($modPath)) {
                $modHooks = require $modPath;
                foreach ($modHooks as $hook => $fn) {
                    $this->hooks[$hook][] = $fn;
                }
                $this->modules[] = $mod;
            }
        }
    }

    /**
     * Devuelve los módulos activos (fácil de cambiar a DB en el futuro)
     */
    public function getActiveModules(): array
    {
        // En el futuro: return $this->loadFromDb();
        return $this->activeModules;
    }

    /**
     * Ejecuta todos los hooks registrados para un evento.
     * @param string $hook
     * @param array $args
     * @return mixed|null
     */
    public function runHook(string $hook, array $args = [])
    {
        $results = [];
        if (!empty($this->hooks[$hook])) {
            foreach ($this->hooks[$hook] as $fn) {
                $result = $fn(...$args); // Esto permite pasar referencias
                if ($result !== null) {
                    $results[] = $result;
                }
            }
        }
        if (count($results) === 1) return $results[0];
        if (count($results) > 1) {
            // Mezclar arrays si hay más de un resultado
            $final = [];
            foreach ($results as $res) {
                if (is_array($res)) {
                    $final = array_merge_recursive($final, $res);
                }
            }
            return $final;
        }
        return null;
    }

    public function installModule(string $mod, $ctx): void
    {
        $modPath = dirname(__DIR__, 2) . "/modules/$mod/hooks.php";
        if (file_exists($modPath)) {
            $modHooks = require $modPath;
            if (isset($modHooks['onInstall'])) {
                $modHooks['onInstall']($ctx);
            }
            // Marcar como activo (en DB o config)
            $this->activeModules[] = $mod;
        }
    }

    public function uninstallModule(string $mod, $ctx): void
    {
        $modPath = dirname(__DIR__, 2) . "/modules/$mod/hooks.php";
        if (file_exists($modPath)) {
            $modHooks = require $modPath;
            if (isset($modHooks['onUninstall'])) {
                $modHooks['onUninstall']($ctx);
            }
            // Marcar como inactivo (en DB o config)
            $this->activeModules = array_diff($this->activeModules, [$mod]);
        }
    }
}
