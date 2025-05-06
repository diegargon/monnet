<?php

/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Models;

class HostsModel
{
    private \DBManager $db;

    public function __construct(\DBManager $db)
    {
        $this->db = $db;
    }

    /**
     *
     * @param array<string, int|string> $host_data
     * @return bool
     */
    public function add(array $host_data): bool
    {
        return $this->db->insert('hosts', $host_data);
    }
    /**
     *
     * @return array<string, mixed>
     */
    public function getAll(): array
    {
        $query = "SELECT * FROM hosts";
        $results = $this->db->qfetchAll($query);

        if (is_bool($results)) {
            return [];
        }

        return $results;
    }

    /**
     * Get filtered hosts
     *
     * @param array $filters
     * @return array Filtered results
     */
    public function getFiltered(array $filters): array
    {
        $query = "SELECT * FROM hosts WHERE 1=1";
        $params = [];

        if (!empty($filters['only_highlight'])) {
            $query .= " AND highlight = :highlight";
            $params['highlight'] = 1;
        }

        if (!empty($filters['not_highlight'])) {
            $query .= " AND highlight = :highlight";
            $params['highlight'] = 0;
        }

        // Filter by network IDs if provided

        if (
                isset($filters['networks']) &&
                is_array($filters['networks']) &&
                count($filters['networks']) > 0
        ) {
            $placeholders = [];
            foreach ($filters['networks'] as $index => $networkId) {
                $key = "net_$index";
                $placeholders[] = ":$key";
                $params[$key] = (int)$networkId;
            }

            $query .= " AND network IN (" . implode(',', $placeholders) . ")";
        }


        // Filter by category IDs

        if (
            isset($filters['cats']) &&
            is_array($filters['cats']) &&
            count($filters['cats']) > 0
        ) {
            $placeholders = [];
            foreach ($filters['cats'] as $i => $id) {
                $key = "cat_$i";
                $placeholders[] = ":$key";
                $params[$key] = (int)$id;
            }
            $query .= " AND category IN (" . implode(',', $placeholders) . ")";
        }


        $results = $this->db->qfetchAll($query, $params);



        if (!$results) {
            return [];
        }

        return $results;
    }

    /**
     *
     * @return array<string, int>
     */
    public function getTotalsStats(): array
    {
        $stats = [];

        /* OLD
         SUM(agent_installed = 1 and online = 0) AS agent_offline,
        */
        $result = $this->db->query("
            SELECT
                COUNT(*) AS total_hosts,
                SUM(online = 1) AS total_online,
                SUM(alert > 0) AS alerts,
                SUM(warn > 0) AS warns,
                SUM(agent_installed = 1) as agent_installed,
                SUM(agent_installed and agent_online = 0) as agent_offline,
                SUM(agent_installed and agent_online = 1) as agent_online,
                SUM(ansible_enabled = 1) as ansible_hosts,
                SUM(ansible_fail = 1) as ansible_hosts_fail,
                SUM(ansible_enabled = 1 AND online = 1) AS ansible_online
            FROM hosts
        ");
        if ($result) {
            $stats = $this->db->fetch($result) ?: [];
        }
        return $stats;
    }
    /**
     * Actualiza los datos de un host.
     *
     * @param int   $id   ID del host a actualizar.
     * @param array $data Datos a actualizar.
     *
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        return $this->db->update('hosts', $data, 'id = :id', ['id' => $id]);
    }
}
