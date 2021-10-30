
<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function page_defaults($cfg, User $user) {
    $page = [];

    $_user = $user->getUser();

    empty($_user['theme']) ? $page['theme'] = $cfg['theme'] : $page['theme'] = $_user['theme'];
    empty($_user['lang']) ? $page['lang'] = $cfg['lang'] : $page['lang'] = $_user['lang'];
    empty($_user['charset']) ? $page['charset'] = $cfg['charset'] : $page['charset'] = $_user['charset'];
    $page['web_title'] = $cfg['web_title'];

    return $page;
}

function page_index($cfg, $lng, $user) {
    global $db;

    $page = [];

    $page['page'] = 'index';
    $page['head_name'] = $cfg['web_title'];

    $results = $db->select('items', '*', ['type' => 'search_engine']);
    $search_engines = $db->fetchAll($results);

    foreach ($search_engines as $search_engine) {
        $conf = json_decode($search_engine['conf'], true);
        $page['search_engines'][] = [
            'url' => $conf['url'],
            'name' => $conf['name'],
        ];
    }

    return $page;
}

function page_login($cfg, $lng, $user) {


    $page = [];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $username = Filters::postUsername('username');
        $password = Filters::postPassword('password');
        if (!empty($username) && !empty($password)) {

            $userid = $user->checkUser($username, $password);
            if (!empty($userid) && $userid > 0) {
                $user->setUser($userid);
                //header("Location: {$cfg['rel_path']} ");
                //echo "page.inc 42";
                header("Location: /monnet ");
                exit();
            }
        }
    }


    $page['page'] = 'login';
    $page['tpl'] = 'login';
    $page['log_in'] = $lng['L_LOGIN'];
    $page['username_placeholder'] = $lng['L_USERNAME'];
    $page['password_placeholder'] = $lng['L_PASSWORD'];

    return $page;
}
