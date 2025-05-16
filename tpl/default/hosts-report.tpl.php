<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
/**
 * In frontend->getTpl()
 * @var array<string> $lng
 * @var array<mixed> $tdata
 */

?>
<div class="report-table-container">
    <table>
        <thead>
            <tr>
                <?php foreach ($tdata['keysToShow'] as $header) : ?>
                    <th><?= $header ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tdata['hosts'] as $host) : ?>
                <tr>
                    <?php foreach ($tdata['keysToShow'] as $key) : ?>
                        <td class="td-host-logs">
                        <?php
                        if ($key === 'online') :
                             $host_status = $host['online'] ? 'led-green-on' : $host_status = 'led-red-on';
                             ?>
                            <div class="host-led <?= $host_status ?>"></div>
                            <?php
                        elseif ($key === 'log_msgs') :
                            if (!isset($host['log_msgs']) || !is_array($host['log_msgs'])) {
                                # Alert/Warn on but Logs probably ACK skip
                                continue;
                            }
                            foreach ($host['log_msgs'] as $log_msg) :
                                ?>
                            <div class="row-logs-msg">
                                <input
                                    type="checkbox"
                                    name="ack_host_log"
                                    data-id="<?= $log_msg['log_id']?>"<?= $log_msg['ack_state'] ? 'checked' : null; ?>
                                    />
                                <?= $log_msg['msg'] . ' ' . $log_msg['event_type'] . ' ' . $log_msg['log_type'] ?>
                            </div>
                                <?php
                            endforeach;
                        else :
                            echo $host[$key] ?? '.';
                        endif;

                        ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
        <?php
        if (!empty($tdata['table_btn'])) :
            ?>
        <button id="<?= $tdata['table_btn'] ?>" onclick="submitCommand('<?= $tdata['table_btn']?>', {id: 0})">
            <?= $tdata['table_btn_name'] ?>
        </button>
            <?php
        endif;
        ?>
</div>
