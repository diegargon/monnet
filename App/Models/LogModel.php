<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

class LogModel
{
    private \DBManager $db;

    public function __construct(\DBManager $db)
    {
        $this->db = $db;
    }

    public function addSystemLog(array $data): bool
    {
        return $this->db->insert('system_logs', $data);
    }

    public function addHostLog(array $data): bool
    {
        return $this->db->insert('hosts_logs', $data);
    }

    /**
     * Get system logs filtered by level with a maximum number of results
     *
     * @param int $level Maximum log level to retrieve
     * @param int $max Maximum number of logs to return
     * @return array<int, array<string, mixed>> Array of log entries
     */
    public function getSystemDBLogs(int $level, int $max): array
    {
        return $this->db->select(
            'system_logs',
            ['*'],
            'level <= :level',
            ['level' => $level],
            $max
        );
    }

    /**
     * Return host logs based on provided options
     *
     * @param array<string, mixed> $opts Filtering options:
     *   - level: Maximum log level
     *   - ack: Whether to include acknowledged logs
     *   - host_id: Filter by specific host ID
     *   - log_type: Array of log types to include
     *   - limit: Maximum number of results
     * @return array<int, array<string, mixed>> Array of log entries
     */
    public function getLogsHosts(array $opts = []): array
    {
        $conditions = [];
        $params = [];

        if (!empty($opts['level'])) {
            $conditions[] = 'level <= :level';
            $params['level'] = (int)$opts['level'];
        }

        if (!empty($opts['ack'])) {
            $conditions[] = 'ack >= 0';
        } else {
            $conditions[] = 'ack != 1';
        }

        if (isset($opts['host_id'])) {
            $conditions[] = 'host_id = :host_id';
            $params['host_id'] = (int)$opts['host_id'];
        }

        if (isset($opts['log_type'])) {
            $logConditions = [];
            foreach ($opts['log_type'] as $index => $l_type) {
                $key = "log_type_$index";
                $logConditions[] = "log_type = :$key";
                $params[$key] = (int)$l_type;
            }
            $conditions[] = '(' . implode(' OR ', $logConditions) . ')';
        }

        return $this->db->select(
            'hosts_logs',
            ['*'],
            implode(' AND ', $conditions),
            $params,
            $opts['limit'] ?? null
        );
    }
}