<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
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
 *          6 (dropdown select) (json object) {"val1"=> 1, "val2=>0} (1 selected)
 *          7(password
 *          8(email) ?
 *
 * ccat = 0 (hidden), 1 (general) 101 (mail) 102 Ansible
 * ('keyname', JSON_QUOTE('key_value'), ctype, ccat, cdesc, cuid=0);
 */
class Config
{
    /** @var AppContext $ctx */
    private AppContext $ctx;

    /** @var array<int|string, mixed> $cfg */
    private array $cfg;

    /** @var array<int|string, mixed> $modifiedKeys */
    private array $modifiedKeys = [];

    /**
     * Constructor de la clase Config.
     *
     * @param AppContext $ctx Contexto de la aplicación.
     */
    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;

        // Registrar la función de guardado al cierre
        register_shutdown_function([$this, 'saveChanges']);
    }

    /**
     *
     * @param array<int|string, mixed> $cfg Configuraciones iniciales.
     * @return void
     */
    public function init(array $cfg): void
    {
        foreach ($cfg as $cfg_key => $cfg_value) :
            $this->cfg[$cfg_key]['value'] = $cfg_value;
        endforeach;

        $this->loadFromDatabase();
    }

    /**
     * Carga las configuraciones desde la tabla `config` en la base de datos y las combina con las predefinidas.
     *
     * @return void
     */
    private function loadFromDatabase(): void
    {
        $db = $this->ctx->get('Mysql');

        $query = "SELECT ckey, cvalue, ctype, ccat FROM config";
        $result = $db->query($query);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $key = $row['ckey'];
                $value = $this->parseRowValue($row['cvalue'], (int) $row['ctype']);

                /*
                 * Only update values with category set to nondefault
                 * Mean db cfg only have precedence if ccat > 0
                 * exception to ccat > 0  if key not exists in cfg
                 */
                if (
                    (isset($row['ccat']) && $row['ccat'] > 0) ||
                    !isset($this->cfg[$key])
                ) {
                    $this->cfg[$key]['value'] = $value;
                    $this->cfg[$key]['ctype'] = (int) $row['ctype'];
                    $this->cfg[$key]['ccat'] = $row['ccat'];
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
        if (!isset($this->cfg[$key]['value'])) :
            return null;
        endif;
        if (isset($this->cfg[$key]['ctype']) && $this->cfg[$key]['ctype'] == 6) {
            foreach ($this->cfg[$key]['value'] as $kvalue => $vvalue) {
                if ($vvalue) :
                    return $kvalue;
                endif;
            }
        }
        return $this->cfg[$key]['value'];
    }

    /**
     *
     * @return array<int|string, mixed>
     */
    public function getAll()
    {
        return $this->cfg;
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
                if (empty($row['cdesc'])) :
                    $row['cdisplay'] = ucfirst($row['ckey']);
                else :
                    if (
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
     * @param mixed $key Clave de configuración.
     * @param mixed $value Valor de configuración.
     * @param int force_save
     * @return int 1 if field change
     */
    public function set($key, $value, int $force_save = 0): int
    {
        if (isset($this->cfg[$key])) {
            $config = &$this->cfg[$key];

            if ($config['ctype'] == 6) {
                foreach ($config['value'] as $key_value => &$value_value) {
                    if ($key_value == $value) {
                        if ($value_value != 1) {
                            $config['value'][$key_value] = 1;
                        }
                    } else {
                        $value_value = 0;
                    }

                    unset($value_value);
                }
                $this->modifiedKeys[$key]['value'] = $config['value'];
                return 1;
            } elseif ($config['value'] !== $value) {
                $config['value'] = $value;
                $this->modifiedKeys[$key]['value'] = $value;
                return 1;
            }
        }
        if ($force_save) :
            $this->saveChanges();
        endif;

        return 0;
    }

    /**
     *
     * @param array<mixed,mixed> $values
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
     * Guarda los cambios en la base de datos. Called by register shudown
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
            $escapedValue = json_encode($value['value'], JSON_HEX_APOS | JSON_HEX_QUOT);

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
            case 5: // url
                //TODO Filter?
                return (string) $decodedValue;
            case 6: // json object VALUE=1 (1 selected)
                if ((!is_array($decodedValue) && $json = isJson($decodedValue))) :
                    $decodedValue = $json;
                endif;
                return $decodedValue;
            case 7: //password
                return $decodedValue;
            default:
                throw new InvalidArgumentException("Unsupported type: $type");
        }
    }
}
