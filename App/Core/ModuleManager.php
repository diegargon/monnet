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
    // TODO: Move to DB
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
     * Returns the active modules (easy to change to DB in the future)
     * @return array
     */
    public function getActiveModules(): array
    {
        // In the future: return $this->loadFromDb();
        return $this->activeModules;
    }

    /**
     * Executes all registered hooks for an event.
     * @param string $hook The hook/event name
     * @param array $args Arguments to pass to the hook callbacks
     * @return mixed|null Returns the result(s) of the hooks, or null if none
     */
    public function runHook(string $hook, array $args = [])
    {
        $results = [];
        if (!empty($this->hooks[$hook])) {
            foreach ($this->hooks[$hook] as $fn) {
                $result = $fn(...$args); // Allows passing by reference
                if ($result !== null) {
                    $results[] = $result;
                }
            }
        }
        if (count($results) === 1) return $results[0];
        if (count($results) > 1) {
            // Merge arrays if there is more than one result
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

    /**
     * Installs a module and marks it as active.
     * @param string $mod Module name
     * @param mixed $ctx Context or parameters for installation
     * @return void
     */
    public function installModule(string $mod, $ctx): void
    {
        $modPath = dirname(__DIR__, 2) . "/modules/$mod/hooks.php";
        if (file_exists($modPath)) {
            $modHooks = require $modPath;
            if (isset($modHooks['onInstall'])) {
                $modHooks['onInstall']($ctx);
            }
            // Mark as active (in DB or config)
            $this->activeModules[] = $mod;
        }
    }

    /**
     * Uninstalls a module and marks it as inactive.
     * @param string $mod Module name
     * @param mixed $ctx Context or parameters for uninstallation
     * @return void
     */
    public function uninstallModule(string $mod, $ctx): void
    {
        $modPath = dirname(__DIR__, 2) . "/modules/$mod/hooks.php";
        if (file_exists($modPath)) {
            $modHooks = require $modPath;
            if (isset($modHooks['onUninstall'])) {
                $modHooks['onUninstall']($ctx);
            }
            // Mark as inactive (in DB or config)
            $this->activeModules = array_diff($this->activeModules, [$mod]);
        }
    }
}
