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

        $query = "SELECT ckey, cvalue, ctype FROM config";
        $result = $db->query($query);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $key = $row['ckey'];
                $value = $this->parseRowValue($row['cvalue'], (int) $row['ctype']);

                /*
                 * Only update values with category set to nondefault
                 * Mean db cfg only have precedence if ccat > 0
                 */
                if (
                    isset($row['ccat']) &&
                    $row['ccat'] > 0
                ) {
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
        $lng = $this->ctx->get('lng');

        $query = "SELECT ckey, cvalue, ctype, ccat, cdesc FROM config WHERE ccat > 0";
        $result = $db->query($query);

        $config = [];

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $row['cvalue'] = $this->parseRowValue($row['cvalue'], (int) $row['ctype']);
                if(empty($row['cdesc'])) :
                    $row['cdisplay'] = ucfirst($row['ckey']);
                else :
                    if(
                        substr($row['cdesc'], 0, 2) == 'L_'
                        && isset($lng[$row['cdesc']])
                    ) {
                        $row['cdisplay'] = $lng[$row['cdesc']];
                    } else {
                        $row['cdisplay'] = ucfirst($row['ckey']);
                    }
                endif;

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
     * @return int 1 if field change
     */
    public function set($key, $value): int
    {

        if (isset($this->cfg[$key]) && $this->cfg[$key] !== $value) {
            $this->cfg[$key] = $value;
            $this->modifiedKeys[$key] = $value;
            return 1;
        }

        return 0;
    }

    /**
     *
     * @param array $values
     * @return int
     */
    public function setMultiple(array $values): int
    {
        $changes = 0;

        foreach ($values as $key => $value) {
            $this->set($key, $value) && $changes++;
        }

        return $changes;
    }
    /**
     * Guarda los cambios en la base de datos.
     *
     * @return void
     */
    private function saveChanges(): void
    {
        if (empty($this->modifiedKeys)) {
            return;
        }

        $db = $this->ctx->get('Mysql');
        $values = [];

        foreach ($this->modifiedKeys as $key => $value) {
            $escapedKey = $db->escape($key);
            $escapedValue = $db->escape(json_encode($value));

            $values[] = "('$escapedKey', '$escapedValue')";
        }

        // Combina todas las filas en una sola consulta
        $query = "INSERT INTO config (ckey, cvalue) VALUES " . implode(', ', $values) . "
              ON DUPLICATE KEY UPDATE cvalue = VALUES(cvalue)";

        $db->query($query);

        // Limpia el buffer de modificaciones
        $this->modifiedKeys = [];
    }

    /**
     * Parses a JSON-encoded database value based on its expected type.
     *
     * @param string|null $value JSON-encoded value from the database (nullable).
     * @param int $type Expected type as per `ctype`:
     *                  0 = string, 1 = int, 2 = bool, 3 = float, 4 = date.
     * @return mixed The parsed and type-casted value.
     */
    private function parseRowValue(?string $value, int $type = 0): mixed
    {
        if ($value === null) {
            return null;
        }

        $decodedValue = json_decode($value, true);

        switch ($type) {
            case 0: // string
                return (string)$decodedValue;
            case 1: // int
                return (int)$decodedValue;
            case 2: // bool
                return (bool)$decodedValue;
            case 3: // float
                return (float)$decodedValue;
            case 4: // date
                return (strtotime($decodedValue) !== false) ? $decodedValue : null;
            default:
                throw new InvalidArgumentException("Unsupported type: $type");
        }
    }
}
