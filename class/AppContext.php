<?php

/**
 * Class AppContext
 *
 */
class AppContext
{
    /**
     * @var array $services Servicios registrados en el contexto.
     */
    private array $services = [];

    /**
     * @var array $cfg Datos config
     */
    private array $cfg = [];

    /**
     * @var array $lng  Datos Language TODO migrar a Lang
     */
    private array $lng = [];

    /**
     * AppContext constructor.
     *
     * @param array $cfg configuracion
     */
    public function __construct(array $cfg)
    {
        $this->cfg = $cfg;
        spl_autoload_register(array($this, 'autoload'));
    }

    /**
     * @param array $lng TODO remove when change to class LANG
     */
    public function setLang(array $lng)
    {
        $this->lng = $lng;
    }

    /**
     * autoload class method TODO: Change autoload
     *
     * @param string $class_name
     *
     * @return void
     */
    public function autoload(string $class_name): void
    {
        $file_path = 'class/' . $class_name . '.php';

        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }

    /**
     * Registra un servicio en el contexto.
     *
     * @param string $name Nombre del servicio.
     * @param mixed $service Instancia del servicio a registrar.
     *
     * @return mixed devuelve la classe instanciada
     */
    public function set(string $name, mixed $service = null): mixed
    {
        if (
            $service && is_object($service) &&
            $this->existsFileSrv($name)
        ) {
            $this->services[$name] = $service;
            return $service;
        } elseif ($this->existsFileSrv($name) && $service === null) {
            $this->services[$name] = new $name($this);
            return $this->services[$name];
        }

        throw new InvalidArgumentException("Invalid service provided: $name");
    }

    /**
     * Obtiene un servicio por nombre.
     *
     * @param string $name Nombre del servicio.
     *
     * @return mixed La instancia del servicio registrado.

     */
    public function get(string $name): mixed
    {
        //TODO Arreglar esto chapuza temporal
        if ($name === 'cfg') {
            return $this->cfg;
        }
        if ($name === 'lng') {
            return $this->lng;
        }
        //END Chapuza

        if (
            !isset($this->services[$name]) &&
            $this->existsFileSrv($name)
        ) {
            $this->set($name);
        }

        if ($this->services[$name]) {
            return $this->services[$name];
        } else {
            return false;
        }
    }

    /**
     * Verifica si un servicio está registrado en el contexto.
     *
     * @param string $name Nombre del servicio.
     *
     * @return bool True si el servicio está registrado, false en caso contrario.
     */
    public function has(string $name): bool
    {
        return isset($this->services[$name]);
    }

    public function existsFileSrv(string $name): bool
    {
        $file_path = 'class/' . $name . '.php';

        if (file_exists($file_path)) {
            return true;
        }
        return false;
    }
}
