<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

/**
  TABLA TASK
  `id`
  `hid`             host id, ver posibilidad de rango en el futuro
  `pid`           playbook a ejecutar
  `trigger_type`    tipo de trigger 1 manual  2 temporizaedo 3 otra task 4 evento
  `last_triggered`  ultima vez que se ejecuto
  `task_name`       nombre comun
  `next_task`       encadena tareas por id
  `disable`         deshabilita la tarea

   Trigger Manual: Encolo el comando, se ejecuta via mgateway, deberia enviar un mensaje
    para avisar de que hay algo en la queue.
 *
    TODO: Field temporizador ¿formato? horas? fecha?
 */

namespace App\Models;

class CmdAnsibleModel
{
    private \AppContext $ctx;
    private \DBManager $db;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->db = $ctx->get('DBManager');
    }

    /**
     *
     * @param int $tid
     * @return array<int, array<string|int>
     */
    public function getTaskById(int $tid): array
    {
        $query = 'SELECT * FROM tasks WHERE id = :tid';
        $params = [ 'tid' => $tid ];

        return $this->db->qfetchAll($query, $params);
    }
    /**
     *
     * @param int $hid
     * @return array<int, array<string|int>
     */
    public function getHostsTasks(int $hid): array
    {
        $query = 'SELECT * FROM tasks WHERE hid = :id';
        $params = [ 'id' => $hid ];

        return $this->db->qfetchAll($query, $params);
    }

    /**
     *
     * @param array<string, string|int> $values
     * @return bool
     */
    public function createTask(array $values): bool
    {
        return $this->db->insert('tasks', $values);
    }

    /**
     *
     * @param int $tid
     * @param array<string, int|string> $values
     * @return bool
     */
    public function updateTask(int $tid, array $values): bool
    {
        return $this->db->update('tasks', $values, 'id = :tid', ['tid' => $tid]);
    }
   /**
     *
     * @param int $tid
     * @return bool
     */
    public function deleteTask(int $tid): bool
    {
        return $this->db->delete('tasks', 'id = :id', ['id' => $tid]);
    }

    /**
     * @param int $hid
     * @param int $vtype
     * @param string $vkey
     * @param string $vvalue
     * @return bool
     */
    public function addAnsibleVar(int $hid, int $vtype, string $vkey, string $vvalue): bool
    {
        if ($this->checkAnsibleVarExists($vkey)) {
            return false;
        }
        $var_data = [
            'hid' => $hid,
            'vtype' => $vtype,
            'vkey' => $vkey,
            'vvalue' => $vvalue
        ];

        return $this->db->insert('ansible_vars', $var_data);
    }

    /**
     *
     * @param int $id
     * @return bool
     */
    public function delAnsibleVar(int $id): bool
    {
        return $this->db->delete('ansible_vars', 'id = :id', ['id' => $id]);
    }

    /**
     *
     * @param int $host_id
     * @return array<int, array<string|int>
     */
    public function getAnsibleVarsByHostId(int $host_id): array
    {
        $query = "SELECT * FROM ansible_vars WHERE hid = :host_id";
        $params = ['host_id' => $host_id];

        return $this->db->qfetchAll($query, $params);
    }
    /**
     *
     * @param string $key
     * @return bool
     */
    private function checkAnsibleVarExists(string $key): bool
    {
        $query = "SELECT COUNT(*) FROM ansible_vars WHERE vkey = :var_name";
        $params = ['var_name' => $key];
        $result = $this->db->qfetch($query, $params);

        return $result && ((int) $result['COUNT(*)'] > 0);
    }
}
