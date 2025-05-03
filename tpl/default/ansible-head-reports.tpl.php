<?php
/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
/**
 * In frontend->getTpl()
 * @var Config $ncfg
 * @var array<string> $lng
 * @var array<mixed> $tdata
 */
!defined('IN_WEB') ? exit : true;
?>
<div id="reports-container">
    <div class="front-container-bar-title"><?= $lng['L_REPORTS'] ?></div>
    <div class="form_container">
        <div id="report_status_msg"><?= isset($tdata['status_msg']) ? $tdata['status_msg'] : null ?></div>
        <table class="table-report">
        <?php
        foreach ($tdata['reports'] as $report) :
            ?>
            <tr id="report_row_<?= $report['id']?>">
                <td>
                    <div class="report-name">
                        <?= $report['pb_name'] ?>
                    </div>
                </td>
                <td>
                    <div class="report-source">
                        <?= $report['source_id'] ?>
                    </div>
                </td>
                <td>
                    <div class="report-date">
                        <?= $report['user_date'] ?>
                    </div>
                </td>
                <td>
                    <button
                        class="submitViewReport"
                        onclick="requestHostDetails('submitViewReport',{id: <?= $report['id'] ?>, action: 'view'})"
                        type="submit"
                        >
                        <?= $lng['L_VIEW'] ?>
                    </button>
                    <button id="submitDeleteReport"
                        onclick="requestHostDetails('submitDeleteReport',{id: <?= $report['id'] ?>, action: 'delete'})"
                        type="submit">
                        <?= $lng['L_DELETE'] ?>
                    </button>
                </td>
            </tr>
            <?php
        endforeach;
        ?>
        </table>
    </div>
</div>
