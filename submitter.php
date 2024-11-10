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
 * @var array $lng
 * @var Database|null $db An instance of Database or null if not defined
 * @var array $cfg
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
    $data['command_error_msg'] = 'Command is empty or not a string';
}

$comm_values = Filters::sanArray('command_values', 'post');

if (empty($comm_values) || !is_array($comm_values)) {
    $data['command_error_msg'] = 'Command values is empty or not an array';
}

//ID is mandatory, must send 0 if not apply
if (!isset($comm_values['id'])) {
    $data['command_error_msg'] = 'Id field is mandatory';
} else {
    $target_id = Filters::varInt($comm_values['id']);
}

if ($command == 'saveNote') {
    $command_value = trim(Filters::varUTF8($comm_values['value']));
} elseif ($command == 'submitScanPorts') {
    $command_value = trim(Filters::varCustomString($comm_values['value'], ',/', 255));
} elseif ($command == 'setCheckPorts' || $command == 'submitHostTimeout') {
    $command_value = Filters::varInt($comm_values['value']);
} elseif ($command == 'addNetwork') {
    $command_value = Filters::varCustomString($comm_values['value'], ',":.{}'); //JSON only special chars
} elseif ($command == 'addBookmark') {
    $command_value = Filters::varCustomString($comm_values['value'], ',":.{}/_'); //Json + url chars
} elseif ($command == 'submitHost') {
    $command_value = Filters::varIP($comm_values['value']);
    if (empty($command_value)) {
        $command_value = Filters::varDomain($comm_values['value']);
    }
} elseif (!empty($comm_values['value'])) {
    if (Filters::varInt($comm_values['value'])) {
        $command_value = Filters::varInt($comm_values['value']);
    } else {
        $command_value = Filters::varString($comm_values['value']);
    }
}

if (!empty($command)) :
    $data['command_receive'] = $command;
endif;
if (!empty($command_value)) :
    $data['command_value'] = $command_value;
endif;
if (!is_numeric($target_id)) :
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
    $data['host_details'] = '';
    $command = $target_id = '';
    $data['command_success'] = 1;
    $data['force_host_refresh'] = 1;
    $data['response_msg'] = 'Host removed: ' . $target_id;
}

if ($command == 'network_select' && !empty($target_id)) {
    $pref_name = 'network_select_' . $target_id;
    $user->setPref($pref_name, 1);
    $data['command_success'] = 1;
    $data['force_host_refresh'] = 1;
    $data['response_msg'] = 'Network Select';
}
if ($command == 'network_unselect' && !empty($target_id)) {
    $pref_name = 'network_select_' . $target_id;
    $user->setPref($pref_name, 0);
    $data['command_success'] = 1;
    $data['force_host_refresh'] = 1;
    $data['response_msg'] = 'Network Unselect';
}

if ($command == 'setCheckPorts' && !empty($command_value) && !empty($target_id)) {
    // 1 ping 2 TCP/UDP
//    ($command_value == 0) ? $value = 1 : $value = 2;

    $hosts->update($target_id, ['check_method' => $command_value]);
    $data['command_success'] = 1;
    $data['response_msg'] = $command_value;
    $data['response_msg'] = 'ok';
}

if ($command == 'submitHostToken' && !empty($target_id)) {
    $token = create_token();
    $hosts->update($target_id, ['token' => $token]);
    $data['response_msg'] = $token;
    $data['command_success'] = 1;
}
if ($command == 'submitScanPorts' && !empty($target_id)) {
    $success_msg = '';
    if (!empty($command_value)) {
        $valid_ports = validatePortsInput(trim($command_value));
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
    if (!empty($command_value)) {
        $hosts->update($target_id, ['title' => $command_value]);
        $success = 1;
    }
    $data['command_success'] = $success;
    $data['force_host_refresh'] = 1;
    $data['response_msg'] = 'ok';
}

if ($command == 'submitOwner' && !empty($target_id)) {
    $success = 0;
    if (!empty($command_value)) {
        $hosts->update($target_id, ['owner' => $command_value]);
        $success = 1;
    }
    $data['command_success'] = $success;
    $data['response_msg'] = 'ok';
}

if ($command == 'submitHostTimeout' && !empty($target_id)) {
    $success = 0;
    if (!empty($command_value)) {
        $hosts->update($target_id, ['timeout' => $command_value]);
        $success = 1;
    }
    $data['command_success'] = $success;
    $data['response_msg'] = 'ok';
}

// Change Host Cat
if ($command == 'submitCat' && !empty($target_id)) {
    $success = 0;
    if (!empty($command_value)) {
        $hosts->update($target_id, ['category' => $command_value]);
        $success = 1;
    }
    $data['command_success'] = $success;
    $data['response_msg'] = 'Category changed to ' . $command_value;
    $data['force_host_refresh'] = 1;
}


if ($command == 'submitManufacture' && !empty($target_id)) {
    $success = 0;
    if (!empty($command_value)) {
        $hosts->update($target_id, ['manufacture' => $command_value]);
        $success = 1;
    }
    $data['command_success'] = $success;
    $data['response_msg'] = 'Manufacture changed to ' . $command_value;
    $data['force_host_refresh'] = 1;
}

if ($command == 'submitOS' && !empty($target_id)) {
    $success = 0;
    if (!empty($command_value)) {
        $hosts->update($target_id, ['os' => $command_value]);
        $success = 1;
    }
    $data['command_success'] = $success;
    $data['response_msg'] = 'OS changed to ' . $command_value;
    $data['force_host_refresh'] = 1;
}

if ($command == 'submitSystemType' && !empty($target_id)) {
    $success = 0;
    if (!empty($command_value)) {
        $hosts->update($target_id, ['system_type' => $command_value]);
        $success = 1;
    }
    $data['command_success'] = $success;
    $data['response_msg'] = 'System Type changed to ' . $command_value;
    $data['force_host_refresh'] = 1;
}

if ($command === 'submitAccessLink' && !empty($target_id)) {
    $success = 5;

    if (!empty($comm_values['value'])) {
        $command_value = Filters::varUrl($comm_values['value']);
        if (!empty($command_value)) {
            $hosts->update($target_id, ['access_link' => trim($command_value)]);
            $data['response_msg'] = "link updated";
            $success = 1;
        } else {
            $data['command_error_msg'] = "Wrong value";
        }
    }
    //clear_field
    if (empty($comm_values['value'])) {
        $hosts->update($target_id, ['access_link' => $command_value]);
        $data['response_msg'] = "link cleared";
        $success = 1;
    }
    $data['command_success'] = $success;
}

if ($command === 'submitAccessType' && !empty($target_id)) {
    $success = 0;
    if (!empty($command_value)) {
        $hosts->update($target_id, ['access_type' => $command_value]);
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
    $data['force_host_refresh'] = 1;
}

if ($command == 'show_host_cat' && !empty($target_id)) {
    $user->toggleHostsCat($target_id);
    $data['command_success'] = 1;
    $data['force_host_refresh'] = 1;
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
    $data['categories_host']['cfg']['place'] = '#left_container';
}

/* /end Host Cat */

/** @var array<string> $new_network */
$new_network = [];
/* ADD NETWORK */
if (
    $command == 'addNetwork' &&
    !empty($command_value)
) {
    $decodedJson = json_decode($command_value, true);

    if ($decodedJson === null) {
        $data['command_error_msg'] .= 'JSON Invalid<br/>';
    } else {
        foreach ($decodedJson as $key => $dJson) {
            ($key == 'networkVLAN') ? $key = 'vlan' : null;
            ($key == 'networkScan') ? $key = 'scan' : null;
            ($key == 'networkName') ? $key = 'name' : null;
            $new_network[$key] = trim($dJson);
        }
        if ($new_network['networkCIDR'] == 0 && $new_network['network'] != '0.0.0.0') {
            $data['command_error_msg'] .= $lng['L_MASK'] .
                ' ' . $new_network['networkCIDR'] .
                ' ' . $lng['L_NOT_ALLOWED'] . '<br/>';
        }
        $network_plus_cidr = $new_network['network'] . '/' . $new_network['networkCIDR'];
        unset($new_network['networkCIDR']);
        $new_network['network'] = $network_plus_cidr;

        if (!Filters::varNetwork($network_plus_cidr)) {
            $data['command_error_msg'] .= $lng['L_NETWORK'] . ' ' . $lng['L_INVALID'] . '<br/>';
        }
        if (!is_numeric($new_network['vlan'])) {
            $data['command_error_msg'] .= 'VLAN ' . "{$lng['L_MUST_BE']} {$lng['L_NUMERIC']}<br/>";
        }
        if (!is_numeric($new_network['scan'])) {
            $data['command_error_msg'] .= 'Scan ' . "{$lng['L_MUST_BE']} {$lng['L_NUMERIC']}<br/>";
        }

        $networks_list = $ctx->get('Networks')->getNetworks();
        foreach ($networks_list as $net) {
            if ($net['name'] == $new_network['name']) {
                $data['command_error_msg'] = 'Name must be unique<br/>';
            }
            if ($net['network'] == $network_plus_cidr) {
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
    !empty($command_value)
) {
    $decodedJson = json_decode($command_value, true);

    if ($decodedJson === null) {
        $data['command_error_msg'] .= 'JSON Invalid<br/>';
    } else {
        foreach ($decodedJson as $key => $dJson) {
            $new_bookmark[$key] = trim($dJson);
        }

        if (!Filters::varString($new_bookmark['name'])) {
            $data['command_error_msg'] .= "{$lng['L_FIELD']} {$lng['L_NAME']} {$lng['L_ERROR_EMPTY_INVALID']}";
        }
        if (!Filters::varString($new_bookmark['image_type'])) {
            $data['command_error_msg'] .= "{$lng['L_FIELD']} {$lng['L_IMAGE_TYPE']} {$lng['L_ERROR_EMPTY_INVALID']}";
        }
        if (!Filters::varInt($new_bookmark['cat_id'])) {
            $data['command_error_msg'] .= "{$lng['L_FIELD']} {$lng['L_TYPE']} {$lng['L_ERROR_EMPTY_INVALID']}";
        }

        if (
            !Filters::varUrl($new_bookmark['urlip']) ||
            Filters::varIP($new_bookmark['urlip'])
        ) {
            $data['command_error_msg'] = "{$lng['L_FIELD']} {$lng['L_URLIP']} {$lng['L_ERROR_EMPTY_INVALID']}";
        }

        if (
            (!Filters::varInt($new_bookmark['weight'])) &&
            (Filters::varInt($new_bookmark['weight']) !== 0)
        ) {
            $data['command_error_msg'] = "{$lng['L_FIELD']} {$lng['L_WEIGHT']} {$lng['L_ERROR_EMPTY_INVALID']}";
        }

        if ($new_bookmark['image_type'] != 'favicon' && empty($new_bookmark['field_img'])) {
            $data['command_error_msg'] = "{$lng['L_LINK']} {$lng['L_ERROR_EMPTY_INVALID']}";
        }
        if ($new_bookmark['image_type'] == 'favicon' && empty($new_bookmark['field_img'])) {
            $data['command_error_msg'] = "{$lng['L_LINK']} {$lng['L_ERROR_INVALID']}";
        }
        if ($new_bookmark['image_type'] == 'local_img' && empty($new_bookmark['field_img'])) {
            $data['command_error_msg'] = "{$lng['L_LINK']} {$lng['L_ERROR_EMPTY_INVALID']}";
        }

        if (empty($data['command_error_msg'])) {
            if ($ctx->get('Items')->addItem('bookmarks', $new_bookmark)) {
                $data['response_msg'] = 'ok';
            } else {
                $data['response_msg'] = 'error';
            }
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
        if (!empty($host_details['ping_stats'])) {
            $tdata['host_details']['ping_graph'] = $frontend->getTpl('chart-time', $host_details['ping_stats']);
        }
        $log_lines = format_host_logs($ctx, $host_details['host_logs']);
        $tdata['host_details']['host_logs'] = $frontend->getTpl(
            'term',
            [
                'term_logs' => $log_lines,
                'host_id' => $target_id
            ]
        );
        order_by_name($cfg['os']);
        order_by_name($cfg['manufacture']);
        order_by_name($cfg['system_type']);
        unset($tdata['host_details']['ping_stats']);
        $data['host_details']['cfg']['place'] = "#left_container";
        $data['host_details']['data'] = $frontend->getTpl('host-details', $tdata);
        $data['command_success'] = 1;
    } else {
        $data['command_error_msg'] .= 'Invalid host-details array';
    }
}

if ($command == 'saveNote' && !empty($target_id) && !empty($command_value)) {
    //For empty note we must write ':clear' to begin to prevent clean the note
    //if a filter or other return false/empty
    $content = urldecode($command_value);
    if (str_starts_with($content, ":clear")) {
        $content = '';
    }
    $set = ['content' => $content];
    $where = ['id' => $target_id];
    $db->update('notes', $set, $where, 'LIMIT 1');
    $data['command_success'] = 1;
}

if ($command == 'setHighlight' && !empty($target_id)) {
    $value = (empty($command_value)) ? 0 : 1;

    $hosts->update($target_id, ['highlight' => $value]);
    $data['command_success'] = 1;
    $data['response_msg'] = 'Changed to ' . $value;
}

/* Bookmarks */
if ($command == 'removeBookmark' && !empty($target_id)) {
    if ($ctx->get('Items')->remove($target_id)) {
        $data['response_msg'] = 'ok';
    } else {
        $data['response_msg'] = 'fail';
    }
    $data['command_success'] = 1;
}

/* /END Bookmarks */

/* Host and Bookmarks create category */
if (
    ($command == 'submitBookmarkCat' || $command == 'submitHostsCat') &&
    !empty($command_value)
) {
    $cat_type = ($command == 'submitBookmarkCat') ? 2 : 1;
    $response = $ctx->get('Categories')->create($cat_type, $command_value);
    ($response['success']) ? $data['response_msg'] = $response['msg'] :
            $data['command_error_msg'] = $response['msg'];

    $data['command_success'] = 1;
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
        $data['command_response_msg'] = $lng['L_OK'];
    } else {
        $data['command_error_msg'] = $lng['L_ERROR'];
    }

    $data['command_success'] = 1;
}

/* Host External submit */
if ($command == 'submitHost' && !empty($target_id)) {
    $host = [];
    $host['hostname'] = Filters::varDomain($command_value);

    if (!empty($host['hostname'])) {
        $host['ip'] = $hosts->getHostnameIP($host['hostname']);
    } else {
        $host['ip'] = $command_value;
    }

    if (!empty($host['ip']) && !$ctx->get('Networks')->isLocal($host['ip'])) {
        $network_match = $ctx->get('Networks')->matchNetwork($host['ip']);
        if (valid_array($network_match)) {
            if ($hosts->getHostByIP($host['ip'])) {
                $data['command_error_msg'] = $lng['L_ERR_DUP_IP'];
            } else {
                $host['network'] = $network_match['id'];
                $hosts->addHost($host);
                $data['response_msg'] = $lng['L_OK'];
            }
        } else {
            $data['command_error_msg'] = $lng['L_ERR_NOT_NET_CONTAINER'];
        }
    } else {
        $data['command_error_msg'] = $lng['L_ERR_NOT_INTERNET_IP'] . $host['ip'];
    }
    $data['command_success'] = 1;
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
if ($command == 'power_off' && !empty($target_id)) {
    $result = $db->select('cmd', 'cmd_id', ['cmd_type' => 2, 'hid' => $target_id], 'LIMIT 1');
    $coincidence = $db->fetchAll($result);

    if (empty($coincidence)) {
        $db->insert('cmd', ['cmd_type' => 2, 'hid' => $target_id]);
    }
}
if ($command == 'reboot' && !empty($target_id)) {
    $result = $db->select('cmd', 'cmd_id', ['cmd_type' => 1, 'hid' => $target_id], 'LIMIT 1');
    $coincidence = $db->fetchAll($result);

    if (empty($coincidence)) {
        $db->insert('cmd', ['cmd_type' => 1, 'hid' => $target_id]);
    }
    $data['command_success'] = 1;
}

if ($command == 'change_bookmarks_tab') {
    $user->setPref('default_bookmarks_tab', $target_id);
    $data['command_success'] = 1;
}

if ($command == 'logs-reload' && !empty($target_id)) {
    if (!empty($comm_values['log_size']) && is_numeric($comm_values['log_size'])) :
        $opts['max_lines'] = $comm_values['log_size'];
    else :
        $opts['max_lines'] = $cfg['term_max_lines'];
    endif;

    if (isset($comm_values['log_level']) && is_numeric($comm_values['log_level'])) :
        if ($comm_values['log_level'] >= 0) :
            $opts['log_level'] = $comm_values['log_level'];
        endif;
    endif;

    $logs = Log::getLoghost($target_id, $opts);
    $data['response_msg'] = format_host_logs($ctx, $logs);
    $data['command_success'] = 1;
}
print json_encode($data, JSON_UNESCAPED_UNICODE);
