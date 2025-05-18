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

<div id="term_container<?= isset($tdata['host_id']) ? '_' . $tdata['host_id'] : null; ?>" class="term_container">
    <div class="term_crystal">
        <div id="term_frame" class="frame glow">
            <div  id="term-output" class="term-output">
                <?php
                if (!empty($tdata['term_logs'])) {
                    echo implode('', $tdata['term_logs']);
                }
                ?>
            </div>
        </div>
    </div>
</div>
