<?php

/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

!defined('IN_WEB') ? exit : true;
/**
 * Config class
 * ctype =
 *      0 (string)
 *      1 (int)
 *      2 (bool)
 *      3 (float)
 *      4 (datetime)
 *      5 (url)
 *      6 (dropdown select) (json object) {"val1"=> 1, "val2=>0} (1 selected)
 *      7 (password) (TODO)
 *      8 (email) (Not yet)
 *      10 (text/textbox)
 *
 *
 * ('keyname', JSON_QUOTE('key_value'), ctype, ccat, cdesc, cuid=0);
 *
 * ccat
 *      0 (hidden)
 *      1 (general)
 *      4 (Gateway)
 *      5 (Format)
 *      101 (mail)
 *      102 Ansible
 *      103 (Agent)
 *      104 (Purge)
 *      105 (Logging)
 *      106 Network
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
     * Constructor of the Config class.
     *
     * @param AppContext $ctx Application context.
     */
    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;

        // Register the save function on shutdown
        register_shutdown_function([$this, 'saveChanges']);
    }

    /**
     *
     * @param array<int|string, mixed> $cfg Initial configurations.
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
     * Loads configurations from the `config` table in the database and merges them with predefined ones.
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

                // DB Precedence only for keys with cat > 0
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
    }

    /**
     * Gets the value of a configuration key.
     *
     * @param string|int $key Configuration key.
     * @return mixed Configuration value or null if it does not exist.
     */
    public function get(string $key, mixed $default = 'None'): mixed
    {
        if (empty($this->cfg[$key]['value'])) {
            if ($default !== 'None') {
                return $default;
            }
            return null;
        }
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
     * @return array<string, mixed>
     */
    public function getAll(): mixed
    {
        return $this->cfg;
    }
    /**
     * Gets all editable values.
     *
     * @return mixed Configuration value or null if it does not exist.
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
     * Sets a value in the configuration.
     *
     * @param mixed $key Configuration key.
     * @param mixed $value Configuration value.
     * @param int $force_save
     * @return int 1 if field changed
     */
    public function set($key, $value, int $force_save = 0): int
    {
        // If ctype exists, it is a database config key
        if (isset($this->cfg[$key]['ctype'])) {
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
            } elseif ($config['ctype'] == 10) {
                $this->modifiedKeys[$key]['value'] = base64_encode($value);
            } elseif ($config['value'] !== $value) {
                $config['value'] = $value;
                $this->modifiedKeys[$key]['value'] = $value;
                return 1;
            }
        } else {
            /* No database value, avoid saving to database */
            $this->cfg['key']['value'] = $value;
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
     * Saves changes to the database. Called by register shutdown.
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

        // Combine all rows into a single query
        $query = "INSERT INTO config (ckey, cvalue) VALUES " . implode(', ', $values) . "
              ON DUPLICATE KEY UPDATE cvalue = VALUES(cvalue)";

        $db->query($query);

        // Clear the modification buffer
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
            case 4: // datetime
                $timestamp = strtotime($decodedValue);
                return ($timestamp !== false) ? date('Y-m-d H:i:s', $timestamp) : null;
            case 5: // url
                //TODO Filter?
                return (string) $decodedValue;
            case 6: // json object VALUE=1 (1 selected)
                if ((!is_array($decodedValue) && $json = $this->isJson($decodedValue))) :
                    $decodedValue = $json;
                endif;
                return $decodedValue;
            case 7: //password
                return $decodedValue;
            case 10: //Textbox
                return $decodedValue;
            default:
                throw new InvalidArgumentException("Unsupported type: $type");
        }
    }

    /**
     * Load file configuration from a JSON file. Key=>Value
     *
     * @param string $path Path to the JSON config file.
     * @return array Loaded configuration as an associative array.
     * @throws RuntimeException If the file can't be read or parsed.
     */
    private function loadFileConfig(string $path): array
    {
        if (!file_exists($path)) {
            throw new RuntimeException("Config file not found: $path");
        }

        $jsonContent = file_get_contents($path);
        if ($jsonContent === false) {
            throw new RuntimeException("Unable to read config file: $path");
        }

        $config = json_decode($jsonContent, true);
        if ($config === null) {
            throw new RuntimeException("Invalid JSON in config file: $path");
        }

        foreach ($config as $ckey => $cvalue) {
            $this->cfg[$ckey] = $cvalue;
        }

        return $config;
    }
    /**
     * Verifica si un valor es JSON válido y lo decodifica.
     */
    private function isJson($string)
    {
        $data = json_decode($string, true);
        return (json_last_error() == JSON_ERROR_NONE) ? $data : false;
    }
}
