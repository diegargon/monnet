<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
/**
 * In frontend->getTpl()
 * @var array<int|string, mixed> $cfg
 * @var array<string> $lng
 * @var array<mixed> $tdata
 */
?>
<?php if (isset($tdata['stats']) && is_array($tdata['stats'])) : ?>
    <h2><?= $lng['L_STATS'] ?></h2>
    <table>
        <thead>
            <tr>
                <th>Host</th>
                <?php
                // Tomamos las claves del primer host
                $keys = array_keys($tdata['stats'][key($tdata['stats'])]);
                foreach ($keys as $key) :
                    ?>
                    <th><?= is_string($key) ? ucwords($key) : $key; ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tdata['stats'] as $host => $stats) : ?>
                <tr>
                    <td><?= $host ?></td>
                    <td><?= $stats['changed'] ?></td>
                    <td><?= $stats['failures'] ?></td>
                    <td><?= $stats['ignored'] ?></td>
                    <td><?= $stats['ok'] ?></td>
                    <td><?= $stats['rescued'] ?></td>
                    <td><?= $stats['skipped'] ?></td>
                    <td><?= $stats['unreachable'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<button onclick="expandAll()">Expand All</button>
<button onclick="collapseAll()">Collapse All</button>
<?php
foreach ($tdata['plays'] as $playIndex => $play) : ?>
    <h2>Play: <?= $play['play']['name'] ?? "Play sin nombre" ?></h2>
    <div class="ansible_list">
        <?php
        foreach ($play['tasks'] as $taskIndex => $task) :
            ?>
        <div class="flex">Task:&nbsp;
            <div class="toggle" onclick="toggleSection('play<?= $playIndex ?>_task<?= $taskIndex ?>')">
            [+] <?= $task['task']['name'] ?? "Tarea sin nombre" ?>
            </div>
        </div>
            <?php
        endforeach;
        ?>
    </div>

    <?php
    foreach ($play['tasks'] as $taskIndex => $task) :
        ?>
    <div class="section hidden-section" id="play<?= $playIndex ?>_task<?= $taskIndex ?>">
        <h3><?= $task['task']['name'] ?? "Tarea sin nombre" ?></h3>
        <?php
        foreach ($task['hosts'] as $host => $host_result) :
            ?>
        <div class="flex">
            <div class="toggle" onclick="toggleSection('<?=$host?>_<?= $playIndex ?>_task<?= $taskIndex ?>')">
                [+] Host: <?= $host ?>
            </div>
            <?php
            if (!empty($host_result['changed']) && (var_export($host_result['changed'], true) == "true")) :
                print '  [Changed]';
            endif;
            if (!empty($host_result['failed']) && (var_export($host_result['failed'], true) == "true")) :
                print '  [Failed]';
            endif;
            if (!empty($host_result['ignored']) && (var_export($host_result['ignored'], true) == "true")) :
                print '  [Ignored]';
            endif;
            if (!empty($host_result['rescued']) && (var_export($host_result['rescued'], true) == "true")) :
                print '  [Rescued]';
            endif;
            if (!empty($host_result['skipped']) && (var_export($host_result['skipped'], true) == "true")) :
                print '  [Skipped]';
            endif;
            if (
                !empty($host_result['unreachable']) &&
                (var_export($host_result['unreachable'], true) == "true")
            ) :
                print '  [Unreachable]';
            endif;
            ?>
        </div>
        <div class="indent4 section hidden-section" id="<?= $host ?>_<?= $playIndex ?>_task<?= $taskIndex ?>">
            <?php
            echo array2Html($host_result);
            ?>
        </div>
            <?php
        endforeach;
        ?>
    </div>
        <?php
    endforeach;
    ?>
    <?php
endforeach;
