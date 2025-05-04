<?php

/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Core;

use InvalidArgumentException;

class NewAppContext
{
    /** @var array<string,object> */
    private static array $services = [];

    /** @var array<string,string> */
    private static array $lng = [];

    /** @var self|null */
    private static ?self $instance = null;

    /**
     * Devuelve la instancia única de AppContext (Singleton).
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Registra un servicio en el contexto.
     *
     * @param string $className Nombre completo de la clase (con namespace).
     * @param object|null $service Instancia de la clase si ya existe.
     * @return object
     */
    public function set(string $className, ?object $service = null): object
    {
        if ($service === null) {
            if (!class_exists($className)) {
                throw new InvalidArgumentException("Clase no encontrada: $className");
            }
            $service = new $className();
        }

        self::$services[$className] = $service;
        return $service;
    }

    /**
     * Obtiene una instancia de una clase. Si no existe, la crea.
     *
     * @param string $className Nombre completo de la clase.
     * @return object
     */
    public function get(string $className): object
    {
        if (!isset(self::$services[$className])) {
            return $this->set($className);
        }

        return self::$services[$className];
    }

    /**
     * Verifica si un servicio está registrado.
     *
     * @param string $className Nombre de la clase.
     * @return bool
     */
    public function has(string $className): bool
    {
        return isset(self::$services[$className]);
    }

    /**
     * Guarda datos de lenguaje.
     *
     * @param array<string,string> $lang
     */
    public function setLang(array $lang): void
    {
        self::$lng = $lang;
    }

    /**
     * Obtiene datos de lenguaje.
     *
     * @return array<string,string>
     */
    public function getLang(): array
    {
        return self::$lng;
    }
}
