<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
define('IN_WEB', true);

header('Content-Type: application/json; charset=UTF-8');

/**
 *
 * @var User|null $user An instance of User or null if not defined
 * @var AppContext|null $ctx An instance of Context or null if not defined
 * @var array<string,string> $lng
 * @var Database|null $db An instance of Database or null if not defined
 * @var array<int|string, mixed> $cfg
 */
require_once 'include/common.inc.php';
require_once 'include/common-call.php';
require_once 'include/usermode.inc.php';
require_once 'include/submitter-call.php';

$tdata = [];
$hosts = $ctx->get('Hosts');
$target_id = 0;

$data = [
    'conn' => 'success',
    'login' => 'fail',
    'command_receive' => '',
    'command_success' => 0,
    'command_error' => 0,
    'command_error_msg' => '',
    'response_msg' => '',
];

if ($user->getId() > 0) {
    $data['login'] = 'success';
} else {
    print(json_encode($data));
    exit();
}
$frontend = new Frontend($ctx);
$tdata['theme'] = $cfg['theme'];

$command = Filters::postString('command');
if (empty($command)) {
    $data['command_error'] = 1;
    $data['command_error_msg'] = 'Command is empty or not a string';
}

$command_values = Filters::sanArray('command_values', 'post');

if (empty($command_values)) {
    $data['command_error'] = 1;
    $data['command_error_msg'] = 'Command values is empty or not an array';
}

//ID is mandatory, must send 0 if not apply
if (!isset($command_values['id'])) {
    $data['command_error'] = 1;
    $data['command_error_msg'] = 'Id field is mandatory';
} else {
    $target_id = Filters::varInt($command_values['id']);
}

if ($command == 'saveNote') {
    $value_command = trim(Filters::varUTF8($command_values['value']));
} elseif ($command == 'submitScanPorts') {
    $value_command = trim(Filters::varCustomString($command_values['value'], ',/', 255));
} elseif ($command == 'setCheckPorts' || $command == 'submitHostTimeout') {
    $value_command = Filters::varInt($command_values['value']);
} elseif ($command == 'addNetwork') {
    $value_command = Filters::varJson($command_values['value']);
} elseif ($command == 'addBookmark') {
    $value_command = Filters::varJson($command_values['value']);
} elseif ($command == 'updateBookmark') {
    $value_command = Filters::varJson($command_values['value']);
} elseif ($command == 'submitHost') {
    $value_command = Filters::varIP($command_values['value']);
    if (empty($value_command)) {
        $value_command = Filters::varDomain($command_values['value']);
    }
} elseif ($command == 'updateAlertEmailList') {
    //TODO filter array of emails
    $value_command = $command_values['value'];
} else {
    if (isset($command_values['value']) && is_numeric($command_values['value'])) {
        $value_command = Filters::varInt($command_values['value']);
    } elseif (
        empty($value_command) &&
        isset($command_values['value']) &&
        is_bool($command_values['value'])
    ) {
        $value_command = Filters::varBool($command_values['value']);
    } elseif (
        empty($value_command) &&
        isset($command_values['value'])
    ) {
        $value_command = Filters::varString($command_values['value']);
    }
}

if (!empty($command)) :
    $data['command_receive'] = $command;
endif;
if (!isEmpty($value_command)) :
    $data['command_value'] = $value_command;
else :
    $value_command = '';
endif;
if (!is_numeric($target_id)) :
    $data['command_error'] = 1;
    $data['command_error_msg'] = 'Id field is no numeric:';
else :
    $target_id = (int) $target_id;
endif;

if (!empty($data['command_error_msg'])) :
    print json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
endif;

/*
 *  END FILTERS(SANITIZE)
  */

/* Remove host */
if ($command === 'remove_host' && $target_id) {
    $hosts->remove($target_id);
    //no host_details
    $user->setPref('host_details', 0);
    $data['command_success'] = 1;
    $data['force_hosts_refresh'] = 1;
    $data['response_msg'] = 'Host removed: ' . $target_id;
    $command = $target_id = '';
}

if ($command === 'network_select' && !empty($value_command)) {
    $pref_name = 'network_select_' . $value_command;
    $user->setPref($pref_name, 1);
    $data['command_success'] = 1;
    $data['force_hosts_refresh'] = 1;
    $data['response_msg'] = 'Network Select';
}
if ($command === 'network_unselect' && !empty($value_command)) {
    $pref_name = 'network_select_' . $value_command;
    $user->setPref($pref_name, 0);
    $data['command_success'] = 1;
    $data['force_hosts_refresh'] = 1;
    $data['response_msg'] = 'Network Unselect';
}

if ($command == 'toggleDisablePing' && isset($value_command) && !empty($target_id)) {
    $hosts->update($target_id, ['disable_ping' => $value_command]);
    $data['command_success'] = 1;
    $data['response_msg'] = $value_command;
    $data['response_msg'] = 'ok';
}

if ($command == 'toggleVMMachine' && isset($value_command) && !empty($target_id)) {
    $hosts->update($target_id, ['vm_machine' => $value_command]);
    $data['command_success'] = 1;
    $data['response_msg'] = $value_command;
    $data['response_msg'] = 'ok';
}

if ($command == 'toggleHypervisorMachine' && isset($value_command) && !empty($target_id)) {
    $hosts->update($target_id, ['hypervisor_machine' => $value_command]);
    $data['command_success'] = 1;
    $data['response_msg'] = $value_command;
    $data['response_msg'] = 'ok';
}

if ($command == 'setCheckPorts' && !empty($value_command) && !empty($target_id)) {
    // 1 ping 2 TCP/UDP
//    ($value_command == 0) ? $value = 1 : $value = 2;

    $hosts->update($target_id, ['check_method' => $value_command]);
    $data['command_success'] = 1;
    $data['response_msg'] = $value_command;
    $data['response_msg'] = 'ok';
}

if ($command == 'submitHostToken' && !empty($target_id)) {
    if ($hosts->createHostToken()) :
        $data['response_msg'] = 'Token Created';
        $data['command_success'] = 1;
    else :
        $data['command_success'] = 0;
        $data['error_msg'] = 'Error creating token';
    endif;
}
if ($command == 'submitScanPorts' && !empty($target_id)) {
    $success_msg = '';
    if (!empty($value_command)) {
        $valid_ports = validatePortsInput($value_command);
        if (valid_array($valid_ports)) {
            if (($encoded_ports = json_encode($valid_ports))) {
                $db->update('hosts', ['ports' => $encoded_ports], ['id' => $target_id]);
                $total_elements = count($valid_ports) - 1;
                foreach ($valid_ports as $index => $port) {
                    $success_msg .= $port['n'] . '/';
                    $success_msg .= ($port['port_type'] === 1) ? 'tcp' : 'udp';
                    $success_msg .= '/' . $port['name'];
                    $success_msg .= ($index === $total_elements) ? '' : ',';
                }
            }
        }
    }
    $data['command_success'] = 1;
    $data['response_msg'] = $success_msg;
    $data['response_msg'] = 'ok';
}

if ($command == 'submitTitle' && !empty($target_id)) {
    $success = 0;
    if (!empty($value_command)) {
        $hosts->update($target_id, ['title' => $value_command]);
        $success = 1;
    }
    $data['command_success'] = $success;
    $data['force_hosts_refresh'] = 1;
    $data['response_msg'] = 'ok';
}

if ($command == 'submitOwner' && !empty($target_id)) {
    $success = 0;
    if (!empty($value_command)) {
        $hosts->update($target_id, ['owner' => $value_command]);
        $success = 1;
    }
    $data['command_success'] = $success;
    $data['response_msg'] = 'ok';
}

if ($command == 'submitHostTimeout' && !empty($target_id)) {
    $success = 0;
    if (!empty($value_command)) {
        $hosts->update($target_id, ['timeout' => $value_command]);
        $success = 1;
    }
    $data['command_success'] = $success;
    $data['response_msg'] = 'ok';
}

// Change Host Cat
if ($command == 'submitCat' && !empty($target_id)) {
    $success = 0;
    if (!empty($value_command)) {
        $hosts->update($target_id, ['category' => $value_command]);
        $success = 1;
    }
    $data['command_success'] = $success;
    $data['response_msg'] = 'Category changed to ' . $value_command;
    $data['force_hosts_refresh'] = 1;
}


if ($command == 'submitManufacture' && !empty($target_id)) {
    $success = 0;
    if (!empty($value_command)) {
        $hosts->update($target_id, ['manufacture' => $value_command]);
        $success = 1;
    }
    $data['command_success'] = $success;
    $data['response_msg'] = 'Manufacture changed to ' . $value_command;
}
if ($command == 'submitMachineType' && !empty($target_id)) {
    $success = 0;
    if (!empty($value_command)) {
        $hosts->update($target_id, ['machine_type' => $value_command]);
        $success = 1;
    }
    $data['command_success'] = $success;
    $data['response_msg'] = 'Machine type changed to ' . $value_command;
}
if ($command == 'submitSysAval' && !empty($target_id)) {
    $success = 0;
    if (!empty($value_command)) {
        $hosts->update($target_id, ['sys_availability' => $value_command]);
        $success = 1;
    }
    $data['command_success'] = $success;
    $data['response_msg'] = 'System availability changed to ' . $value_command;
}

if ($command == 'submitOS' && !empty($target_id)) {
    $success = 0;
    if (!empty($value_command)) {
        $hosts->update($target_id, ['os' => $value_command]);
        $success = 1;
    }
    $data['command_success'] = $success;
    $data['response_msg'] = 'OS changed to ' . $value_command;
}

if ($command == 'submitSystemType' && !empty($target_id)) {
    $success = 0;
    if (!empty($value_command)) {
        $hosts->update($target_id, ['system_type' => $value_command]);
        $success = 1;
    }
    $data['command_success'] = $success;
    $data['response_msg'] = 'System Type changed to ' . $value_command;
}

if ($command === 'submitAccessLink' && !empty($target_id)) {
    $success = 5;

    if (!empty($command_values['value'])) {
        $value_command = Filters::varUrl($command_values['value']);
        if (!empty($value_command)) {
            $hosts->update($target_id, ['access_link' => trim($value_command)]);
            $data['response_msg'] = "link updated";
            $success = 1;
        } else {
            $data['command_error'] = 1;
            $data['command_error_msg'] = "Wrong value";
        }
    }
    //clear_field
    if (empty($command_values['value'])) {
        $hosts->update($target_id, ['access_link' => $value_command]);
        $data['response_msg'] = "link cleared";
        $success = 1;
    }
    $data['command_success'] = $success;
}

if ($command === 'submitAccessType' && !empty($target_id)) {
    $success = 0;
    if (!empty($value_command)) {
        $hosts->update($target_id, ['access_type' => $value_command]);
        $success = 1;
    }
    $data['command_success'] = $success;
}

/* Show cat Only * */
if ($command == 'show_host_only_cat' && !empty($target_id)) {
    $categories_state = $user->getHostsCatState();

    $ones = 0;
    foreach ($categories_state as $state) {
        if ($state == 1) {
            $ones++;
        }
    }

    if (empty($categories_state) || $ones == 1) {
        $user->turnHostsCatsOn();
    } else {
        $user->turnHostsCatsOff();
        $user->toggleHostsCat($target_id);
    }
    $data['command_success'] = 1;
    $data['force_hosts_refresh'] = 1;
}

if ($command == 'show_host_cat' && !empty($target_id)) {
    $user->toggleHostsCat($target_id);
    $data['command_success'] = 1;
    $data['force_hosts_refresh'] = 1;
}

if (
    $command == 'show_host_cat' ||
    $command == 'show_host_only_cat' &&
    $target_id
) {
    $hosts_categories = $user->getHostsCats();


    foreach ($hosts_categories as $key => $host_cat) {
        if (!$hosts->catHaveHosts($host_cat['id'])) {
            unset($hosts_categories[$key]);
        }
    }
    $tdata['hosts_categories'] = $hosts_categories;

    $networks_list = $ctx->get('Networks')->getNetworks();

    $networks_selected = 0;
    foreach ($networks_list as &$net) {
        $net_set = $user->getPref('network_select_' . $net['id']);
        if (($net_set) || $net_set === false) {
            $net['selected'] = 1;
            $networks_selected++;
        }
    }
    $tdata['networks'] = $networks_list;
    $tdata['networks_selected'] = $networks_selected;

    $data['categories_host']['data'] = $frontend->getTpl('categories-host', $tdata);
    $data['categories_host']['cfg']['place'] = '#left-container';
}

/* /end Host Cat */

/** @var array<string> $new_network */
$new_network = [];
/* ADD NETWORK */
if (
    $command == 'addNetwork' &&
    !empty($value_command)
) {
    $decodedJson = json_decode($value_command, true);

    if ($decodedJson === null) {
        $data['command_error'] = 1;
        $data['command_error_msg'] .= 'JSON Invalid';
    } else {
        foreach ($decodedJson as $key => $dJson) {
            ($key == 'networkVLAN') ? $key = 'vlan' : null;
            ($key == 'networkScan') ? $key = 'scan' : null;
            ($key == 'networkName') ? $key = 'name' : null;
            $new_network[$key] = trim($dJson);
        }
        if ($new_network['networkCIDR'] == 0 && $new_network['network'] != '0.0.0.0') {
            $data['command_error'] = 1;
            $data['command_error_msg'] .= $lng['L_MASK'] .
                ' ' . $new_network['networkCIDR'] .
                ' ' . $lng['L_NOT_ALLOWED'] . '<br/>';
        }
        $network_plus_cidr = $new_network['network'] . '/' . $new_network['networkCIDR'];
        unset($new_network['networkCIDR']);
        $new_network['network'] = $network_plus_cidr;

        if (!Filters::varNetwork($network_plus_cidr)) {
            $data['command_error'] = 1;
            $data['command_error_msg'] .= $lng['L_NETWORK'] . ' ' . $lng['L_INVALID'] . '<br/>';
        }
        if (!is_numeric($new_network['vlan'])) {
            $data['command_error'] = 1;
            $data['command_error_msg'] .= 'VLAN ' . "{$lng['L_MUST_BE']} {$lng['L_NUMERIC']}<br/>";
        }
        if (!is_numeric($new_network['scan'])) {
            $data['command_error_msg'] .= 'Scan ' . "{$lng['L_MUST_BE']} {$lng['L_NUMERIC']}<br/>";
        }

        $networks_list = $ctx->get('Networks')->getNetworks();
        foreach ($networks_list as $net) {
            if ($net['name'] == $new_network['name']) {
                $data['command_error'] = 1;
                $data['command_error_msg'] = 'Name must be unique<br/>';
            }
            if ($net['network'] == $network_plus_cidr) {
                $data['command_error'] = 1;
                $data['command_error_msg'] = 'Network must be unique<br/>';
            }
        }
        if (
            str_starts_with($new_network['network'], "0") ||
            !$ctx->get('Networks')->isLocal($new_network['network'])
        ) {
            $new_network['vlan'] = 0;
            $new_network['scan'] = 0;
        }

        if (empty($data['command_error_msg'])) {
            $ctx->get('Networks')->addNetwork($new_network);
            $data['command_success'] = 1;
            $data['response_msg'] = 'ok';
        }
    }
}

/* ADD Bookmark */
/** @var array<string> $new_bookmark */
$new_bookmark = [];
if (
    $command == 'addBookmark' &&
    !empty($value_command)
) {
    $decodedJson = json_decode($value_command, true);

    if ($decodedJson === null) {
        $data['command_error'] = 1;
        $data['command_error_msg'] .= 'JSON Invalid<br/>';
    } else {
        foreach ($decodedJson as $key => $dJson) {
            $new_bookmark[$key] = trim($dJson);
        }

        if (!Filters::varString($new_bookmark['name'])) {
            $data['command_error_msg'] .= "{$lng['L_NAME']}: {$lng['L_ERROR_EMPTY_INVALID']}";
        }
        if (!Filters::varString($new_bookmark['image_type'])) {
            $data['command_error'] = 1;
            $data['command_error_msg'] .= "{$lng['L_IMAGE_TYPE']}: {$lng['L_ERROR_EMPTY_INVALID']}";
        }
        if (!Filters::varInt($new_bookmark['cat_id'])) {
            $data['command_error_msg'] .= "{$lng['L_TYPE']}: {$lng['L_ERROR_EMPTY_INVALID']}";
        }

        if (
            !Filters::varUrl($new_bookmark['urlip']) ||
            Filters::varIP($new_bookmark['urlip'])
        ) {
            $data['command_error_msg'] = "{$lng['L_URLIP']}:{$lng['L_ERROR_EMPTY_INVALID']}";
        }

        if (
            (!Filters::varInt($new_bookmark['weight'])) &&
            (Filters::varInt($new_bookmark['weight']) !== 0)
        ) {
            $data['command_error_msg'] = "{$lng['L_WEIGHT']}: {$lng['L_ERROR_EMPTY_INVALID']}";
        }

        if ($new_bookmark['image_type'] === 'local_img') {
            if (empty($new_bookmark['field_img'])) {
                $data['command_error_msg'] = "{$lng['L_LINK']}: {$lng['L_ERROR_EMPTY_INVALID']}";
            } else {
                if (!Filters::varCustomString($new_bookmark['field_img'], '.', 255) || !file_exists('bookmark_img/')) {
                    $data['command_error_msg'] = "{$lng['L_LINK']}: {$lng['L_ERROR_EMPTY_INVALID']}";
                }
            }
        }

        if ($new_bookmark['image_type'] == 'url' && !empty($new_bookmark['field_img'])) {
            if (!Filters::varUrl($new_bookmark['field_img'])) {
                $data['command_error_msg'] = "{$lng['L_ERROR_URL_INVALID']}";
            }
        }

        if (empty($data['command_error_msg'])) {
            if ($ctx->get('Items')->addItem('bookmarks', $new_bookmark)) {
                $data['response_msg'] = 'ok';
            } else {
                $data['response_msg'] = 'error';
            }
        } else {
            $data['command_error'] = 1;
        }

        $data['command_success'] = 1;
    }
}

if ($command == 'updateBookmark' && !empty($value_command) && $target_id > 0) {
    $decodedJson = json_decode($value_command, true);
    $bookmark = [];
    $bookmark['id'] = $target_id;
    if ($decodedJson === null) {
        $data['command_error'] = 1;
        $data['command_error_msg'] .= 'JSON Invalid';
    } else {
        foreach ($decodedJson as $key => $dJson) {
            $bookmark[$key] = trim($dJson);
        }

        if (!Filters::varString($bookmark['name'])) {
            $data['command_error_msg'] .= "{$lng['L_NAME']}: {$lng['L_ERROR_EMPTY_INVALID']}";
        }
        if (!Filters::varString($bookmark['image_type'])) {
            $data['command_error_msg'] .= "{$lng['L_IMAGE_TYPE']}: {$lng['L_ERROR_EMPTY_INVALID']}";
        }
        if (!Filters::varInt($bookmark['cat_id'])) {
            $data['command_error_msg'] .= "{$lng['L_TYPE']}: {$lng['L_ERROR_EMPTY_INVALID']}";
        }
        if (!Filters::varInt($bookmark['bookmark_id'])) {
            $data['command_error_msg'] .= "{$lng['L_TYPE']}: {$lng['L_ERROR_EMPTY_INVALID']}";
        }
        if (
            !Filters::varUrl($bookmark['urlip']) ||
            Filters::varIP($bookmark['urlip'])
        ) {
            $data['command_error_msg'] .= "{$lng['L_URLIP']}: {$lng['L_ERROR_EMPTY_INVALID']}";
        }

        if (
            (!Filters::varInt($bookmark['weight'])) &&
            (Filters::varInt($bookmark['weight']) !== 0)
        ) {
            $data['command_error_msg'] = "{$lng['L_WEIGHT']}: {$lng['L_ERROR_EMPTY_INVALID']}";
        }

        if ($bookmark['image_type'] === 'local_img') {
            if (empty($bookmark['field_img'])) {
                $data['command_error_msg'] = "{$lng['L_LINK']}: {$lng['L_ERROR_EMPTY_INVALID']}";
            } else {
                if (!Filters::varCustomString($bookmark['field_img'], '.', 255) || !file_exists('bookmark_img/')) {
                    $data['command_error_msg'] = "{$lng['L_LINK']}: {$lng['L_ERROR_EMPTY_INVALID']}";
                }
            }
        }
        if ($bookmark['image_type'] == 'url' && !empty($bookmark['field_img'])) {
            if (!Filters::varUrl($bookmark['field_img'])) {
                $data['command_error_msg'] = "{$lng['L_ERROR_URL_INVALID']}";
            }
        }

        if (empty($data['command_error_msg'])) {
            if ($ctx->get('Items')->updateItem('bookmarks', $bookmark)) {
                $data['response_msg'] = 'ok';
            } else {
                $data['response_msg'] = 'error';
            }
        } else {
            $data['command_error'] = 1;
        }
        $data['command_success'] = 1;
    }
}

/* Set show/hide host-details */

if ($command === 'host-details' && !empty($target_id)) {
    $host_details = [];
    $tdata['host_details'] = [];

    $host_details = get_host_detail_view_data($ctx, $target_id);
    if (valid_array($host_details)) {
        $tdata['host_details'] = $host_details;
        $tdata['host_details']['host_logs'] = $frontend->getTpl(
            'term',
            [
                'term_logs' => '',
                'host_id' => $target_id
            ]
        );
        order_by_name($cfg['os']);
        order_by_name($cfg['manufacture']);
        order_by_name($cfg['system_type']);
        unset($tdata['host_details']['ping_stats']);
        $data['host_details']['cfg']['place'] = "#left-container";
        $data['host_details']['data'] = $frontend->getTpl('host-details', $tdata);
        $data['command_success'] = 1;
    } else {
        $data['command_error'] = 1;
        $data['command_error_msg'] .= 'Invalid host-details array';
    }
}

if ($command == 'saveNote' && !empty($target_id) && !empty($value_command)) {
    //For empty note we must write ':clear' to begin to prevent clean the note
    //if a filter or other return false/empty
    $content = urldecode($value_command);
    if (str_starts_with($content, ":clear")) {
        $content = '';
    }
    $set = ['content' => $content];
    $where = ['id' => $target_id];
    $db->update('notes', $set, $where, 'LIMIT 1');
    $data['command_success'] = 1;
}

if ($command == 'setHighlight' && !empty($target_id)) {
    $value = (empty($value_command)) ? 0 : 1;

    $hosts->update($target_id, ['highlight' => $value]);
    $data['command_success'] = 1;
    $data['response_msg'] = 'Changed to ' . $value;
}

/* Bookmarks */
if ($command == 'removeBookmark' && !empty($target_id)) {
    if ($ctx->get('Items')->remove($target_id)) {
        $data['response_msg'] = $target_id;
    } else {
        $data['response_msg'] = -1;
    }
    $data['command_success'] = 1;
}

if ($command == "mgmtBookmark" && !empty($target_id)) {
    if (empty($categories)) :
        $categories = $ctx->get('Categories');
    endif;

    $tdata = [];
    $items = $ctx->get('Items');
    if (isset($command_values['action']) && $command_values['action'] === 'edit') {
        $tdata = $items->getById($target_id);
    }
    $tdata['web_categories'] = [];
    if (!empty($tdata['conf'])) {
        $conf = json_decode($tdata, true);
        $tdata['image_resource'] = $conf['image_resource'];
        $tdata['image_type'] = $conf['image_type'];
    }
    if ($categories !== null) :
        $tdata['web_categories'] = $categories->getByType(2);
    endif;

    $tdata['local_icons'] = getLocalIconsData($cfg, 'bookmark_img/');
    if (isset($command_values['action']) && $command_values['action'] === 'edit') {
        $tdata['bookmark_buttonid'] = 'updateBookmark';
        $tdata['bookmark_title'] = $lng['L_EDIT'];
    } elseif (isset($command_values['action']) && $command_values['action'] === 'add') {
        $tdata['bookmark_buttonid'] = 'addBookmark';
        $tdata['bookmark_title'] = $lng['L_ADD'];
    }
    $data['response_msg'] = $target_id;
    $data['mgmt_bookmark']['cfg']['place'] = "#left-container";
    $data['mgmt_bookmark']['data'] = $frontend->getTpl('mgmt-bookmark', $tdata);
    $data['command_success'] = $target_id;
}
/* /END Bookmarks */

/* Host and Bookmarks create category */
if (
    ($command == 'submitBookmarkCat' || $command == 'submitHostsCat') &&
    !empty($value_command)
) {
    $cat_type = ($command == 'submitBookmarkCat') ? 2 : 1;
    $response = $ctx->get('Categories')->create($cat_type, $value_command);

    if ($response['success'] == 1) :
        $data['command_success'] = 1;
        $data['response_msg'] = $response['msg'];
    else :
        $data['command_success'] = $response['success'];
        $data['response_msg'] = $response['msg'];
        $data['command_error_msg'] = $response['msg'];
    endif;
}

if (
    ($command == 'removeBookmarkCat' || $command == 'removeHostsCat') && !empty($target_id)
) {
    /*
     * 1 Host Cats 2 Bookmarks Cat
     * id 50 is L_OTHERS cant delete
     */
    $cat_type = ($command == 'removeBookmarkCat') ? 2 : 1;
    if ($cat_type === 2 && $target_id === 50) {
        $data['command_error_msg'] = $lng['L_ERR_CAT_NODELETE'];
    } elseif ($cat_type === 1 && $target_id === 1) {
        $data['command_error_msg'] = $lng['L_ERR_CAT_NODELETE'];
    } elseif ($ctx->get('Categories')->remove($target_id)) {
        //Set to default all elements
        if ($cat_type == 1) {
            $db->update('hosts', ['category' => 1], ['category' => $target_id]);
        } else {
            $db->update('items', ['cat_id' => 50], ['cat_id' => $target_id]);
        }
        $data['response_msg'] = $target_id;
    } else {
        $data['command_error_msg'] = $lng['L_ERROR'];
    }
    //$data['force_hosts_refresh'] = 1;
    $data['command_success'] = 1;
}

/* Host External submit */
if ($command == 'submitHost' && !empty($value_command)) {
    $host = [];
    $host['hostname'] = Filters::varDomain($value_command);

    if (!empty($host['hostname'])) {
        $host['ip'] = $hosts->getHostnameIP($host['hostname']);
    } else {
        $host['ip'] = $value_command;
    }

    if (!empty($host['ip']) && !$ctx->get('Networks')->isLocal($host['ip'])) {
        $network_match = $ctx->get('Networks')->matchNetwork($host['ip']);
        if (valid_array($network_match)) {
            if ($hosts->getHostByIP($host['ip'])) {
                $data['command_error'] = 1;
                $data['command_error_msg'] = $lng['L_ERR_DUP_IP'];
            } else {
                $host['network'] = $network_match['id'];
                $hosts->addHost($host);
                $data['command_success'] = 1;
                $data['response_msg'] = $lng['L_OK'];
            }
        } else {
            $data['command_error'] = 1;
            $data['command_error_msg'] = $lng['L_ERR_NOT_NET_CONTAINER'];
        }
    } else {
        $data['command_error'] = 1;
        $data['command_error_msg'] = $lng['L_ERR_NOT_INTERNET_IP'] . $host['ip'];
    }
}

/* Power ON/OFF  & Reboot */
if ($command == 'power_on' && !empty($target_id)) {
    $host = $hosts->getHostById($target_id);

    if (valid_array($host) && !empty($host['mac'])) {
        sendWOL($host['mac']);
        $data['command_success'] = 1;
    } else {
        $err_msg = "Host {$host['ip']} has not mac address";
        Log::warning($err_msg);
        $data['command_error_msg'] .= $err_msg;
    }
}

if ($command == 'change_bookmarks_tab') {
    $user->setPref('default_bookmarks_tab', $target_id);
    $data['command_success'] = 1;
}

/* Logs Host */
if (
    $command === 'logs-reload' ||
    ($command === 'changeHDTab' && $value_command == 'tab9')
) {
    if (!empty($command_values['log_size']) && is_numeric($command_values['log_size'])) :
        $opts['max_lines'] = $command_values['log_size'];
    else :
        $opts['max_lines'] = $cfg['term_max_lines'];
    endif;

    if (isset($command_values['log_level']) && is_numeric($command_values['log_level'])) :
        if ($command_values['log_level'] >= 0) :
            $opts['log_level'] = $command_values['log_level'];
        endif;
    endif;

    $logs = Log::getLoghost($target_id, $opts);
    if (!empty($logs)) {
        $data['response_msg'] = format_host_logs($ctx, $logs);
    }
    $data['command_success'] = 1;
}

/* Metrics */
if ($command === 'changeHDTab' && $value_command == 'tab10') {
    $ping_stats = get_host_metrics($ctx, $target_id);
    if (!empty($ping_stats)) {
        $data['response_msg'] = $frontend->getTpl('chart-time-js', $ping_stats);
    }

    $data['command_success'] = 1;
}

/* Alarms */
if ($command === 'clearHostAlarms' && $target_id > 0) {
    if ($hosts->clearHostAlarms($user->getUsername(), $target_id)) {
        $data['command_success'] = 1;
        $data['force_hosts_refresh'] = 1;
    }
}
if ($command === 'setHostAlarms' && $target_id > 0) {
    $msg = $hosts->setAlarms($target_id, $value_command);
    $data['command_success'] = 1;
    $data['response_msg'] = $value_command;
}
if ($command === 'toggleMailAlarms' && $target_id > 0) {
    $msg = $hosts->setEmailAlarms($target_id, $value_command);
    $data['command_success'] = 1;
    $data['response_msg'] = $value_command;
}

if (
    $target_id > 0 &&
    in_array($command, [
        "alarm_ping_disable",
        "alarm_port_disable",
        "alarm_macchange_disable",
        "alarm_newport_disable",
    ])
) {
    $msg = $hosts->toggleAlarmType($target_id, $command, $value_command);
    $data['command_success'] = 1;
    $data['response_msg'] = $msg;
}

if (
    $target_id > 0 &&
    in_array($command, [
    "alarm_ping_email",
    "alarm_port_email",
    "alarm_macchange_email",
    "alarm_newport_email",
    ])
) {
    $msg = $hosts->toggleEmailAlarmType($target_id, $command, $value_command);
    $data['command_success'] = 1;
    $data['response_msg'] = $msg;
}

if ($command === 'updateAlertEmailList' && $target_id > 0) {
    $msg = $hosts->setEmailList($target_id, $value_command);
    $data['command_success'] = 1;
    $data['response_msg'] = $msg;
}

/* Submit Forms */
if ($command === 'submitform') {
    if (!isset($ncfg)) :
        $ncfg = $ctx->get('Config');
    endif;

    unset($command_values['id']);

    // TODO 1111: Filter/check values
    $changes = $ncfg->setMultiple($command_values);
    $data['command_success'] = 1;
    $data['response_msg'] = $changes;
    $data['response_msg2'] = $command_values;
}

/* Ansible */
if ($command == 'setHostAnsible' && !empty($value_command) && !empty($target_id)) {
    $hosts->update($target_id, ['ansible_enabled' => $value_command]);
    $data['command_success'] = 1;
    $data['response_msg'] = $value_command;
}

if ($command == 'playbook_exec' && !empty($target_id) && !empty($value_command)) {
    $host = $hosts->getHostById($target_id);
    $playbook = $value_command;
    if (valid_array($host) && $host['ansible_enabled']) {
        // Verificar si extra_vars está presente y no vacío
        if (!isEmpty($command_values['extra_vars'])) {
            $extra_vars = $command_values['extra_vars'];
        } else {
            $extra_vars = $command_values['extra_vars'];
        }

        if ($playbook == 'install-monnet-agent-linux') :
            if (empty($host['token'])) :
                $token = $hosts->createHostToken($target_id);
            else :
                $token = $host['token'];
            endif;
            $agent_config = [
                "id" => $host['id'],
                "token" => $token,
                "loglevel" => 'info',
                "default_interval" => $cfg['agent_refresh_interval'],
                "ignore_cert" => $cfg['agent_allow_selfcerts'],
                "server_host" => $_SERVER['HTTP_HOST'], //TODO Filter?
                "server_endpoint" => "/feedme.php",
            ];

            $extra_vars['agent_config'] = json_encode($agent_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        endif;
        $response = ansible_playbook($ctx, $host, $playbook, $extra_vars);
        if ($response['status'] === "success") {
            $data['command_success'] = 1;
            $data['response_msg'] = $response;
        } else {
            $data['command_error'] = 1;
            $data['command_error_msg'] = $response['error_msg'];
        }
    } else {
        $data['command_error'] = 1;
        $data['command_error_msg'] = $lng['L_ACCESS_METHOD'];
    }
}

if (
    ($command == 'reboot' || $command == 'shutdown') &&
    !empty($target_id)
) {
    $host = $hosts->getHostById($target_id);
    $playbook = $command . '-linux';
    if (valid_array($host) && $host['ansible_enabled']) {
        $response = ansible_playbook($ctx, $host, $playbook);
        if ($response['status'] === "success") {
            $data['command_success'] = 1;
            $data['response_msg'] = $response;
        } else {
            $data['command_error'] = 1;
            $data['command_error_msg'] = $response['error_msg'];
        }
    } else {
        $data['command_error'] = 1;
        $data['command_error_msg'] = $lng['L_ACCESS_METHOD'];
    }
}

if (
    ($command === 'syslog-load' || $command === 'journald-load') &&
    !empty($target_id)
) {
    $host = $hosts->getHostById($target_id);
    if ($command === 'syslog-load') {
        $playbook = 'syslog-linux';
    } else {
        $playbook = 'journald-linux';
    }
    if (valid_array($host) && $host['ansible_enabled']) {
        $extra_vars = [];
        if (is_numeric($value_command)) {
            $extra_vars['num_lines'] = $value_command;
        }
        $response = ansible_playbook($ctx, $host, $playbook, $extra_vars);
        if ($response['status'] === "success") {
            $debug_lines = [];
            $host_ip = $host['ip'];
            foreach ($response['plays'] as $play) {
                foreach ($play['tasks'] as $task) {
                    if (isset($task['hosts'][$host_ip]['action']) && $task['hosts'][$host_ip]['action'] === 'debug') {
                        $debug_lines = $task['hosts'][$host_ip]['msg'] ?? [];
                        foreach ($debug_lines as &$debug_line) :
                            $debug_line = $debug_line . '<br/>';
                        endforeach;
                        //$debug_lines[] =  serialize($task['hosts']);
                    }
                }
            }
            $data['command_success'] = 1;
            $data['response_msg'] = $debug_lines;
        } else {
            $hosts->setAnsibleAlarm($target_id, $response['error_msg']);
            $data['command_error'] = 1;
            $data['command_error_msg'] = $response['error_msg'];
        }
    } else {
        $data['command_error'] = 1;
        $data['command_error_msg'] = $lng['L_ACCESS_METHOD'];
    }
}

if (
    $command === 'report_ansible_hosts' ||
    $command === 'report_ansible_hosts_off' ||
    $command === 'report_ansible_hosts_fail' ||
    $command === 'report_agents_hosts' ||
    $command === 'report_agents_hosts_off' ||
    $command === 'report_agents_hosts_missing_pings'
) {
    $keysToShow = ["id", "display_name", "ip" , "online"];

    if ($command === 'report_ansible_hosts') :
        $tdata['hosts'] = $hosts->getAnsibleHosts();
    elseif ($command === 'report_ansible_hosts_off') :
        $tdata['hosts'] = $hosts->getAnsibleHosts(0);
    elseif ($command === 'report_ansible_hosts_fail') :
        $tdata['hosts'] = $hosts->getAnsibleHosts(2);
    elseif ($command === 'report_agents_hosts') :
        $tdata['hosts'] = $hosts->getAgentsHosts();
    elseif ($command === 'report_agents_hosts_off') :
        $tdata['hosts'] = $hosts->getAgentsHosts(0);
    elseif ($command === 'report_agents_hosts_missing_pings') :
        $tdata['hosts'] = $hosts->getAgentsHosts(2);
    endif;


    $availableKeys = array_keys($tdata['hosts'][0] ?? []);
    $tdata['keysToShow'] = array_intersect($keysToShow, $availableKeys);

    if (empty($tdata['hosts'])) :
        $data['response_msg'] = "No results";
    else :
        $data['response_msg'] = $frontend->getTpl("hosts-report", $tdata);
    endif;

    $data['command_success'] = 1;
}
print json_encode($data, JSON_UNESCAPED_UNICODE);
