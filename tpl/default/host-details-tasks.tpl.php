<?php
/**
 * Host Details Template
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
?>
<div id="tab15" class="host-details-tab-content">
    <div id="tasks_status_msg" class="center">&nbsp</div>
    <div class="create_task">
        <fieldset>
            <legend>Create Task</legend>
            <table>
                <!-- Fila de labels -->
                <tr>
                    <td><label for="task_name"><?= $lng['L_NAME'] ?></label></td>
                    <td><label for="task_trigger"><?= $lng['L_TASK_TRIGGER'] ?></label></td>
                    <td><label for="conditional_field"><?= $lng['L_CONDITIONAL'] ?></label></td>
                    <td><label for="playbook">Playbook</label></td>
                    <td><label for="ansible_groups"><?= $lng['L_GROUPS']?></label></td>
                    <td><label for="disable_task"><?= $lng['L_DISABLE']?></label></td>
                    <td><label for="next_task"><?= $lng['L_NEXT_TASK']?></label></td>
                    <td></td>
                </tr>
                <!-- Fila de inputs -->
                <tr data-id="0">
                    <td>
                        <input type="hidden" name="hid" value="<?= $tdata['host_details']['id']?>"/>
                        <input type="text" size="12" max-size="12" id="task_name" name="task_name" required>
                    </td>
                    <td>
                        <select id="task_trigger" name="task_trigger" required>
                            <option value="" disabled selected>Select Trigger</option>
                            <?php
                            foreach ($ncfg->get('task_trigger') as $task) :
                                print("<option value={$task['id']}>{$lng[$task['name']]}</option>");
                            endforeach;
                            ?>
                        </select>
                    </td>
                    <td id="conditional_field"></td>
                    <td>
                        <select id="playbooks" name="playbooks">
                        </select>
                    </td>
                    <td> <!-- Groups -->
                        <select id="ansible_groups" disabled name="ansible_groups">
                            <option value="0" selected disabled><?= $lng['L_THIS_SYSTEM'] ?></option>
                        </select>
                    </td>
                    <td><input type="checkbox" id="disable_task" name="disable_task"></td>
                    <td>
                        <select id="next_task" name="next_task" disabled>
                            <option value="0" selected>No Next Task</option>
                        </select>
                    </td>
                    <td>
                        <button type="submit" data-action="create_host_task">Create</button>
                    </td>
                </tr>
            </table>
            <input
                type="hidden"
                id="event_data"
                data-input-events="<?= htmlspecialchars(json_encode(EventType::getConstants()))?>"
            />
        </fieldset>
    </div>
    <div id="tasks-list" class="task-list"></div>
</div>
