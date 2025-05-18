<?php

/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Models;

use App\Core\DBManager;

class NetworksModel
{
    private DBManager $db;

    public function __construct(DBManager $db)
    {
        $this->db = $db;
    }

    public function getAllNetworks(): array
    {
        return $this->db->select('networks');
    }

    public function addNetwork(array $data): int
    {
        $this->db->insert('networks', $data);
        return $this->db->lastInsertId();
    }

    public function updateNetwork(int $id, array $data): void
    {
        $this->db->update('networks', $data, 'id = :id', ['id' => $id]);
    }

    public function deleteNetwork(int $id): void
    {
        $this->db->delete('networks', 'id = :id', ['id' => $id]);
    }

    /**
     * Obtiene una red específica por su ID
     *
     * @param int $id ID de la red
     * @return array<string, mixed>|false Array con los datos de la red o false si no se encuentra
     */
    public function getNetworkByID(int $id): array|false
    {
        return $this->db->selectOne('networks', ['*'], 'id = :id', ['id' => $id]);
    }

    /**
     * Verifica si existe una red con el CIDR especificado
     */
    public function networkExistsByCIDR(string $cidr): bool
    {
        return (bool) $this->db->selectOne('networks', ['*'], 'network = :cidr', ['cidr' => $cidr]);
    }
}
