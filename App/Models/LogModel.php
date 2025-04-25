<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Models;

class LogModel
{
    private \DBManager $db;

    public function __construct(\DBManager $db)
    {
        $this->db = $db;
    }

    public function addSystemLog(array $data)
    {
        $this->db->insert('system_logs', $data);
    }
    public function addHostLog(array $data)
    {
        $this->db->insert('system_logs', $data);
    }

    /**
     *
     * @param int $level
     * @param int $max
     *
     * @return array<int, array<string, string>>
     */
    public function getSystemDBLogs(int $level, int $max): array
    {
        $lines = [];

        $query = 'SELECT * FROM system_logs WHERE level <= ' . $level
             . ' ORDER BY date DESC LIMIT ' . $max;
        $result = $this->db->query($query);
        $lines = $this->db->fetchAll($result);

        return $lines;
    }


    /**
     * Return logs based on [$opt]ions
     * @param array<string,mixed> $opts
     * @return array<string,mixed>
     */
    public function getLogsHosts(array $opts = []): array
    {
        $lines = [];
        $conditions = [];

        $query = 'SELECT * FROM hosts_logs';

        if (!empty($opts['level'])) :
            $conditions[] = 'level <= ' . (int)$opts['level'];
        endif;

        /* if ack is set show all if not hidde ack */
        if (!empty($opts['ack'])) :
            $conditions[] = ' ack >= 0';
        else :
            $conditions[] = ' ack != 1';
        endif;

        if (isset($opts['host_id'])) :
            $conditions[] = 'host_id = ' . (int)$opts['host_id'];
        endif;

        if (isset($opts['log_type'])) {
            $logConditions = [];
            foreach ($opts['log_type'] as $l_types) {
                $logConditions[] = 'log_type=' . (int)$l_types;
            }
            $conditions[] = '(' . implode(' OR ', $logConditions) . ')';
        }

        $query .= ' WHERE ' . implode(' AND ', $conditions);
        $query .= ' ORDER BY date DESC';

        if (!empty($opts['limit'])) :
            $query .= ' LIMIT ' . (int)$opts['limit'];
        endif;
        $result = $this->db->query($query);
        $lines = $this->db->fetchAll($result);

        return $lines;
    }
}
