<?php
/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
/**
 * In frontend->getTpl()
 * @var array<string> $lng
 * @var array<mixed> $tdata
 */
!defined('IN_WEB') ? exit : true;
?>
<div id="pool-container" class="draggable">
    <div class="front-container-bar dragbar">
        <button id="close_pool" class="button-ctrl" type="submit">
            <img class="close_link" src="./tpl/<?= $ncfg->get('theme') ?>/img/close.png" title="<?= $lng['L_CLOSE'] ?>">
        </button>
        <div class="front-container-bar-title"><?= $lng['L_IP_POOL'] ?></div>
    </div>
    <div class="form_container">
        <div id="pool_status_msg"><?= isset($tdata['status_msg']) ? $tdata['status_msg'] : null ?></div>
        <table class="table-pool">
        <?php
        if (!empty($tdata['networks'])) :
            foreach ($tdata['networks'] as $network_pool) :
                foreach ($network_pool['pool'] as $pool_ip) :
                    ?>
                <tr>
                    <td>
                        <div class="network-name">
                            <?= $network_pool['name'] . ' (' . $network_pool['network'] . ')'?>
                        </div>
                    </td>
                    <td>
                        <div class="network-ip">
                            <?= $pool_ip ?>
                        </div>
                    </td>
                    <td>
                        <div class="network-occupancy">
                            <?= $network_pool['occupancy'] ?>%
                        </div>
                    </td>
                    <td>
                        <button
                            class="submitPoolReserver"
                            type="submit"
                            data-id="<?= $network_pool['id']?>"
                            data-ip="<?= $pool_ip ?>"
                            >
                            <?= $lng['L_RESERVE'] ?>
                        </button>
                    </td>
                </tr>
                    <?php
                endforeach;
            endforeach;
        endif;
        ?>
        </table>
    </div>
</div>
