<?php

?>
<fieldset>
    <legend>Tasks</legend>
    <table>
        <!-- Fila de labels -->
        <tr>
            <td><label for="task_name">Name</label></td>
            <td><label for="task_trigger">Task Trigger</label></td>
            <td><label for="conditional_field"></label></td>
            <td><label for="playbook">Playbook</label></td>
            <td><label for="disable_task">Disable</label></td>
            <td><label for="next_task">Next Task</label></td>
            <td></td> <!-- Espacio para el botón -->
        </tr>
        <!-- Fila de inputs -->
        <tr>
            <td>
                <input type="text" size="12" max-size="12" id="task_name" name="task_name" required>
            </td>
            <td>
                <select id="task_trigger" name="task_trigger" required>
                    <option value="" disabled selected>Select Trigger</option>
                    <?php
                    foreach ($cfg['task_trigger'] as $task) :
                        print("<option value={$task['id']}>{$lng[$task['name']]}</option>");
                    endforeach;
                    ?>
                </select>
            </td>
            <td id="conditional_field"></td>
            <td>
                <select id="playbooks" name="playbooks">
                    <option value="" disable selected>No select</option>
                    <?php
                    foreach ($cfg['playbooks'] as $playbook) :
                        print("<option value={$playbook['name']}>{$playbook['name']}</option>");
                    endforeach;
                    ?>
                </select>
            </td>
            <td><input type="checkbox" id="disable_task" name="disable_task"></td>
            <td>
                <select id="next_task" name="next_task">
                  <option value="0" selected>No Next Task</option>
                </select>
            </td>
            <td>
                <button type="submit" name="action" value="create">Create</button>
            </td>
        </tr>
    </table>
    <input
        type="hidden"
        id="event_data"
        data-input-events="<?= htmlspecialchars(json_encode(EventType::getConstants()))?>"
    />
</fieldset>
