<?php

/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Models;

use App\Core\AppContext;
use App\Core\DBManager;

class CmdAnsibleReportModel
{
    /** @var AppContext */
    private AppContext $ctx;

    /** @var DBManager */
    private DBManager $db;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->db = new DBManager($ctx);
    }

    /**
     * Get Report by ID
     *
     * report_type: 1 manual 2 task
     *
     * @param int $rid report id
     * @return array<int, mixed>
     */
    public function getDbReportById(int $rid): array
    {
        $query = 'SELECT * FROM reports WHERE id = :id';
        $params = [ 'id' => $rid ];

        return $this->db->qfetch($query, $params);
    }

    /**
     * Get Report by host id
     *
     * $opt['rtype'] (report_type): 1 manual 2 task
     * $opt['head'] No report content
     * $opt['source_id'] task_id or user_id
     * $opt['host_id']
     * $opt['order'] ASC/DESC
     *
     * @param array<string, string|int> $opts
     * @return array<int, array<int|string>>
     */
    public function getDbReports(array $opts): array
    {
        $query = 'SELECT';
        $params = [];

        $query .= !empty($opts['head']) ? ' id, host_id, pid, source_id, date, ack, status ' : ' * ';
        $query .= ' FROM reports';

        $conditions = [];

        if (!empty($opts['host_id'])) {
            $conditions[] = 'host_id = :host_id';
            $params['host_id'] = $opts['host_id'];
        }
        if (!empty($opts['source_id'])) {
            $conditions[] = 'source_id = :source_id';
            $params['source_id'] = $opts['source_id'];
        }
        if (!empty($opts['rtype'])) {
            $conditions[] = 'rtype = :rtype';
            $params['rtype'] = $opts['rtype'];
        }

        if (!empty($conditions)) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        // Validar ORDER BY
        if (!empty($opts['order']) && in_array($opts['order'], ['ASC', 'DESC'], true)) {
            $query .= ' ORDER BY date ' . $opts['order'];
        }

        return $this->db->qfetchAll($query, $params);
    }

    /**
     *
     * @param int $rid
     * @return bool
     */
    public function delete(int $rid): bool
    {
        return $this->db->delete('reports', 'id = :id', ['id' => $rid]);
    }

    /**
     *
     * @param int $rid
     * @param int $value
     * @return bool
     */
    public function setAck(int $rid, int $value): bool
    {
        $set = ['ack' => $value];
        return $this->db->update('reports', $set, 'id = :id', ['id' => $rid]);
    }

    /**
     *
     * @param array<string, mixed> $pb_data
     * @return bool
     */
    public function insertReport(array $pb_data): bool
    {
        $result = $this->db->insert('reports', $pb_data);

        return $result;
    }
}
