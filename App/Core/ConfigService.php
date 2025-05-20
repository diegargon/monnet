<?php
/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */


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
namespace App\Core;

use App\Core\AppContext;
use App\Core\DBManager;
use App\Models\ConfigModel;

class ConfigService
{
    private AppContext $ctx;
    private ConfigModel $model;
    private array $cfg = [];
    private array $modifiedKeys = [];
    private DBManager $db;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->db = new DBManager($ctx);
        $this->model = new ConfigModel($this->db);
        register_shutdown_function([$this, 'saveChanges']);
    }

    /**
     * Inicializa la configuración con un array base.
     */
    public function init(array $cfg): void
    {
        foreach ($cfg as $cfg_key => $cfg_value) {
            $this->cfg[$cfg_key]['value'] = $cfg_value;
        }
        $this->loadFromDatabase();
    }

    /**
     * Carga configuraciones desde la base de datos y las fusiona con las existentes.
     */
    private function loadFromDatabase(): void
    {
        $rows = $this->model->getAll();
        foreach ($rows as $row) {
            $key = $row['ckey'];
            $value = $this->parseRowValue($row['cvalue'], (int)$row['ctype']);
            if (
                (isset($row['ccat']) && $row['ccat'] > 0) ||
                !isset($this->cfg[$key])
            ) {
                $this->cfg[$key]['value'] = $value;
                $this->cfg[$key]['ctype'] = (int)$row['ctype'];
                $this->cfg[$key]['ccat'] = $row['ccat'];
            }
        }
    }

    /**
     * Get key value
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (empty($this->cfg[$key]['value'])) {
            return $default !== 'None' ? $default : null;
        }
        if (isset($this->cfg[$key]['ctype']) && $this->cfg[$key]['ctype'] == 6) {
            foreach ($this->cfg[$key]['value'] as $kvalue => $vvalue) {
                if ($vvalue) {
                    return $kvalue;
                }
            }
        }
        return $this->cfg[$key]['value'];
    }

    /**
     * Get all config
     * @return array<string, mixed>
     */
    public function getAll(): array
    {
        return $this->cfg;
    }

    /**
     * Get all user editable config
     * @return array<string, mixed>
     */
    public function getAllEditable(): array
    {
        $lng = $this->ctx->get('lng');
        $rows = $this->model->getAllEditable();
        $config = [];
        foreach ($rows as $row) {
            $row['cvalue'] = $this->parseRowValue($row['cvalue'], (int)$row['ctype']);
            if (empty($row['cdesc'])) {
                $row['cdisplay'] = ucfirst($row['ckey']);
            } else {
                if (substr($row['cdesc'], 0, 2) == 'L_' && isset($lng[$row['cdesc']])) {
                    $row['cdisplay'] = $lng[$row['cdesc']];
                } else {
                    $row['cdisplay'] = ucfirst($row['ckey']);
                }
            }
            $config[] = $row;
        }
        return $config;
    }

    /**
     * Set config value
     * @param string $key
     * @param mixed $value
     * @param int $force_save
     * @return int
     */
    public function set(string $key, mixed $value, int $force_save = 0): int
    {
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
            $this->cfg[$key]['value'] = $value;
        }
        if ($force_save) {
            $this->saveChanges();
        }
        return 0;
    }

    /**
     * Set multiple values
     * @param array $values
     * @return int Number of changes
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
     * Save Changes
     * @return void
     */
    public function saveChanges(): void
    {
        if (empty($this->modifiedKeys)) {
            return;
        }
        $this->model->saveChanges($this->modifiedKeys);
        $this->modifiedKeys = [];
    }

    /**
     * Parse values
     * @param string|null $value
     * @param int $type
     * @return mixed
     * @throws \InvalidArgumentException
     */
    private function parseRowValue(?string $value, int $type = 0): mixed
    {
        if ($value === null) {
            return null;
        }
        $decodedValue = json_decode($value, true);
        switch ($type) {
            case 0: return (string)$decodedValue;
            case 1: return (int)$decodedValue;
            case 2: return (bool)$decodedValue;
            case 3: return (float)$decodedValue;
            case 4:
                $timestamp = strtotime($decodedValue);
                return ($timestamp !== false) ? date('Y-m-d H:i:s', $timestamp) : null;
            case 5: return (string)$decodedValue;
            case 6:
                if ((!is_array($decodedValue) && $json = $this->isJson($decodedValue))) {
                    $decodedValue = $json;
                }
                return $decodedValue;
            case 7: return $decodedValue;
            case 10: return $decodedValue;
            default:
                throw new \InvalidArgumentException("Unsupported type: $type");
        }
    }

    /**
     * Check and decode json
     * @param string $string
     * @return array|false
     */
    private function isJson(string $string): array|false
    {
        $data = json_decode($string, true);
        return (json_last_error() == JSON_ERROR_NONE) ? $data : false;
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
}
