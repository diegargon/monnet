<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Models;

class CmdHostModel
{
    private \AppContext $ctx;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
    }
    /**
     * Elimina un host.
     *
     * @param int $target_id El ID del host.
     * @return bool True si se eliminó correctamente, False en caso contrario.
     */
    public function remove($target_id)
    {
        $db = $this-ctx->get('Mysql');
        return $db->delete('hosts', ['id' => $target_id]);
    }

    /**
     * Actualiza un host.
     *
     * @param int $target_id El ID del host.
     * @param array $data Los datos a actualizar.
     * @return bool True si se actualizó correctamente, False en caso contrario.
     */
    public function update($target_id, $data)
    {
        $db = $this-ctx->get('Mysql');
        return $db->update('hosts', $data, ['id' => $target_id]);
    }

    /**
     * Crea un token para un host.
     *
     * @param int $target_id El ID del host.
     * @return bool True si se creó correctamente, False en caso contrario.
     */
    public function createHostToken($target_id)
    {
        $db = $this-ctx->get('Mysql');
        $token = bin2hex(random_bytes(16));
        return $db->update('hosts', ['token' => $token], ['id' => $target_id]);
    }

    /**
     * Agrega un puerto remoto a un host.
     *
     * @param int $target_id El ID del host.
     * @param array $port_details Los detalles del puerto.
     * @return bool True si se agregó correctamente, False en caso contrario.
     */
    public function addRemoteScanHostPort($target_id, $port_details)
    {
        $db = $this-ctx->get('Mysql');
        $port_details['host_id'] = $target_id;
        return $db->insert('ports', $port_details);
    }

    /**
     * Elimina un puerto de un host.
     *
     * @param int $target_id El ID del puerto.
     * @return bool True si se eliminó correctamente, False en caso contrario.
     */
    public function deletePort($target_id)
    {
        $db = $this-ctx->get('Mysql');
        return $db->delete('ports', ['id' => $target_id]);
    }

    /**
     * Actualiza un puerto de un host.
     *
     * @param int $target_id El ID del puerto.
     * @param array $data Los datos a actualizar.
     * @return bool True si se actualizó correctamente, False en caso contrario.
     */
    public function updatePort($target_id, $data)
    {
        $db = $this-ctx->get('Mysql');
        return $db->update('ports', $data, ['id' => $target_id]);
    }
}
