<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
/**
 * @var Config $ncfg
 * @var array<string> $lng Language data
 * @var array<mixed> $tdata Template Data
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
                        if ($key === 'ack') :
                            ?>

                                <label class="ack_log_label">
                                <input
                                    type="checkbox"
                                    name="ack_host_log"
                                    data-id="<?= $log['id']?>"<?= $log['ack'] ? 'checked' : null; ?>
                                    />
                                </label>
                            <?php
                        elseif ($key === 'msg'):
                            ?>
                            <div class="row-logs-msg"><?= $log[$key]?></div>
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
    if (!empty($tdata['table_btn'])) :
        ?>
        <button id="<?= $tdata['table_btn'] ?>" onclick="submitCommand('<?= $tdata['table_btn']?>', {id: 0})">
        <?= $tdata['table_btn_name'] ?>
        </button>
        <?php
    endif;
    ?>
</div>
