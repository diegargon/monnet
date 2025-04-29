<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Models;

class NetworksModel
{
    private \DBManager $db;

    public function __construct(\DBManager $db) {
        $this->db = $db;
    }

   public function getAllNetworks(): array
    {
        $query = $this->db->selectAll('networks');
        return $this->db->fetchAll($query);
    }

    public function addNetwork(array $data): int
    {
        $this->db->insert('networks', $data);
        return $this->db->insertID();
    }

    public function updateNetwork(int $id, array $data): void
    {
        $this->db->upsert('networks', $data, ['id' => $id]);
    }

    public function deleteNetwork(int $id): void
    {
        $this->db->query("DELETE FROM networks WHERE id=$id");
    }

    /**
     * Obtiene una red específica por su ID
     *
     * @param int $id ID de la red
     * @return array<string, mixed>|false Array con los datos de la red o false si no se encuentra
     */
    public function getNetworkByID(int $id): array|false
    {
        $query = $this->db->select('networks', ['id' => $id]);
        $result = $this->db->fetch($query);

        return $result ?: false;
    }

    /**
     * Verifica si existe una red con el CIDR especificado
     */
    public function networkExistsByCIDR(string $cidr): bool
    {
        $query = $this->db->select('networks', ['network' => $cidr]);
        return (bool) $this->db->fetch($query);
    }
}

