<?php

/**
 *  Plugins
 * 
 *  Plugins load/manager
 * 
 *  @author diego////@////envigo.net
 *  @package ProjectBase
 *  @subpackage CORE
 *  @copyright Copyright @ 2016 - 2021 Diego Garcia (diego////@////envigo.net)
 */
!defined('IN_WEB') ? exit : true;

/**
 * Class Plugins
 */
class Plugins {

    /**
     * debug or not 0/1
     * @var int 
     */
    private $debug;

    /**
     * hold disk scan plugins
     * @var array
     */
    private $registered_plugins = [];

    /**
     * when load a plugin hold list provide/version
     * @var array 
     */
    private $depends_provide = [];

    /**
     * hold db list plugins
     * @var array
     */
    private $plugins_db = [];

    /**
     * Init plugins with autostart 1 and depends with autostart 0/1
     * @global debug $debug
     * @global array $cfg
     */
    public function init() {
        global $debug, $cfg;

        (defined('DEBUG') && $cfg['plugins_debug']) ? $this->debug = 1 : $this->debug = 0;
        $this->debug ? $debug->log('Plugin INIT called', 'PLUGINS', 'DEBUG') : null;
        $this->setPluginsDB();

        //We use &reference because setStarted add field in the loop and foreach copy the array
        foreach ($this->plugins_db as &$plugin) {
            if ($plugin['autostart'] && $plugin['enabled']) {
                if (empty($plugin['started'])) {
                    if ($this->pluginCheck($plugin)) {
                        $this->startPlugin($plugin);
                    } else {
                        $plugin['fail'] = 1;
                    }
                } else {
                    
                }
            } else {
                
            }
        }
    }

    /**
     * Return plugins db
     * @return array
     */
    function getPluginsDB() {
        $this->setPluginsDB();
        return $this->plugins_db;
    }

    /**
     * Start plugin and include common plugin files
     * @global Debug $debug
     * @param array $plugin
     * @return boolean
     */
    function startPlugin($plugin) {
        global $debug;

        $this->debug ? $debug_msg = 'STARTING plugin ' . $plugin['plugin_name'] . ' -> ' : null;

        require_once('plugins/' . $plugin['plugin_name'] . '/' . $plugin['main_file']);
        $this->includePluginFiles($plugin['plugin_name']);
        $init_function = $plugin['function_init'];
        if (function_exists($init_function)) {
            $init_function();
            $this->debug ? $debug->log($debug_msg . 'Function init called successful', 'PLUGINS', 'NOTICE') : null;
        } else {
            $this->debug ? $debug->log($debug_msg . 'Function init no exist', 'PLUGINS', 'ERROR') : null;
            return false;
        }
        $this->setStarted($plugin);
        $allprovide = preg_split('/\s+/', $plugin['provide']);
        foreach ($allprovide as $provide) {
            $this->setProvideDepend($provide, $plugin['version']);
        }

        return true;
    }

    /**
     * Set plugin started to 1 in (@link $plugin_db)
     * @param array $plugin
     * @return boolean
     */
    function setStarted($plugin) {
        foreach ($this->plugins_db as $key => $plugin_db) {
            if ($plugin['plugin_id'] == $plugin_db['plugin_id']) {
                $this->plugins_db[$key]['started'] = 1;
                return true;
            }
        }
        return false;
    }

    /**
     * Return plugin id providing the plugin name or false
     * @param string $plugin_name
     * @return boolean|int
     */
    function getPluginID($plugin_name) {
        foreach ($this->plugins_db as $plugin) {
            if ($plugin['plugin_name'] == $plugin_name && $plugin['enabled'] == 1) {
                return $plugin['plugin_id'];
            }
        }
        return false;
    }

    /**
     * Return plugin version
     * 
     * @param type $plugin_name
     * @return boolean|float
     */
    function getPluginVersion($plugin_name) {
        foreach ($this->plugins_db as $plugin) {
            if ($plugin['plugin_name'] == $plugin_name) {
                return $plugin['version'];
            }
        }
        return false;
    }

    /**
     * Install plugin. Calls plugin install function
     * @global Database $db
     * @param int $plugin_id
     * @return boolean
     */
    function install($plugin_id) {
        global $db;

        $query = $db->selectAll('plugins', ['plugin_id' => $plugin_id], 'LIMIT 1');
        $plugin = $db->fetch($query);
        if ($plugin['installed'] != 1) {
            require_once("plugins/{$plugin['plugin_name']}/{$plugin['main_file']}");
            $func_plugInstall = $plugin['function_install'];
            if (function_exists($func_plugInstall)) {
                if ($func_plugInstall()) {
                    $db->update('plugins', ['installed' => 1], ['plugin_id' => $plugin_id]);
                }
            } else {
                die('function no exists');
            }
        } else {
            return false;
        }
        $this->reloadPlugin($plugin_id);
        return true;
    }

    /**
     * Uninstall by plugin id, force will ignore installed flag for clean a fail
     * @global Database $db
     * @param int $plugin_id 
     * @param int $force
     * @return boolean
     */
    function uninstall($plugin_id, $force = 0) {
        global $db;

        $query = $db->selectAll('plugins', ['plugin_id' => $plugin_id], 'LIMIT 1');
        $plugin = $db->fetch($query);
        if ($plugin['installed'] == 1 || $force) {
            require_once("plugins/{$plugin['plugin_name']}/{$plugin['main_file']}");
            $func_plugUninstall = $plugin['function_uninstall'];
            if (function_exists($func_plugUninstall)) {
                if ($func_plugUninstall()) {
                    $db->update('plugins', ['installed' => 0], ['plugin_id' => $plugin_id]);
                }
            } else {
                die('function no exists');
            }
        } else {
            return false;
        }
        $this->reloadPlugin($plugin_id);
        return true;
    }

    /**
     * Upgrade plugin process
     * @global Database $db
     * @param int $plugin_id
     * @return boolean
     */
    function upgrade($plugin_id) {
        global $db;

        $query = $db->selectAll('plugins', ['plugin_id' => $plugin_id], 'LIMIT 1');
        $plugin = $db->fetch($query);
        if ($plugin['installed'] == 1) {
            require_once("plugins/{$plugin['plugin_name']}/{$plugin['main_file']}");
            $func_Upgrade = $plugin['function_upgrade'];
            if (function_exists($func_Upgrade)) {
                if ($func_Upgrade($plugin['version'], $plugin['upgrade_from'])) {
                    $db->update('plugins', ['upgrade_from' => 0], ['plugin_id' => $plugin_id]);
                }
            } else {
                die('function no exists');
            }
        } else {
            return false;
        }
        $this->reloadPlugin($plugin_id);

        return true;
    }

    /**
     * Set plugin enabled
     * @global Database $db
     * @param int $plugin_id
     * @param int $value
     * @return boolean
     */
    function setEnable($plugin_id, $value) {
        global $db;

        if (!(($value == 0) || ($value == 1))) {
            return false;
        }
        $db->update('plugins', ['enabled' => $value], ['plugin_id' => $plugin_id], 'LIMIT 1');
        $this->reloadPlugin($plugin_id);

        return true;
    }

    /**
     * set automatic start
     * @global Database $db
     * @param int $plugin_id
     * @param int $value
     * @return boolean
     */
    function setAutostart($plugin_id, $value) {
        global $db;

        if (!(($value == 0) || ($value == 1))) {
            return false;
        }

        $db->update('plugins', ['autostart' => $value], ['plugin_id' => $plugin_id], 'LIMIT 1');

        $this->reloadPlugin($plugin_id);

        return true;
    }

    /**
     * Reload a plugin info
     * @global Database $db
     * @param int $plugin_id
     */
    function reloadPlugin($plugin_id) {
        global $db;

        $query = $db->selectAll('plugins', ['plugin_id' => $plugin_id], 'LIMIT 1');
        if ($db->numRows($query) > 0) {
            foreach ($this->plugins_db as &$plugin_db) {
                if ($plugin_db['plugin_id'] == $plugin_id) {
                    $plugin_db = $db->fetch($query);
                }
            }
        }
    }

    /**
     * Scan plugins dir and push into (@link $registered_plugins)
     * @global Debug $debug
     */
    function scanDir() {
        global $debug;
        foreach (glob('plugins/*', GLOB_ONLYDIR) as $plugins_dir) {
            $filename = str_replace('plugins/', '', $plugins_dir);
            $full_json_filename = $plugins_dir . '/' . $filename . '.json';

            if (file_exists($full_json_filename)) {
                $jsondata = file_get_contents($full_json_filename);
                $plugin_data = json_decode($jsondata);
                $this->debug ? $debug->log('Plugin ' . $plugin_data->plugin_name . ' added to the registered', 'PLUGINS', 'INFO') : null;
                array_push($this->registered_plugins, $plugin_data);
            }
        }
    }

    /**
     * Search for a plugin provider key
     * @param string $provide
     * @return array
     */
    function getPluginProvide($provide) {
        $result = [];

        foreach ($this->registered_plugins as $plugin) {
            if (!empty($plugin) && (trim($plugin->provide)) == $provide) {
                array_push($result, $plugin);
            }
        }

        return $result;
    }

    /**
     * Search by name the plugin and return the plugin array 
     * @param string $plug_name
     * @return boolean|array
     */
    function getPluginByName($plug_name) {
        foreach ($this->registered_plugins as $plugin) {
            if (!empty($plugin) && (trim($plugin->plugin_name)) == $plug_name) {
                return $plugin;
            }
        }
        return false;
    }

    /**
     * Rescan plugin dir for new plugins and update json changes
     * @global Database $db
     */
    function reScanToDB() {
        global $db;

        $this->registered_plugins = [];
        $this->scanDir();
        $db->update('plugins', ['missing' => 1]); //mark everything missing

        foreach ($this->registered_plugins as $plugin) {
            $result = $db->selectAll('plugins', ['plugin_name' => $plugin->plugin_name], 'LIMIT 1');

            $query_ary = [
                'plugin_name' => $plugin->plugin_name,
                'version' => $plugin->version,
                'main_file' => $plugin->main_file,
                'function_init' => $plugin->function_init,
                'function_admin_init' => $plugin->function_admin_init,
                'function_install' => $plugin->function_install,
                'function_pre_install' => $plugin->function_pre_install,
                'function_pre_install_info' => $plugin->function_pre_install_info,
                'function_uninstall' => $plugin->function_uninstall,
                'function_upgrade' => $plugin->function_upgrade,
                'provide' => $plugin->provide,
                'depends' => serialize($plugin->depends),
                'priority' => $plugin->priority,
                'optional' => serialize($plugin->optional),
                'conflicts' => serialize($plugin->conflicts)
            ];

            if ($db->numRows($result) > 0) {
                $plugin_row = $db->fetch($result);
                if ($plugin->version != $plugin_row['version']) {
                    if ($plugin_row['upgrade_from'] == 0) {
                        $query_ary['upgrade_from'] = $plugin_row['version'];
                    }
                    $query_ary['missing'] = 0;
                    $db->update('plugins', $query_ary, ['plugin_name' => $plugin->plugin_name]);
                } else {
                    //$db->update("plugins", ["missing" => 0], ["plugin_name" => $plugin->plugin_name]);
                    /* Update DB all better while devel and not versioning */
                    $query_ary['missing'] = 0;
                    $db->update('plugins', $query_ary, ['plugin_name' => $plugin->plugin_name]);
                }
            } else {
                $db->insert('plugins', $query_ary);
            }
        }
    }

    /**
     * After load a plugin keep track of what provide 
     * @param string $provide
     * @param float $version
     */
    function setProvideDepend($provide, $version) {
        $this->depends_provide[$provide] = $version;
    }

    /**
     * Express start a plugin by plugin name (mainly when not autostart and plugin need it)
     * @global Debug $debug
     * @param string $plugin_name
     * @return boolean
     */
    function expressStart($plugin_name) {
        global $debug;

        $this->debug ? $debug_msg = 'Express order to start plugin ' . $plugin_name . '-> ' : null;
        foreach ($this->plugins_db as $plugin) {
            if ($plugin['enabled'] && $plugin['plugin_name'] == $plugin_name) {
                if (!empty($plugin['started'])) {
                    $this->debug ? $debug->log($debug_msg . 'Plugin  already started', 'PLUGINS', 'INFO') : null;
                    return true;
                }
                if ($this->pluginCheck($plugin)) {
                    $this->debug ? $debug->log($debug_msg . 'plugin check succes.. trying express started plugin', 'PLUGINS', 'INFO') : null;
                    if ($this->startPlugin($plugin)) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    $this->debug ? $debug->log($debug_msg . 'plugin check fail', 'PLUGINS', 'ERROR') : null;
                    return false;
                }
            }
        }
        $this->debug ? $debug->log($debug_msg . 'Plugin not exist ', 'PLUGINS', 'ERROR') : null;

        return false;
    }

    /**
     * Search for a provide and express Start
     * @global Debug $debug
     * @param string $provider
     * @return boolean
     */
    function expressStartProvider($provider) {
        global $debug;

        $this->debug ? $debug_msg = 'Express order to start the provider ' . $provider . '-> ' : null;

        foreach ($this->plugins_db as $plugin) {
            if ($plugin['enabled'] && $plugin['provide'] == $provider) {
                if ($this->checkStarted($plugin['plugin_name'])) {
                    $this->debug ? $debug->log($debug_msg . 'Provider already started', 'PLUGINS', 'INFO') : null;
                    return true;
                }
                if ($this->pluginCheck($plugin)) {
                    $this->debug ? $debug->log($debug_msg . 'Check success, trying start', 'PLUGINS', 'INFO') : null;
                    if ($this->startPlugin($plugin)) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    $this->debug ? $debug->log($debug_msg . 'Check fail', 'PLUGINS', 'ERROR') : null;
                    return false;
                }
            }
        }
        $this->debug ? $debug->log($debug_msg . 'Provider not exist', 'PLUGINS', 'ERROR') : null;

        return false;
    }

    /**
     * Check if a plugin its started
     * @param string $plugin_name
     * @return boolean
     */
    function checkStarted($plugin_name) {

        foreach ($this->plugins_db as $plugins) {
            if ($plugins['plugin_name'] == $plugin_name && !empty($plugins['started'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Include basic/common plugin files like lang, /includes/pluginname.inc.php
     * /includes/pluginame.class.php, /admin/pluginname.inc.php
     * @global array $cfg
     * @global array $LNG 
     * @global Debug $debug
     * @param string $plugin
     * @param int $admin //Include admin mng/panel files
     */
    private function includePluginFiles($plugin, $admin = 0) {
        global $cfg, $LNG, $debug; //need global LNG for included lang files in global

        $class_file = '';
        $inc_file = '';

        $this->debug ? $debug_msg = 'Loading standard files: ' : null;

        $lang_file = "plugins/$plugin/lang/{$cfg['WEB_LANG']}/$plugin.lang.php";
        if (file_exists($lang_file)) {
            $this->debug ? $debug_msg .= 'Lang file (' . $lang_file . ') ' : null;
            include_once($lang_file); // Need global $LNG
        }

        //INC FILE
        if ($admin == 0) {
            $inc_file = 'plugins/' . $plugin . '/includes/' . $plugin . '.inc.php';
            $class_file = 'plugins/' . $plugin . '/includes/' . $plugin . '.class.php';
        } else {
            $inc_file = 'plugins/' . $plugin . '/admin/' . $plugin . '.admin.inc.php';
        }
        !empty($inc_file) && file_exists($inc_file) ? include_once($inc_file) : null;
        !empty($inc_file) && file_exists($class_file) ? include_once($class_file) : null;
        if ($this->debug) {
            if (!empty($inc_file) && file_exists($inc_file)) {
                include_once($inc_file);
                $this->debug ? $debug_msg .= 'Include file (' . $inc_file . ') ' : null;
            }
            if (!empty($inc_file) && file_exists($class_file)) {
                include_once($class_file);
                $this->debug ? $debug_msg .= 'Class file (' . $class_file . ') ' : null;
            }
        }
        $this->debug ? $debug->log($debug_msg, 'PLUGINS', 'INFO') : null;
    }

    /**
     * Retrieve from db the plugins information and set (@link $plugins_db) 
     * @global Database $db
     * @param int $force
     */
    private function setPluginsDB($force = 0) {
        global $db;

        if (empty($this->plugins_db) || $force == 1) {
            $result = $db->selectAll('plugins');
            if ($result) {
                $this->plugins_db = $db->fetchAll($result);
            }
        }
    }

    /**
     * Check pluigin, provide conflicts, and resolve depends
     * @global Debug $debug
     * @param array $plugin
     * @return boolean
     */
    private function pluginCheck($plugin) {
        global $debug;

        if ($this->checkConflicts($plugin)) {
            $this->debug ? $debug->log('Conflicts ' . $plugin['plugin_name'] . ' another plugin provided', 'PLUGINS', 'ERROR') : null;
            return false;
        }

        if (empty($plugin['depends']) || $this->resolvePluginDepends($plugin)) {
            return true;
        } else {
            $this->debug ? $debug->log('Error resolving depends ' . $plugin['plugin_name'], 'PLUGINS', 'ERROR') : null;
        }

        return false;
    }

    /**
     * Check plugins conflict
     * 
     * Check if already exists the provide
     * TODO: conflict array check
     * 
     * @param array $plugin
     * @return boolean
     */
    private function checkConflicts($plugin) {
        $allprovide = preg_split('/\s+/', $plugin['provide']);
        foreach ($allprovide as $provide) {
            if (empty($provide)) {
                return false;
            }
            if ($this->checkDupProvide($provide)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check duplicate provide in (@link $plugins_db)
     * @param string $provide
     * @return boolean
     */
    private function checkDupProvide($provide) {

        foreach ($this->plugins_db as $plugin) {
            if (!empty($plugin['started'])) {
                $allprovide = preg_split('/\s+/', $plugin['provide']);
                foreach ($allprovide as $started_provide) {
                    if ($started_provide == $provide) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Resolve plugins depends
     * @global Debug $debug
     * @param array $plugin
     * @return boolean
     */
    private function resolvePluginDepends($plugin) {
        global $debug;

        $this->debug ? $debug->log('Resolving ' . $plugin['plugin_name'], 'PLUGINS', 'DEBUG') : null;

        if (empty($plugin['depends'])) {
            $this->debug ? $debug->log('Resolving depends... ' . $plugin['plugin_name'] . ':[Empty depends]', 'PLUGINS', 'INFO') : null;
            return true;
        } else {
            $depends = unserialize($plugin['depends']);
        }

        $this->debug ? $debug_msg = 'Depends check for plugin ' . $plugin['plugin_name'] . ' -> ' : null;
        foreach ($depends as $depend) {
            $this->debug ? $debug_msg .= ' ' . $depend->name . ' ' : null;

            $result = $this->checkIfDepsStarted($depend->name, $depend->min_version, $depend->max_version);

            if (!$result) {
                if ($this->findDepsAndStart($depend->name, $depend->min_version, $depend->max_version)) {
                    $this->debug ? $debug_msg .= '[Found]' : null;
                } else {
                    $this->debug ? $debug->log($debug_msg . '[Not found][Stopped]', 'PLUGINS', 'ERROR') : null;
                    return false;
                }
            } else {
                $this->debug ? $debug_msg .= '[Already started]' : null;
            }
        }
        $this->debug ? $debug->log($debug_msg, 'PLUGINS', 'DEBUG') : null;
        return true;
    }

    /**
     * Check if $plugin_name its enabled.
     * @param string $plugin_name
     * @return boolean
     */
    function checkEnabled($plugin_name) {
        foreach ($this->plugins_db as $plugin) {
            if ($plugin['enabled'] == 1 && $plugin['plugin_name'] == $plugin_name) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if a plugin provide its enabled
     * @param string $provide
     * @return boolean
     */
    function checkEnabledProvider($provide) {
        if ($provide == 'CORE' || $provide == 'SQL' || $provide == 'DEBUG') {
            return true;
        }
        foreach ($this->plugins_db as $plugin) {
            if ($plugin['enabled'] == 1 && $plugin['provide'] == $provide) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if provider is installed
     * @param string $provide
     * @return boolean
     */
    function checkInstalledProvider($provide) {
        if ($provide == 'CORE' || $provide == 'SQL' || $provide == 'DEBUG') {
            return true;
        }
        foreach ($this->plugins_db as $plugin) {
            if ($plugin['installed'] == 1 && $plugin['provide'] == $provide) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the plugins have his depends started
     * @global Debug $debug
     * @param string $depend_name
     * @param float $min_version
     * @param flaot $max_version
     * @return boolean
     */
    private function checkIfDepsStarted($depend_name, $min_version, $max_version) {
        if (isset($this->depends_provide[$depend_name])) {
            $version = $this->depends_provide[$depend_name];
            if (($version >= $min_version) && ($version <= $max_version)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Search dependencies and try start
     * 
     * cyclic dependences
     * 
     * @global Debug $debug
     * @param string $depend_name
     * @param float $min_version
     * @param float $max_version
     * @return boolean
     */
    private function findDepsAndStart($depend_name, $min_version, $max_version) {
        global $debug;

        foreach ($this->plugins_db as $plugin) {
            if ($plugin['enabled']) {
                $allprovide = preg_split('/\s+/', $plugin['provide']);
                foreach ($allprovide as $provide) {
                    if (($provide == $depend_name) && ($plugin['version'] >= $min_version) && ($plugin['version'] <= $max_version)
                    ) {
                        $this->debug ? $debug->log('Need resolve ' . $depend_name . ' for ' . $plugin['plugin_name'] . ' before', 'PLUGINS', 'INFO') : null;
                        if ($this->resolvePluginDepends($plugin)) {//resolv de dependes of the depends
                            if ($plugin['autostart']) {
                                $this->debug ? $debug->log('Starting ' . $plugin['plugin_name'] . ' as a ' . $depend_name . ' depend ', 'PLUGINS', 'INFO') : null;
                                $this->startPlugin($plugin);
                            } else {
                                $this->debug ? $debug->log('NOT Starting  as depend ' . $depend_name . ' because autostart its off, express start need', 'PLUGINS', 'INFO') : null;
                            }
                            return true;
                        }
                    }
                }
            }
        }
        $this->debug ? $debug->log('Failed finding dependences for ' . $depend_name, 'PLUGINS', 'ERROR') : null;
        return false;
    }

}
