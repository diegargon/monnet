<?php

/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

/*
    TABLA TASKS

    `id`                int(11)         PRIMARY KEY AUTO_INCREMENT
    `hid`               int(11)         NOT NULL
    `trigger_type`      smallint(6)     NOT NULL
    `last_triggered`    datetime        NULL
    `next_trigger`      datetime        NULL
    `task_name`         varchar(100)    NOT NULL
    `next_task`         int(11)         NULL DEFAULT 0
    `disable`           tinyint(1)      NULL DEFAULT 0
    `task_interval`     varchar(10)     NULL
    `interval_seconds`  int(11)         NULL
    `created`           datetime        NULL DEFAULT current_timestamp()
    `event_id`          int(11)         NULL DEFAULT 0
    `crontime`          varchar(255)    NULL
    `groups`            varchar(255)    NULL
    `pid`               varchar(255)    NOT NULL DEFAULT 'std-ansible-ping'
    `done`              int(11)         NULL DEFAULT 0
    `status`            tinyint(4)      NOT NULL DEFAULT 0

    Trigger Manual: Encolo el comando, se ejecuta via mgateway, deberia enviar un mensaje
        para avisar de que hay algo en la queue.
*/

namespace App\Models;

use App\Core\AppContext;
use App\Core\DBManager;

class CmdAnsibleModel
{
    private AppContext $ctx;
    private DBManager $db;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->db = new DBManager($ctx);
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
