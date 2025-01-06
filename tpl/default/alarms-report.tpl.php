<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
/**
 * In frontend->getTpl()
 * @var array<int|string, mixed> $cfg
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
            <?php foreach ($tdata['logs'] as $log) : ?>
                <tr>
                    <?php foreach ($tdata['keysToShow'] as $key) : ?>
                        <td class="td-host-logs">
                        <?php
                        if ($key === 'online' && (int) $host['online'] === 1) :
                            ?>
                            <img
                                class="hosts-online"
                                src="tpl/<?= $cfg['theme']?>/img/green2.png"
                                alt="online_status"
                                title="On"
                            >
                            <?php
                        elseif ($key === 'online' && (int) $host['online'] === 0) :
                            ?>
                            <img
                                class="hosts-offline"
                                src="tpl/<?= $cfg['theme']?>/img/red2.png"
                                alt="online_status"
                                title="Off">
                            <?php
                        elseif ($key === 'ack') :
                             ?>
                            <div>
                                <input
                                    type="checkbox"
                                    name="ack_host_log"
                                    data-id="<?= $log['id']?>"<?= $log['ack'] ? 'checked' : null; ?>
                                    />
                            </div>
                        <?php
                        else :
                            echo $log[$key] ?? '.';
                        endif;

                        ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
        if  (!empty($tdata['table_btn'])) :
    ?>
        <button id="<?= $tdata['table_btn'] ?>" onclick="submitCommand('<?= $tdata['table_btn']?>', {id: 0})">
        <?= $tdata['table_btn_name'] ?>
        </button>
     <?php
        endif;
    ?>
</div>
