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

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
    }

    /**
     *
     * @param int $hid
     * @return array<int, array<string|int>
     */
    public function getHostsTasks(int $hid): array
    {
        $db = $this->ctx->get('DBManager');
        $query = 'SELECT * FROM tasks WHERE hid = :id';
        $params = [ 'id' => $hid ];

        return $db->qfetchAll($query, $params);
    }
}
