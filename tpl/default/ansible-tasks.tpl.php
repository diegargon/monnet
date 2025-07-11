<?php
/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
/**
 * In frontend->getTpl()
 * @var Config $ncfg
 * @var array<string> $lng Language data
 * @var array<mixed> $tdata Template Data
 */
//TODO avoid this in tpl
use App\Constants\EventType;

?>
<fieldset>
    <legend>Tasks</legend>
    <table id="tasksTable">
        <!-- labels -->
        <tr>
            <td><label for="task_id">Id</label></td>
            <td><label for="task_name"><?= $lng['L_NAME'] ?></label></td>
            <td><label for="task_trigger"><?= $lng['L_TASK_TRIGGER'] ?></label></td>
            <td><label for="conditional_field"><?= $lng['L_CONDITIONAL'] ?></label></td>
            <td><label for="playbook">Playbook</label></td>
            <td><label for="ansible_groups"><?= $lng['L_GROUPS']?></label></td>
            <td><label for="disable_task"><?= $lng['L_DISABLE']?></label></td>
            <td><label for="next_task"><?= $lng['L_NEXT_TASK']?></label></td>
            <td><label for="task_done"><?= $lng['L_DONE']?></label></td>
            <td><label for="task_status"><?= $lng['L_STATUS']?></label></td>
            <td></td>
        </tr>
    <?php
    foreach ($tdata['host_tasks'] as $task) :
        $task_id = $task['id'];
        $task['trigger_type'] == 1 && $task['done'] > 0 ? $disabled = ' disabled' : $disabled = '';
        $status_ico = $task['status'] == 1 ? 'fail-icon' : 'success-icon';
    ?>
        <tr data-id="<?= $task_id ?>">
            <td>
                <span><?= $task_id ?></span>
            </td>
            <td>
                <input type="hidden" name="hid" value="<?= $task['hid'] ?>"/>
                <input
                    type="text" size="12" max-size="20"
                    name="task_name[<?= $task_id ?>]"
                    value="<?= $task['task_name'] ?>"
                    required
                    <?= $disabled ?>
                >
            </td>
            <td>
                <select name="task_trigger[<?= $task_id ?>]" required <?= $disabled ?>>
                    <option value="" disabled selected>Select Trigger</option>
                    <?php foreach ($ncfg->get('task_trigger') as $trigger) :
                        $selected = ($trigger['id'] == $task['trigger_type']) ? 'selected' : ''; ?>
                        <option value="<?= $trigger['id'] ?>" <?= $selected ?>><?= $lng[$trigger['name']] ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td id="conditional_field_<?= $task_id ?>">
                <?php
                # Trigger By a Event (field event_id)
                if ($task['trigger_type'] == 3) :
                    ?>
                    <select id="conditional" <?= $disabled ?>>
                        <?php
                        foreach (EventType::getConstants() as $value => $name) :
                            $value === $task['event_id'] ? $event_selected = 'selected' : $event_selected = null;
                            echo '<option value="' . $name . '" ' . $event_selected . '>' . $value . '</option>';
                        endforeach;
                        ?>
                    </select>
                    <?php
                endif;
                if ($task['trigger_type'] == 4) : # Cron Scheduler
                    echo '<input type="text" size="15" name="conditional" value="' . $task['crontime'] . '"/>';
                endif;
                if ($task['trigger_type'] == 5) : # Interval
                    echo '<input type="text" size="5" name="conditional" value="' . $task['task_interval'] . '"/>';
                endif;
                ?>
            </td>
            <td>
                <select name="playbooks[<?= $task_id ?>]" <?= $disabled ?>>
                    <option value="" disabled selected>No select</option>
                    <?php foreach ($tdata['pb_meta'] as $playbook) :
                        $selected = ($playbook['id'] == $task['pid']) ? 'selected' : ''; ?>
                        <option value="<?= $playbook['id'] ?>" <?= $selected ?>><?= $playbook['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <select name="ansible_groups[<?= $task_id ?>]" disabled>
                    <option value="0" selected disabled><?= $lng['L_THIS_SYSTEM'] ?></option>
                </select>
            </td>
            <td>
                <input
                    type="checkbox"
                    name="disable_task[<?= $task_id ?>]" <?= $task['disable'] ? 'checked' : '' ?>
                    <?= $disabled ?>
                >
            </td>
            <td>
                <select name="next_task[<?= $task_id ?>]" <?= $disabled ?> disabled>
                    <option value="0" selected disabled>No Next Task</option>
                </select>
            </td>
            <td>
                <div class="task_done"><?= $task['done'] ?></div>
            </td>
            <td class="center">
                <div class="task_status <?= $status_ico ?>"></div>
            </td>
            <td>
                <button type="submit" data-action="delete_host_task">Borrar</button>
                <button type="submit" data-action="update_host_task" <?= $disabled ?>>Modificar</button>
                <button type="submit" data-action="force_exec_task" <?= $disabled ?>>Forzar</button>
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
