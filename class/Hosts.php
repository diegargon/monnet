<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

class Hosts
{
    /** @var int */
    public int $totals = 0;
    /** @var int */
    public int $total_on = 0;
    /** @var int */
    public int $total_off = 0;
    /** @var int */
    public int $highlight_total = 0;
    /** @var int */
    public int $ansible_hosts = 0;
    /** @var int */
    public int $ansible_hosts_off = 0;
    /** @var int */
    public int $ansible_hosts_fail = 0;
    /** @var int */
    public int $agents = 0;
    /** @var int */
    public int $agents_off = 0;
    /** @var int */
    public int $agents_missing_pings = 0;
    /**  @var int */
    public int $hypervisor_rols = 0;
    /**  @var int */
    public int $alerts = 0;
    /**  @var int */
    public int $warns = 0;
    /** @var Database $db */
    private Database $db;

    /**
     *  List of field we save in json field misc.
     *  @var array<string> $misc_keys
     */
    private array $misc_keys = [
        'mac_vendor',
        'manufacture',
        'machine_type',
        'sys_availability',
        'system_type',
        'os',
        'os_version',
        'owner',
        'access_type',
        'access_link',
        'timeout',
        'disable_alarms',
        'email_alarms',
        'disable_ping',
        'alarm_ping_disable',
        'alarm_port_disable',
        'alarm_macchange_disable',
        'alarm_newport_disable',
        'enableMailAlarms',
        'alarm_ping_email',
        'alarm_port_email',
        'alarm_macchange_email',
        'alarm_newport_email',
        'email_list',
        'agent_installed', /* Setting at first ping */
        'agent_online',
        'agent_next_report', /* Timesstamp for next report */
        'agent_last_contact',
        'agent_version',
        'load_avg',
        'mem_info',
        'disks_info',
        'ncpu',
        'uptime',
        'iowait',
    ];

    /**
     * List of ignore/not keep in database keys
     * @var array<string> $db_ignore_keys
     */
    private array $db_ignore_keys = [
        'agent_missing_pings'
    ];
    /**
     * host[$id] = ['key' => 'value']
     * json field is decode and encode on load/update ($misc_keys)
     * @var array<int, array<string, mixed>> $hosts
     */
    private $hosts = [];

    /** @var array<string> $lng */
    private array $lng = [];

    /** @var AppContext $ctx */
    private AppContext $ctx;
    /** @var Config $ncfg */
    private Config $ncfg;
    /**
     *
     * @var array<int, int> $host_cat_track
     */
    private array $host_cat_track = [];

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->db = $ctx->get('Mysql');
        $this->lng = $ctx->get('lng');
        $this->ncfg = $ctx->get('Config');
        $this->setHostsDb();
    }

    /**
     *
     * @param array<string, mixed> $host
     * @return bool
     */
    public function addHost(array $host): bool
    {
        if (empty($host['ip']) && !empty($host['hostname'])) {
            if (!Filters::varDomain($host['hostname'])) :
                return false;
            endif;
            $host['ip'] = gethostbyname($host['hostname']);
            if (!$host['ip']) :
                return false;
            endif;
        }

        $this->insert($host);

        return true;
    }

    /**
     *
     * @return array<int, array<string, mixed>>
     */
    public function getknownEnabled(): array
    {
        /**
         * @var array<int, array<string, mixed>> $hosts
         */
        $hosts = [];

        foreach ($this->hosts as $host) {
            if (empty($host['disable'])) :
                $hosts[] = $host;
            endif;
        }

        return $hosts;
    }

    /**
     *
     * @param int $highligth
     * @return array<int, array<string, mixed>>
     */
    public function getHighlight(int $highligth = 1): array
    {
        $hosts = $this->getknownEnabled();
        foreach ($hosts as $khost => $vhost) {
            if ($vhost['highlight'] != $highligth) :
                unset($hosts[$khost]);
            endif;
        }

        return $hosts;
    }

    /**
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAll(): array
    {
        return $this->hosts;
    }

    /**
     *
     * @param int $id
     * @param array<string, mixed> $values
     * @return void
     */
    public function update(int $id, array $values): void
    {
        /** @var array<int, array<string, mixed>> $fvalues */
        $fvalues = []; //filter
        /** @var array<int, array<string, mixed>> $misc_container misc json field key/values */
        $misc_container = [];

        foreach ($values as $kvalue => $vvalue) {
            if (!empty($kvalue) && isset($vvalue)) {
                //TODO warning signs
                //Log change
                if (
                        empty($this->hosts[$id]['disable_alarms']) &&
                        ($kvalue === 'mac' || $kvalue === 'mac_vendor' || $kvalue === 'hostname') &&
                        ($this->hosts[$id][$kvalue] != $vvalue)
                ) {
                    $loghostmsg = $this->lng['L_HOST_MSG_DIFF'] . ' ( '
                            . $this->hosts[$id]['display_name'] . ' )([' . $kvalue . '])'
                            . $this->hosts[$id][$kvalue] . '->' . $vvalue;
                    Log::logHost('LOG_ALERT', $id, $loghostmsg);
                    $this->hosts[$id]['alert'] = 1;
                    $alert_msg = '';
                    if ($kvalue === 'mac') :
                        $alert_msg = 'Mac ' . $this->lng['L_HAS_CHANGED'];
                    endif;
                    if ($kvalue === 'mac_vendor') :
                        $alert_msg = 'Mac vendor ' . $this->lng['L_HAS_CHANGED'];
                    endif;
                    if ($kvalue === 'hostname') :
                        $alert_msg = 'Hostname ' . $this->lng['L_HAS_CHANGED'];
                    endif;
                    Log::loghost('LOG_ALERT', $id, $alert_msg, 3);
                    if (!empty($this->hosts[$id]['alarm_macchange_email'])) :
                        $this->sendHostMail($id, $alert_msg, $alert_msg);
                    endif;
                }
                //Log category change
                if ($kvalue == 'category' && $vvalue != $this->hosts[$id]['category']) {
                    Log::logHost('LOG_NOTICE', $id, 'Host ' . $this->hosts[$id]['display_name']
                        . ' change category ' . $this->hosts[$id]['category'] . '->' . $vvalue);
                    $this->host_cat_track[$this->hosts[$id]['category']]--;
                    $this->host_cat_track[$vvalue]++;
                }

                //Add the new values to the current array.
                $this->hosts[$id][$kvalue] = $vvalue;
                //misc is json field deal with it
                if (in_array($kvalue, $this->misc_keys)) {
                    $misc_container[$kvalue] = $vvalue;
                } else {
                    if (!in_array($kvalue, $this->db_ignore_keys)) :
                        $fvalues[$kvalue] = $vvalue;
                    endif;
                }
            }
        }

        /*
         * To update the misc field (json) we add to the array the other fields
         * encode it and update all.
         */
        if (valid_array($misc_container)) {
            $host = $this->hosts[$id];
            foreach ($host as $h_key => $h_value) {
                if (
                    in_array($h_key, $this->misc_keys) &&
                    !array_key_exists($h_key, $misc_container)
                ) {
                    $misc_container[$h_key] = $h_value;
                }
            }

            $fvalues['misc'] = json_encode($misc_container);
        }
        if (valid_array($fvalues)) :
            $this->db->update('hosts', $fvalues, ['id' => ['value' => $id]], 'LIMIT 1');
        endif;
    }

    /**
     *
     * @param array<string, mixed> $host
     * @return void
     */
    public function insert(array $host): void
    {
        $misc_container = [];

        if (!isset($host['hostname'])) {
            $hostname = $this->getHostname($host['ip']);
            if ($hostname) :
                $host['hostname'] = $hostname;
            endif;
        }
        //remove keys that we store in misc json field
        foreach ($host as $h_key => $h_value) {
            if (
                in_array($h_key, $this->misc_keys) &&
                !in_array($h_key, $misc_container)
            ) {
                $misc_container[$h_key] = $h_value;
                unset($host[$h_key]);
            }
        }
        if (count($misc_container) > 0) {
            $host['misc'] = json_encode($misc_container);
        }
        $this->db->insert('hosts', $host);

        $host_id = $this->db->insertID();
        $hostlog = $this->getDisplayName($host);
        if (!empty($host['mac_vendor']) && $host['mac_vendor'] !== '-') {
            $hostlog .= ' [' . $host['mac_vendor'] . ']';
        } elseif (!empty($host['mac'])) {
            $hostlog .= ' [' . $host['mac'] . ']';
        }
        $host['id'] = $host_id;
        $host['display_name'] = $this->getDisplayName($host);
        $this->hosts[$host_id] = $host;
        $network_name = $this->ctx->get('Networks')->getNetworkNameByID($host_id);
        Log::logHost('LOG_WARNING', $host_id, 'Found new host: '
            . $host['display_name'] . ' on network ' . $network_name, 4);
    }

    /**
     *
     * @param int $hid
     * @return void
     */
    public function remove(int $hid): void
    {
        Log::notice('Deleted host: ' . $this->hosts[$hid]['display_name']);
        $this->db->delete('hosts', ['id' => $hid], 'LIMIT 1');
        $this->db->delete('notes', ['host_id' => $hid], 'LIMIT 1');
        $this->db->delete('stats', ['host_id' => $hid], 'LIMIT 1');
        $this->db->delete('hosts_logs', ['host_id' => $hid], 'LIMIT 1');
        unset($this->hosts[$hid]);
    }

    /**
     *
     * @param int $id
     * @return array<string, mixed>
     */
    public function getHostById(int $id): ?array
    {

        if (empty($this->hosts[$id])) :
            return null;
        endif;
        $host = $this->hosts[$id];
        //TODO: Load notes on changetab
        $result = $this->db->select('notes', '*', ['id' => $host['notes_id']], 'LIMIT 1');
        $notes = $this->db->fetch($result);
        $host['notes'] = $notes['content'];
        $host['notes_date'] = $notes['update'];

        return $host;
    }

    /**
     *
     * @param string $ip
     * @return array<string>
     */
    public function getHostByIp(string $ip): ?array
    {
        foreach ($this->hosts as $host) {
            if ($host['ip'] == trim($ip)) :
                return $host;
            endif;
        }

        return null;
    }

    /**
     *
     * @param int $cat_id
     * @return array<int, array<string, mixed>>
     */
    public function getHostsByCat(int $cat_id): array
    {
        /**
         * @var array<int, array<string, mixed>> $hosts_by_cat
         */
        $hosts_by_cat = [];

        foreach ($this->hosts as $host) {
            if ($host['category'] == $cat_id) {
                $hosts_by_cat[] = $host;
            }
        }

        return $hosts_by_cat;
    }

    /**
     *
     * @param int $id
     * @return bool
     */
    public function catHaveHosts(int $id): bool
    {
        if (isset($this->host_cat_track[$id]) && $this->host_cat_track[$id] > 0) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param string $ip
     * @return string|false
     */
    public function getHostname(string $ip): string|false
    {
        $hostname = gethostbyaddr($ip);
        if ($hostname == $ip) {
            return false;
        }
        return $hostname;
    }

    /**
     *
     * @param string $domain
     * @return string
     */
    public function getHostnameIP(string $domain): string
    {
        return gethostbyname($domain);
    }

    /**
     *
     * @param int $network_id
     * @return array<string,mixed>
     */
    public function getHostsByNetworkId(int $network_id): array
    {
        $hosts = [];
        foreach ($this->hosts as $host) :
            $host['network'] == $network_id ? $hosts[] = $host : null;
        endforeach;

        return $hosts;
    }
    /**
     *
     * @param string $username
     * @param int $id
     * @return bool
     */
    public function clearHostAlarms(string $username, int $id): bool
    {
        $values = [
            'alert' => 0,
            'warn' => 0,
            'ansible_fail' => 0,
            ];
        $this->update($id, $values);
        Log::logHost('LOG_NOTICE', $id, $this->lng['L_CLEAR_ALARMS_BITS_BY'] . ': ' . $username);

        return true;
    }

    /**
     *
     * @param int $id
     * @param bool $value
     * @return bool
     */
    public function setAlarmState(int $id, bool $value): bool
    {
        $this->update($id, ['disable_alarms' => $value]);
        Log::logHost('LOG_NOTICE', $id, $this->lng['L_ALARMS_DISABLE']);
        return true;
    }

    /**
     *
     * @param int $id
     * @param string $msg
     * @param int $log_type
     * @return void
     */
    public function setAlertOn(int $id, string $msg, int $log_type = LT_ALERT): void
    {
        Log::logHost('LOG_ALERT', $id, $msg, $log_type);
        $this->update($id, ['alert' => 1]);
    }

    /**
     *
     * @param int $id
     * @param string $msg
     * @param int $log_type
     * @return void
     */
    public function setWarnOn(int $id, string $msg, int $log_type = LT_WARN): void
    {
        Log::logHost('LOG_WARNING', $id, $msg, $log_type);
        $this->update($id, ['warn' => 1]);
    }

    public function clearAlerts(): bool
    {
        foreach ($this->hosts as $host) :
            $id = $host['id'];
            if (!empty($host['alert'])) :
                $this->db->update('hosts', ['alert' => 0], ['id' => $id]);
                $this->hosts[$id]['alert'] = 0;
            endif;
        endforeach;

        return true;
    }
    /**
     *
     * @return bool
     */
    public function clearWarns(): bool
    {
        foreach ($this->hosts as $host) :
            $id = $host['id'];
            if (!empty($host['warn'])) :
                $this->db->update('hosts', ['warn' => 0], ['id' => $id]);
                $this->hosts[$id]['warn'] = 0;
            endif;
        endforeach;

        return true;
    }
    /**
     *
     * @param int $id
     * @param string $msg
     * @return void
     */
    public function setAnsibleAlarm(int $id, string $msg): void
    {
        $log_msg = 'Ansible alert: ' . $msg;
        Log::logHost('LOG_WARNING', $id, $log_msg, 3);
        $this->update($id, ['alert' => 1, 'ansible_fail' => 1]);
        $this->db->query("INSERT INTO `ansible_msg` ('host_id', 'msg') VALUES ($id, $msg)");
    }

    /**
     *
     * @param int $id
     * @param bool $value
     * @return bool
     */
    public function setEmailAlarms(int $id, bool $value): bool
    {
        $this->update($id, ['email_alarms' => $value]);

        return true;
    }

    /**
     *
     * @param int $id
     * @param string $command
     * @param int $value
     * @return bool
     */
    public function toggleAlarmType(int $id, string $command, int $value): bool
    {
        $this->update($id, [$command => $value]);

        return true;
    }

    /**
     *
     * @param int $id
     * @param string $command
     * @param int $value
     * @return bool
     */
    public function toggleEmailAlarmType(int $id, string $command, int $value): bool
    {
        $this->update($id, [$command => $value]);

        return true;
    }

    /**
     *
     * @param int $id
     * @param array<string> $value
     * @return bool
     */
    public function setEmailList(int $id, array $value): bool
    {
        $string = implode(",", $value);
        $this->update($id, ['email_list' => $string]);

        return true;
    }

    public function sendHostMail(int $id, string $subject, ?string $body = ''): void
    {
        if (
            $this->ncfg->get('mail') &&
            !empty($this->hosts[$id]['email_alarms']) &&
            !empty($this->hosts[$id]['email_list'])
        ) {
            $mails = explode(",", $this->hosts[$id]['email_list']);
            if (!isEmpty($mails)) {
                $mailer = $this->ctx->get('Mailer');
                if (isset($this->hosts[$id]['display_name'])) :
                    $body .= $this->hosts[$id]['display_name'] . "\n";
                endif;
                if (isset($this->hosts[$id]['hostname'])) :
                    $body .= $this->hosts[$id]['hostname'] . "\n";
                endif;
                if (isset($this->hosts[$id]['ip'])) :
                    $body .= $this->hosts[$id]['ip'] . "\n";
                endif;
                foreach ($mails as $mail) :
                    $mailer->sendMail($mail, $subject, $body);
                endforeach;
            }
        }
    }

    /**
     *
     * @param int $id
     * @return string|bool
     */
    public function createHostToken(int $id): string|bool
    {
        $token = create_token();
        if ($token) :
            $this->update($id, ['token' => $token]);
            return $token;
        endif;

        return false;
    }

    /**
     * Status (null All) (0 Off) (1 On) (2 Fail) - Returns hosts
     * @param int|null $status
     * @return list<array<string, mixed>>
     */
    public function getAnsibleHosts(?int $status = null): array
    {
        $result_hosts = [];

        if (!$this->ncfg->get('ansible')) :
            return [];
        endif;

        foreach ($this->hosts as $host) :
            if (empty($host['ansible_enabled'])) :
                continue;
            endif;
            // All
            if ($status === null) :
                $result_hosts[] = $host;
            endif;
            // Off
            if ($status === 0 && (int) $host['online'] === 0) :
                $result_hosts[] = $host;
            endif;
            // On
            if ($status === 1 && (int) $host['online'] === 1) :
                $result_hosts[] = $host;
            endif;
            // Fail
            if ($status === 2 && $host['ansible_fail']) :
                $result_hosts[] = $host;
            endif;
        endforeach;

        return $result_hosts;
    }
    /**
     * Status (null All) (0 Off) (1 Missing Pings)
     * @param int|null $status
     * @return list<array<string, mixed>>
     */
    public function getAgentsHosts(?int $status = null): array
    {
        $result_hosts = [];

        foreach ($this->hosts as $host) :
            if (empty($host['agent_installed'])) :
                continue;
            endif;
            // All
            if ($status === null) :
                $result_hosts[] = $host;
            endif;
            // Off
            if ($status === 0 && (int) $host['online'] === 0) :
                $result_hosts[] = $host;
            endif;
            // On
            if ($status === 1 && (int) $host['online'] === 1) :
                $result_hosts[] = $host;
            endif;
            // Ping Fail
            if ($status === 2 && !empty($host['agent_missing_pings'])) :
                $result_hosts[] = $host;
            endif;
        endforeach;

        return $result_hosts;
    }

    /**
     * Recogemos las alertas, quitamos duplicados y mostramos las 4 ultimas
     * @return array<int,array<string,string>>
     */
    public function getAlertHosts(): array
    {
        $result_hosts = [];

        $log_type_constants = $this->ncfg->get('log_type_constants');

        foreach ($this->hosts as $host) :
            $alert_logs_msgs = [];
            $alert_logs_items = [];
            $min_host = [
                'id' => $host['id'],
                'display_name' => $host['display_name'],
                'mac' => $host['mac'],
                'ip' => $host['ip'],
                'online' => $host['online'],
            ];

            if ($host['alert'] && empty($host['disable_alarms'])) :
                $log_type = [3, 5];

                $opt = [
                    'log_type' => $log_type,
                    'host_id' => $host['id'],
                ];
                $alert_logs = Log::getLogsHosts($opt);

                if (!empty($alert_logs)) :
                    foreach ($alert_logs as $item) :
                        if (!in_array($item['msg'], $alert_logs_msgs)) :
                            $alert_logs_msgs[] = $item['msg'];
                            $alert_logs_items[] = $item;
                        endif;
                    endforeach;
                    $alert_logs = array_slice($alert_logs_msgs, 0, 4);
                    $timezone = $this->ncfg->get('timezone');
                    $timeformat = $this->ncfg->get('datetime_format_min');
                    foreach ($alert_logs_items as $item) :
                        $date = utc_to_tz($item['date'], $timezone, $timeformat);
                        $min_host['log_msgs'][] = [
                            'log_id' => $item['id'],
                            'log_type' => array_search($item['log_type'], $log_type_constants),
                            'msg' => "{$item['msg']} - $date",
                            'ack_state' => $item['ack']
                        ];
                    endforeach;
                    $result_hosts[] = $min_host;
                else :
                    $min_host['alert_msg']  .= 'Alert logs are empty';
                endif;
            endif;
        endforeach;

        return $result_hosts;
    }

    /**
     * Recogemos los warnings, quitamos duplicados y mostramos las 4 ultimas
     * 2 port_wartn, 4 warn
     * @return array<int,array<string,string>>
     */
    public function getWarnHosts(): array
    {
        $result_hosts = [];

        $log_type_constants = $this->ncfg->get('log_type_constants');

        foreach ($this->hosts as $host) :
            $min_host = [
                'id' => $host['id'],
                'display_name' => $host['display_name'],
                'mac' => $host['mac'],
                'ip' => $host['ip'],
                'online' => $host['online'],
            ];

            if ($host['warn'] && empty($host['disable_alarms'])) :
                $log_type = [2, 4, 6];

                $opt = [
                    'log_type' => $log_type,
                    'host_id' => $host['id'],
                ];
                $warn_logs = Log::getLogsHosts($opt);
                $warn_logs_msgs = [];
                $warn_logs_items = [];

                if (!empty($warn_logs)) :
                    foreach ($warn_logs as $item) :
                        if (!in_array($item['msg'], $warn_logs_msgs)) :
                            $warn_logs_msgs[] = $item['msg'];
                            $warn_logs_items[] = $item;
                        endif;
                    endforeach;
                    $warn_logs_items = array_slice($warn_logs_items, 0, 4);
                    $timezone = $this->ncfg->get('timezone');
                    $timeformat = $this->ncfg->get('datetime_format_min');
                    foreach ($warn_logs_items as $item) :
                        $date = utc_to_tz($item['date'], $timezone, $timeformat);
                        $min_host['log_msgs'][] = [
                            'log_id' => $item['id'],
                            'log_type' => array_search($item['log_type'], $log_type_constants),
                            'msg' => "{$item['msg']} - $date",
                            'ack_state' => $item['ack']
                        ];
                    endforeach;
                    $result_hosts[] = $min_host;
                else :
                    $min_host['warn_msg']  .= 'Warn logs are empty';
                endif;
            endif;
        endforeach;

        return $result_hosts;
    }

    /**
     * Return all ports associate with this host id
     * @param int $hid
     * @return array<int,array<string,string|int>>
     */
    public function getAllHostPorts(int $hid): array
    {
        $result = $this->db->selectAll('ports', ['hid' => $hid]);

        return $this->db->fetchAll($result);
    }

    public function getHostScanPorts(int $hid, int $scan_type = 0): array
    {

        $result = $this->db->selectAll(
            'ports',
                [
                    'hid' => $hid,
                    'scan_type' => $scan_type
                ]
        );

        return $this->db->fetchAll($result);
    }

    public function updatePort(int $port_id, array $set): void
    {
        $this->db->update('ports', $set, ['id' => $port_id]);
    }

    public function addPort(array $insert_data): void
    {
        $this->db->insert('ports', $insert_data);
    }
    /**
     *
     * @param int $hid
     * @param array<string,string|int> $details
     * @return void
     */
    public function addRemoteScanHostPort(int $hid, array $details): void
    {
        empty($details['ip_version']) ? $details['ip_version'] = 1 : null;
        empty($details['service']) ? $details['service'] = 'unknown' : null;
        $insert = [
            'hid' => $hid,
            'scan_type' => 1,
            'protocol' => $details['protocol'],
            'pnumber' => $details['pnumber'],
            'last_change' => date_now(),
            'ip_version' => $details['ip_version'],
            'service' => $details['service'],
        ];
        //TODO check if exists
        $this->db->insert('ports', $insert);
    }
    /**
     *
     * @param int $port_id
     * @return void
     */
    public function deletePortById(int $port_id): void
    {
        $this->db->delete('ports', ['id' => $port_id]);
    }

    /**
     *
     * @param int $host_id
     * @return string
     */
    public function getDisplayNameById(int $host_id): string
    {
        return $this->getDisplayName($this->hosts[$host_id]);
    }

    /**
     *
     * @param array<string, mixed> $host
     *
     * @return string
     */
    private function getDisplayName(array $host): string
    {
        if (!empty($host['title'])) {
            return $host['title'];
        } elseif (!empty($host['hostname'])) {
            return ucfirst(explode('.', $host['hostname'])[0]);
        }

        return $host['ip'];
    }

    /**
     * Load and set the hosts db
     *
     * @return bool
     */
    private function setHostsDb(): bool
    {
        $ncfg = $this->ctx->get('Config');
        $networks = $this->ctx->get('Networks');
        $query_hosts = 'SELECT * FROM hosts';
        $results = $this->db->query($query_hosts);
        if (!$results) :
            return false;
        endif;
        $hosts = $this->db->fetchAll($results);
        $this->totals = count($hosts);

        foreach ($hosts as $host) {
            $id = (int) $host['id'];
            $net_id = $host['network'];
            $network = $networks->getNetworkByID($net_id);
            if ($network !== false) {
                $host['net_cidr'] = $network['network'];
                $host['network_name'] = $network['name'];
                $host['network_vlan'] = $network['vlan'];
            } else {
                Log::warning('Host network seems not exists: ' . "[H: $id][N: $net_id]");
            }
            $host['display_name'] = ucfirst($this->getDisplayName($host));

            $this->hosts[$id] = $host;
            $host['online'] == 1 ? ++$this->total_on : ++$this->total_off;
            $host['highlight'] ? $this->highlight_total++ : null;
            $this->hosts[$id]['disable'] = empty($host['disable']) ? 0 : 1;

            //Track host categories
            if (empty($this->host_cat_track[$host['category']])) :
                $this->host_cat_track[$host['category']] = 1;
            else :
                $this->host_cat_track[$host['category']]++;
            endif;
            /* Port Field JSON TODO remove  */
            if (!empty($this->hosts[$id]['ports'])) :
                $this->hosts[$id]['ports'] = json_decode($host['ports'], true);
                //upgrade remove later
                foreach ($this->hosts[$id]['ports'] as $port) :
                    $set = [
                        "hid" => $id,
                        "pnumber" => $port['n'],
                        "scan_type" => 1,
                        "protocol" => $port['port_type'],
                        "online" => $port['online'],
                        "last_change" => date_now()
                    ];
                    $this->db->insert('ports', $set);
                    $this->db->update('hosts', ['ports' => '{}'], ['id' => $id]);
                endforeach;
                $this->hosts[$id]['ports'] = '';
            endif;
            /* END REMOVE */

            /* Misc field  fields that we keep in JSON format */
            if (!isEmpty($this->hosts[$id]['misc'])) :
                $misc_values = json_decode($this->hosts[$id]['misc'], true);
                foreach ($misc_values as $key => $value) :
                    if (in_array($key, $this->misc_keys, true)) : //Prevent unused/old keys
                        if (in_array($key, ['agent_version'], true)) : //Prevent Version numbers to float
                            $host[$key] = $this->hosts[$id][$key] = (string) $value;
                        elseif (is_float($value)) :
                            $host[$key] = $this->hosts[$id][$key] = (float) $value;
                        elseif (is_numeric($value)) :
                            $host[$key] = $this->hosts[$id][$key] = (int) $value;
                        elseif (is_bool($value)) :
                            $host[$key] = $this->hosts[$id][$key] = (bool) $value;
                        else :
                            $host[$key] = $this->hosts[$id][$key] = $value;
                        endif;
                    endif;
                endforeach;
            endif;
            /* Notes */
            if (empty($host['notes_id'])) :
                $this->db->insert('notes', ['host_id' => $host['id']]);
                $insert_id = $this->db->insertID();
                $this->db->update('hosts', ['notes_id' => $insert_id], ['id' => $host['id']]);
                $this->hosts[$host['id']]['notes_id'] = $insert_id;
            endif;

            /*
             * MISC KEYS EXTRA TASKS
             */
            /* General */
            if (!empty($host['system_type'])) :
                if ((int) $host['system_type'] === 17) :
                    $this->hypervisor_rols++;
                endif;
            endif;

            /* ALARMS */
            if (empty($host['disable_alarms'])) :
                if (!empty($host['alert'])) :
                    $this->alerts++;
                endif;

                if (!empty($host['warn'])) :
                    $this->warns++;
                endif;
            endif;

            /* Ansible */
            if ($ncfg->get('ansible')) {
                if ($host['ansible_enabled']) {
                    $this->ansible_hosts++;
                    if (!$host['online']) :
                        $this->ansible_hosts_off++;
                    endif;
                }
                if ($host['ansible_fail']) :
                    $this->ansible_hosts_fail++;
                endif;
            }

            /* Agent */
            if (!empty($this->hosts[$id]['agent_installed'])) :
                $this->agents++;
                if (!$host['online'] || empty($this->hosts[$id]['agent_online'])) :
                    $this->agents_off++;
                endif;

                if (
                    !empty($this->hosts[$id]['agent_next_report']) &&
                    $this->hosts[$id]['agent_next_report'] < time()
                ) :
                    $this->agents_missing_pings++;
                    $this->hosts[$id]['agent_missing_pings'] = 1;
                    //With pings disabled, agent pings missing change state to off
                    if (!empty($this->hosts[$id]['disable_pings'])) :
                        $this->update($id, ['online' => 0]);
                    endif;
                endif;
            endif;

            if (isset($this->hosts[$id]['misc'])) :
                unset($this->hosts[$id]['misc']);
            endif;
        } // LOOP FIN

        return true;
    }
}
