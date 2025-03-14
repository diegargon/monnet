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
  `pb_id`           playbook a ejecutar
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
     * @return bool
     */
    public function deleteTask(int $tid): bool
    {
        return $this->db->delete('tasks', 'id = :id', ['id' => $tid]);
    }

    /**
     *
     * @param int $vtype
     * @param string $vkey
     * @param string $vvalue
     * @return bool
     */
    public function add_ansible_var(int $vtype, string $vkey, string $vvalue): bool
    {
        if ($this->check_ansible_var_exists($vkey)) {
            return false;
        }
        $var_data = [
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
    public function del_ansible_var(int $id): bool
    {
        return $this->db->delete('ansible_vars', 'id = :id', ['id' => $id]);
    }

    /**
     *
     * @param int $key
     * @return bool
     */
    private function check_ansible_var_exists(int $key): bool
    {
        $query = "SELECT COUNT(*) FROM ansible_vars WHERE ckey = :var_name LIMIT 1";
        $params = ['var_name' => $key];

        return $this->db->qfetch($query, $params) > 0;
    }
}
