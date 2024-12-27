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
 *
 * @var array<int|string, mixed> $cfg
 * @var array<int|string, mixed> $cfg_db
 */
if (!file_exists('config/config.defaults.php')) {
    print 'Missing config.defaults.php. Leaving';
    exit(1);
}
if (!file_exists('config/config.priv.php')) {
    print 'Missing config.priv.php. Leaving';
    exit(1);
}
if (!file_exists('/etc/monnet/config.inc.php')) {
    print 'Missing config.inc.php. Leaving';
    exit(1);
}

require_once 'config/config.priv.php';
require_once 'config/config.defaults.php';

/*
 * FIXME: climode.inc.php need include this file before for acccess 'path'
 * This include cant be include_once because climode rewrite with defaults
 * and must load again
 */
include '/etc/monnet/config.inc.php';

date_default_timezone_set($cfg['timezone']);

require_once 'include/checks.inc.php';
common_checks($cfg_db, $cfg);

require_once 'class/AppContext.php';

if ($cfg_db['dbtype'] == 'mysqli') {
    require_once 'class/Mysql.php';
}

$ctx = new AppContext($cfg);

try {
    $db = $ctx->set('Mysql', new Database($cfg_db));

    if (!$db instanceof Database) {
        throw new Exception("Database instance is not created.");
    }

    $db->connect();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

require_once 'class/Log.php';

/**
 * @var array<string> $lng
 */
/* Get default lang overwrite after with user settings */
require_once 'lang/es/main.lang.php';
$ctx->setLang($lng);

require_once 'class/Lang.php';

require_once 'include/util.inc.php';
require_once 'include/time.inc.php';
require_once 'class/Filters.php';

/*
 * TODO Actualmente necesita update primero para que cree la tabla Config
 * update.inc.php utiliza ncfg
 * para evitar y actualice bien de momento usamos un archivo updateuserold.inc.php
 * hasta que exista la tabla config y $ncfg no devuelva null borrar sobre finales
 * de enero
 *
 */

$query = $db->select('prefs', 'pref_value', ['uid' => 0, 'pref_name' => 'monnet_version']);
if ($query) :
    $result = $db->fetchAll($query);
    if ($result) :
        $db_version = (float) $result[0]['pref_value'];
    endif;
endif;

Log::init($cfg, $db, $lng);

if (isset($db_version) && $db_version  < 0.40) {
    require_once 'include/updaterold.inc.php';
} else {
    $ncfg = $ctx->set('Config', new Config($cfg, $ctx));
    require_once 'include/updater.inc.php';
}

if (!empty($ncfg) && $ncfg->get('ansible')) {
    require_once('include/ansible.inc.php');
}
