<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Models;

class CmdAnsibleReportModel
{
    private \AppContext $ctx;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
    }

    /**
     * Get Report by ID
     *
     * report_type: 1 manual 2 task
     *
     * @param int $rid report id
     * @return array<int, array<int|string>>
     */
    public function getDbReportById(int $rid): array
    {
        $db = $this->ctx->get('DBManager');
        $query = 'SELECT * FROM reports WHERE id = :id';
        $params = [ 'id' => $rid ];

        return $db->qfetch($query, $params);
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
        $db = $this->ctx->get('DBManager');

        $query = 'SELECT';
        $params = [];

        $query .= !empty($opts['head']) ? ' id, host_id, pb_id, source_id, date ' : ' * ';
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

        return $db->qfetchAll($query, $params);
    }

    public function delete($target_id)
    {
        $db = $this->ctx->get('DBManager');
        return $db->delete('reports', 'id = :id', ['id' => $target_id]);
    }

    public function insertReport(array $pb_data): bool
    {
        $db = $this->ctx->get('DBManager');

        $result = $db->insert('reports', $pb_data);

        return $result;
    }
}
