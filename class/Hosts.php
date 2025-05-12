<?php

/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
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
        'system_rol',
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
        'alarm_hostname_disable',
        'alarm_ping_email',
        'alarm_port_email',
        'alarm_macchange_email',
        'alarm_newport_email',
        'email_list',
        'agent_next_report', /* Timesstamp for next report */
        'agent_last_contact',
        'agent_version',
        'agent_missing_pings',
        'load_avg',
        'mem_info',
        'disks_info',
        'ncpu',
        'uptime',
        'iowait',
        'always_on',
        'latency'
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

        /* Temporaly fix misc in two places TODO remove later */
        foreach ($values as $kvalue => $vvalue) {
            if ($kvalue == 'misc' && is_array($vvalue)) {
                $misc_container = $vvalue;
                unset($values[$kvalue]);
                break;
            }

            //TODO temporaly move system_type a system_rol
            if ($kvalue === 'system_type' && empty($values['system_rol'])) {
                $values['system_rol'] = $vvalue;
                unset($values['system_type']);
            }
        }

        foreach ($values as $kvalue => $vvalue) {
            if (!empty($kvalue) && isset($vvalue)) {
                //Log change
                if (
                        empty($this->hosts[$id]['misc']['disable_alarms']) &&
                        ($kvalue === 'mac' || $kvalue === 'mac_vendor' || $kvalue === 'hostname') &&
                        ($this->hosts[$id][$kvalue] != $vvalue)
                ) {
                    $alert = 0;
                    $warn = 0;

                    $log_msg = '';
                    if ($kvalue === 'mac') :
                        $log_msg = 'Mac ' . $this->lng['L_HAS_CHANGED'] . " to $vvalue" ;
                        $alert = 1;
                    elseif ($kvalue === 'mac_vendor') :
                        $log_msg = 'Mac vendor ' . $this->lng['L_HAS_CHANGED'] . " to $vvalue";
                    elseif ($kvalue === 'hostname') :
                        $log_msg = 'Hostname ' .
                            $this->lng['L_HAS_CHANGED'] .
                            " from {$this->hosts[$id][$kvalue]} to $vvalue";
                        $warn = 1;
                    endif;

                    if ($alert) {
                        $this->setAlertOn($id, $log_msg, LogType::EVENT_ALERT, EventType::HOST_INFO_CHANGE);
                    } elseif ($warn) {
                        $this->setWarnOn($id, $log_msg, LogType::EVENT_WARN, EventType::HOST_INFO_CHANGE);
                    } else {
                        Log::logHost(LogLevel::NOTICE, $id, $log_msg);
                    }

                    if (
                            !empty($this->hosts[$id]['misc']['email_alarms']) &&
                            !empty($this->hosts[$id]['misc']['alarm_macchange_email'])
                    ) {
                        $this->sendHostMail($id, $log_msg, $log_msg);
                    }
                }
                //Log category change
                if ($kvalue == 'category' && $vvalue != $this->hosts[$id]['category']) {
                    Log::logHost(LogLevel::NOTICE, $id, 'Host ' . $this->hosts[$id]['display_name']
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
                    $fvalues[$kvalue] = $vvalue;
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
        /*
         * remove/move keys and that we store in misc json field
         * to misc container
         */

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

        $host['id'] = $host_id;
        $host['display_name'] = $this->getDisplayName($host);
        $this->hosts[$host_id] = $host;
        $network_name = $this->ctx->get('Networks')->getNetworkNameByID($host_id);
        $log_msg = 'Found new host: ' . $host['display_name'] . ' on network ' . $network_name;
        Log::logHost(LogLevel::WARNING, $host_id, $log_msg, LogType::EVENT_WARN, EventType::NEW_HOST_DISCOVERY);
    }

    /**
     *
     * @param int $hid
     * @return void
     */
    public function remove(int $hid): void
    {
        //TODO: Claves foraneas ON CASCADE
        Log::notice('Deleted host: ' . $this->hosts[$hid]['display_name']);
        $this->db->delete('hosts', ['id' => $hid], 'LIMIT 1');
        $this->db->delete('notes', ['host_id' => $hid], 'LIMIT 1');
        $this->db->delete('stats', ['host_id' => $hid]);
        $this->db->delete('hosts_logs', ['host_id' => $hid]);
        $this->db->delete('reports', ['host_id' => $hid]);
        $this->db->delete('ansible_msg', ['host_id' => $hid]);
        $this->db->delete('ports', ['hid' => $hid]);
        $this->db->delete('ansible_vars', ['hid' => $hid]);
        $this->db->delete('tasks', ['hid' => $hid]);

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
     * Check if category is not empty
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
        Log::logHost(LogLevel::NOTICE, $id, $this->lng['L_CLEAR_ALARMS_BITS_BY'] . ': ' . $username);

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
        Log::logHost(LogLevel::NOTICE, $id, $this->lng['L_ALARMS_DISABLE']);
        return true;
    }

    /**
     *
     * @param int $id
     * @param string $msg
     * @param int $log_type
     * @return void
     */
    public function setAlertOn(int $id, string $msg, int $log_type, int $event_type): void
    {
        Log::logHost(LogLevel::ALERT, $id, $msg, $log_type, $event_type);
        $this->update($id, ['alert' => 1]);
    }

    /**
     *
     * @param int $id
     * @param string $msg
     * @param int $log_type
     * @return void
     */
    public function setWarnOn(int $id, string $msg, int $log_type, int $event_type): void
    {
        Log::logHost(LogLevel::WARNING, $id, $msg, $log_type, $event_type);
        $this->update($id, ['warn' => 1]);
    }

    public function clearAlerts(): bool
    {
        foreach ($this->hosts as $host) :
            $id = $host['id'];
            if (!empty($host['alert'])) {
                $this->db->update('hosts', ['alert' => 0], ['id' => $id]);
                $this->hosts[$id]['alert'] = 0;
            }
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
            if (!empty($host['warn'])) {
                $this->db->update('hosts', ['warn' => 0], ['id' => $id]);
                $this->hosts[$id]['warn'] = 0;
            }
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
        Log::logHost(LogLevel::WARNING, $id, $log_msg, 3);
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
            !empty($this->hosts[$id]['misc']['email_alarms']) &&
            !empty($this->hosts[$id]['misc']['email_list'])
        ) {
            $mails = explode(",", $this->hosts[$id]['misc']['email_list']);
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
            if ($status === 2 && !empty($host['misc']['agent_missing_pings'])) :
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

            if ($host['alert'] && empty($host['misc']['disable_alarms'])) :
                $log_type = [ LogType::EVENT_ALERT ];

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

                    $timezone = $this->ncfg->get('default_timezone');
                    $timeformat = $this->ncfg->get('datetime_format_min');
                    foreach ($alert_logs_items as $item) :
                        $date = utc_to_tz($item['date'], $timezone, $timeformat);
                        $min_host['log_msgs'][] = [
                            'log_id' => $item['id'],
                            'log_type' => LogType::getName($item['log_type']),
                            'event_type' => EventType::getName($item['event_type']),
                            'msg' => "{$item['msg']} - $date",
                            'ack_state' => $item['ack']
                        ];
                    endforeach;
                    $result_hosts[] = $min_host;
                else :
                    $min_host['alert_msg']  = 'Alert logs are empty';
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

        foreach ($this->hosts as $host) :
            $min_host = [
                'id' => $host['id'],
                'display_name' => $host['display_name'],
                'mac' => $host['mac'],
                'ip' => $host['ip'],
                'online' => $host['online'],
            ];

            if ($host['warn'] && empty($host['misc']['disable_alarms'])) :
                $log_type = [ LogType::EVENT_WARN ];

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
                    $timezone = $this->ncfg->get('default_timezone');
                    $timeformat = $this->ncfg->get('datetime_format_min');
                    foreach ($warn_logs_items as $item) :
                        $date = utc_to_tz($item['date'], $timezone, $timeformat);
                        $min_host['log_msgs'][] = [
                            'log_id' => $item['id'],
                            'log_type' => LogType::getName($item['log_type']),
                            'event_type' => EventType::getName($item['event_type']),
                            'msg' => "{$item['msg']} - $date",
                            'ack_state' => $item['ack']
                        ];
                    endforeach;
                    $result_hosts[] = $min_host;
                else :
                    $min_host['warn_msg']  = 'Warn logs are empty';
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

        if (is_bool($result)) {
            return [];
        }
        return $this->db->fetchAll($result);
    }

    /**
     *
     * @param int $hid
     * @param int $scan_type
     * @return array<string,string|int>
     */
    public function getHostScanPorts(int $hid, int $scan_type = 0): array
    {

        $result = $this->db->selectAll(
            'ports',
            [
                'hid' => $hid,
                'scan_type' => $scan_type
            ]
        );
        if (is_bool($result)) {
            return [];
        }
        return $this->db->fetchAll($result);
    }

    /**
     *
     * @param int $port_id
     * @param array<string,string|int> $set
     * @return void
     */
    public function updatePort(int $port_id, array $set): void
    {
        $this->db->update('ports', $set, ['id' => $port_id]);
    }

    /**
     *
     * @param array<string,string|int> $insert_data
     * @return void
     */
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
        empty($details['ip_version']) ? $details['ip_version'] = 'unknown' : null;
        empty($details['service']) ? $details['service'] = 'unknown' : null;
        $insert = [
            'hid' => $hid,
            'scan_type' => 1,
            'protocol' => $details['protocol'],
            'pnumber' => $details['pnumber'],
            'last_check' => date_now(),
            'ip_version' => $details['ip_version'],
            'service' => $details['service'],
        ];
        //TODO check if exists
        $this->db->insert('ports', $insert);
    }

    /**
     *
     * @param int $host_id
     * @return string
     */
    public function getDisplayNameById(int $host_id): string
    {
        if (isset($this->hosts[$host_id])) {
            return $this->getDisplayName($this->hosts[$host_id]);
        }
        return $host_id;
    }

    /**
     *
     * @param int $id
     * @return array<int,string|int>
     */
    public function getReportById(int $id)
    {
        $query = 'SELECT * FROM reports WHERE id=' . $id . ' LIMIT 1';
        $results = $this->db->query($query);
        if (is_bool($results)) {
            return [];
        }
        $row = $this->db->fetch($results);

        return $row;
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
        if (is_bool($results)) :
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
            if ($network['disable']) {
                continue;
            }
            $host['display_name'] = ucfirst($this->getDisplayName($host));

            $this->hosts[$id] = $host;
            $host['online'] == 1 ? ++$this->total_on : ++$this->total_off;
            $host['highlight'] ? $this->highlight_total++ : null;

            //Track host categories
            if (empty($this->host_cat_track[$host['category']])) :
                $this->host_cat_track[$host['category']] = 1;
            else :
                $this->host_cat_track[$host['category']]++;
            endif;

            /* Misc field  fields that we keep in JSON format */
            if (!isEmpty($this->hosts[$id]['misc'])) :
                $misc_values = json_decode($this->hosts[$id]['misc'], true);
                $this->hosts[$id]['misc'] = $misc_values;
                foreach ($misc_values as $key => $value) :
                    if (in_array($key, $this->misc_keys, true)) : //Prevent unused/old keys
                        if (in_array($key, ['agent_version'], true)) : //Prevent Version numbers to float
                            $host[$key] = $this->hosts[$id][$key] = (string) $value;
                        elseif (is_numeric($value)) :
                            //String float is numeric but not float is_float not work
                            if (strpos((string) $value, '.') !== false) {
                                $host[$key] = $this->hosts[$id][$key] = (float) $value;
                            } else {
                                $host[$key] = $this->hosts[$id][$key] = (int) $value;
                            }
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
            if (!empty($host['misc']['system_rol'])) {
                if ((int) $host['misc']['system_rol'] === 17) :
                    $this->hypervisor_rols++;
                endif;
            }
            //TODO to migrate to system rol delete in the future
            if (
                empty($host['misc']['system_rol']) &&
                !empty($host['misc']['system_type'])
            ) {
                $host['misc']['system_rol'] = $host['misc']['system_type'];
            }
            /* ALARMS */
            if (empty($host['misc']['disable_alarms'])) :
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
                    !empty($this->hosts[$id]['misc']['agent_next_report']) &&
                    $this->hosts[$id]['misc']['agent_next_report'] < (time() - 5) # minus grace period
                ) :
                    $this->agents_missing_pings++;
                    if (empty($this->hosts[$id]['misc']['agent_missing_pings'])) {
                        $this->update($id, ['agent_missing_pings' => 1]);
                    }
                    $this->hosts[$id]['misc']['agent_missing_pings'] = 1;

                    //With pings disabled, if agent  missing a ping change state to off
                    if (!empty($this->hosts[$id]['misc']['disable_pings'])) :
                        $this->update($id, ['online' => 0]);
                    elseif (
                        ((int) $this->hosts[$id]['misc']['agent_next_report'] +
                        $this->ncfg->get('agent_default_interval'))  <
                        time()
                    ) :
                        //Two pings missed
                        $this->update($id, ['online' => 0]);
                    endif;
                else :
                    if (!empty($this->hosts[$id]['misc']['agent_missing_pings'])) {
                        $this->update($id, ['agent_missing_pings' => 0]);
                    }
                endif;
            endif;

         } // LOOP END

        return true;
    }
}
