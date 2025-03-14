<?php

?>
<fieldset>
    <legend>Tasks</legend>
    <table id="tasksTable">
        <!-- Fila de labels -->
        <tr>
            <td><label for="task_name">Name</label></td>
            <td><label for="task_trigger">Task Trigger</label></td>
            <td><label for="conditional_field"></label></td>
            <td><label for="playbook">Playbook</label></td>
            <td><label for="disable_task">Disable</label></td>
            <td><label for="next_task">Next Task</label></td>
            <td></td>
        </tr>
<?php
    foreach ($tdata['host_tasks'] as $task) :
        $task_id = $task['id'];
    ?>
        <!-- Fila de inputs -->
        <tr data-id="<?= $task_id ?>">
            <td>
                <input type="text" size="20" max-size="20" name="task_name[<?= $task_id ?>]" value="<?= $task['task_name'] ?>" required>
            </td>
            <td>
                <select name="task_trigger[<?= $task_id ?>]" required>
                    <option value="" disabled selected>Select Trigger</option>
                    <?php
                    foreach ($cfg['task_trigger'] as $trigger) :
                        $selected = ($trigger['id'] == $task['trigger_type']) ? 'selected' : '';
                        echo "<option value=\"{$trigger['id']}\" $selected>{$lng[$trigger['name']]}</option>";
                    endforeach;
                    ?>
                </select>
            </td>
            <td id="conditional_field"></td>
            <td>
                <select name="playbooks[<?= $task_id ?>]">
                    <option value="" disabled selected>No select</option>
                    <?php
                    foreach ($cfg['playbooks'] as $playbook) :
                        $selected = ($playbook['id'] == $task['pb_id']) ? 'selected' : '';
                        echo "<option value=\"{$playbook['id']}\" $selected>{$playbook['name']}</option>";
                    endforeach;
                    ?>
                </select>
            </td>
            <td>
                <input type="checkbox" name="disable_task[<?= $task_id ?>]" <?= $task['disable'] ? 'checked' : '' ?>>
            </td>
            <td>
                <select name="next_task[<?= $task_id ?>]">
                    <option value="0" selected disabled>No Next Task</option>
                </select>
            </td>
            <td>
                <button type="submit" data-action="delete_task">Borrar</button>
                <button type="submit" data-action="update_task">Modificar</button>
                <button type="submit" data-action="force_exec_task">Forzar</button>
            </td>
        </tr>
    <?php
    endforeach;
    ?>

    </table>
    <input
        type="hidden"
        id="event_data"
        data-input-events="<?= htmlspecialchars(json_encode(EventType::getConstants()))?>"
    />
</fieldset>
