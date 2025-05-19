<?php

/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Core;

class AppContext
{
    /**
     * @var array<string,mixed> $services Registered services
     */
    private array $services = [];

    /**
     * @var array<string,mixed> $cfg Config data.
     */
    private array $cfg = [];

    /**
     * @var array<string,string> $lng Language data. TODO migrate to Lang class.
     */
    private array $lng = [];

    /**
     * @var array<int, string> $resolving - cyclic dependency detection.
     */
    private array $resolving = [];

    public function __construct()
    {
        spl_autoload_register([$this, 'autoload']);
    }

    /**
     * @param array<string,string> $lng TODO remove when Lang class is used.
     */
    public function setLang(array &$lng): void
    {
        $this->lng = &$lng;
    }

    /**
     * @param array<string,mixed> $cfg
     */
    public function setCfg(array &$cfg): void
    {
        $this->cfg = &$cfg;
    }

    /**
     * @param array<string,mixed> $cfg_db
     */
    public function setCfgDb(array $cfg_db): void
    {
        $this->cfg['dbtype'] = $cfg_db['dbtype'];
        $this->cfg['dbhost'] = $cfg_db['dbhost'];
        $this->cfg['dbname'] = $cfg_db['dbname'];
        $this->cfg['dbuser'] = $cfg_db['dbuser'];
        $this->cfg['dbpassword'] = $cfg_db['dbpassword'];
        $this->cfg['dbprefix'] = $cfg_db['dbprefix'];
        $this->cfg['dbcharset'] = $cfg_db['dbcharset'];
    }

    /**
     * Autoload method Legacy. TODO: ¿composer?
     */
    public function autoload(string $class_name): void
    {
        $file_path = 'class/' . $class_name . '.php';

        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }

    /**
     * Register a service in the context.
     */
    public function set(string $name, mixed $service = null): mixed
    {
        // Detect cyclic dependency
        if (in_array($name, $this->resolving, true)) {
            $cycle = implode(' -> ', array_merge($this->resolving, [$name]));
            throw new \RuntimeException("Cyclic dependency detected: $cycle");
        }

        $this->resolving[] = $name;
        try {
            if ($service && is_object($service)) {
                $this->services[$name] = $service;
                return $service;
            }

            if (class_exists($name)) {
                $this->services[$name] = new $name($this);
                return $this->services[$name];
            }

            if ($this->existsFileSrv($name)) {
                require_once 'class/' . $name . '.php';
                $this->services[$name] = new $name($this);
                return $this->services[$name];
            }

            throw new \InvalidArgumentException("Invalid service provided: $name");
        } finally {
            array_pop($this->resolving);
        }
    }

    /**
     * Retrieve a service by name (modern or legacy).
     */
    public function &get(string $name): mixed
    {
        if ($name === 'cfg') {
            return $this->cfg;
        }

        if ($name === 'lng') {
            return $this->lng;
        }

        if (!isset($this->services[$name])) {
            if (class_exists($name)) {
                $this->set($name);
            } elseif ($this->existsFileSrv($name)) {
                $this->set($name);
            }
        }

        if (isset($this->services[$name])) {
            return $this->services[$name];
        }

        $null = null;
        return $null;
    }

    /**
     * Check if a service is registered.
     */
    public function has(string $name): bool
    {
        return isset($this->services[$name]);
    }

    /**
     * Check if a legacy class file exists.
     */
    public function existsFileSrv(string $name): bool
    {
        return file_exists('class/' . $name . '.php');
    }
}
