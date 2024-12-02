<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

/**
 * Clase config
 * ctype =
 *          0(string)
 *          1(int)
 *          2(bool)
 *          3(float)
 *          4(date)
 *          5(url)
 *          6(dropdown)
 *          7(password)
 *          8(email)
 *          10(array)
 *          11(array<array>)
 * ccat = 0 (hidde), 1 (misc) 2 (general)
 */
class Config
{
    /** @var AppContext $ctx */
    private AppContext $ctx;

    /** @var array<int|string, mixed> $cfg */
    private array $cfg;

    /** @var array<string, mixed> $modifiedKeys */
    private array $modifiedKeys = [];

    /**
     * Constructor de la clase Config.
     *
     * @param array<int|string, mixed> $cfg Configuraciones iniciales.
     * @param AppContext $ctx Contexto de la aplicación.
     */
    public function __construct(array $cfg, AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->cfg = $cfg;
        $this->loadFromDatabase();

        // Registrar la función de guardado al cierre
        register_shutdown_function([$this, 'saveChanges']);
    }

    /**
     * Carga las configuraciones desde la tabla `config` en la base de datos y las combina con las predefinidas.
     *
     * @return void
     */
    private function loadFromDatabase(): void
    {
        $db = $this->ctx->get('Mysql');

        $query = "SELECT ckey, cvalue FROM config";
        $result = $db->query($query);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $key = $row['ckey'];
                $value = json_decode($row['cvalue'], true);

                // Solo actualiza si no existe ya en la configuración
                if (!array_key_exists($key, $this->cfg)) {
                    $this->cfg[$key] = $value;
                }
            }
        }

        // Verificar claves predefinidas que no estén en la base de datos
        /*
        foreach ($this->cfg as $key => $value) {
            $escapedKey = $db->escape($key);

            // Si no existe en la base de datos, insertamos el valor predeterminado
            $query = "SELECT COUNT(*) AS count FROM config WHERE ckey = '$escapedKey'";
            $countResult = $db->query($query);
            $count = $countResult->fetch_assoc()['count'];

            if ($count == 0) {
                // Insertar el valor predeterminado
                $escapedValue = $db->escape(json_encode($value)); // Codificar como JSON
                $insertQuery = "INSERT INTO config (ckey, cvalue) VALUES ('$escapedKey', '$escapedValue')";
                $db->query($insertQuery);
            }
        }
        */
    }

    /**
     * Obtiene el valor de una clave de configuración.
     *
     * @param string|int $key Clave de configuración.
     * @return mixed Valor de la configuración o null si no existe.
     */
    public function get($key)
    {
        return $this->cfg[$key] ?? null;
    }

    /**
     * Obtiene todos los valores editables
     *
     * @return mixed Valor de la configuración o null si no existe.
     */

    public function getAllEditable()
    {
        $db = $this->ctx->get('Mysql');

        $query = "SELECT ckey, cvalue, ctype, ccat, cdesc FROM config WHERE ccat > 0";
        $result = $db->query($query);

        $config = [];

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $row['cvalue']  = json_decode($row['cvalue'], true);
                $config[] = $row;
            }
        }
        return $config;
    }

    /**
     * Establece un valor en la configuración.
     *
     * @param string|int $key Clave de configuración.
     * @param mixed $value Valor de configuración.
     * @return void
     */
    public function set($key, $value): void
    {
        if (isset($this->cfg[$key]) && $this->cfg[$key] !== $value) {
            $this->cfg[$key] = $value;
            $this->modifiedKeys[$key] = $value;
        }
    }

    public function setMultiple(array $values): void
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value);
        }
    }
    /**
     * Guarda los cambios en la base de datos.
     *
     * @return void
     */
    public function saveChanges(): void
    {
        if (empty($this->modifiedKeys)) {
            return;
        }

        $db = $this->ctx->get('Mysql');
        $values = [];

        foreach ($this->modifiedKeys as $key => $value) {
            // Escapar las claves y los valores para evitar inyecciones SQL
            $escapedKey = $db->escape($key);
            $escapedValue = $db->escape(json_encode($value)); // Convertir a JSON antes de guardar

            $values[] = "('$escapedKey', '$escapedValue')";
        }

        // Combina todas las filas en una sola consulta para eficiencia
        $query = "INSERT INTO config (ckey, cvalue) VALUES " . implode(', ', $values) . "
              ON DUPLICATE KEY UPDATE cvalue = VALUES(cvalue)";

        $db->query($query);

        // Limpia el buffer de modificaciones
        $this->modifiedKeys = [];
    }
}
