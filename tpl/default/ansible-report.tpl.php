<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
/**
 * @var Config $ncfg
 * @var array<string> $lng Language data
 * @var array<mixed> $report Template Data
 */

 # TODO: Refactor to remove this dependency
 use App\Utils\MiscUtils;

$report = $tdata['result'];
?>

<?php if (isset($report['stats']) && is_array($report['stats'])) : ?>
    <h2><?= $lng['L_STATS'] ?></h2>
    <table>
        <thead>
            <tr>
                <th>Host</th>
                <?php
                $keys = array_keys($report['stats'][key($report['stats'])]);
                foreach ($keys as $key) :
                    ?>
                    <th><?= is_string($key) ? ucwords($key) : $key; ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($report['stats'] as $host => $stats) : ?>
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
<!-- SUMMARY BUILD -->
<?php
$messages = [];

foreach ($report['plays'] as $play) {
    foreach ($play['tasks'] as $task) {
        foreach ($task['hosts'] as $ip => $hostData) {
            $msg = '';
            $extractedMessages = ArrayUtils::extractMessages($hostData);
            if (!empty($extractedMessages)) {
                $msg .= implode("", $extractedMessages);
            }

            if (!empty($msg)) {
                $messages[] = [
                    'ip' => $ip,
                    'msg' => $msg,
                ];
            }
        }
    }
}
?>
<!-- SUMMARY -->
    <?php
    if (!empty($messages)) :
        ?>
    <h2><?= $lng['L_SUMMARY']?>:</h2>
        <?php
        foreach ($messages as $msg) :
            if (!empty($msg['msg'])) :
                ?>
                <div><?= $msg['ip'] ?></div>
                <pre><?php echo $msg['msg']; ?></pre>
                <?php
            endif;
        endforeach;
    endif;
    ?>
<!-- DETAIL VIEW -->
    <h2><?= $lng['L_DETAIL_VIEW'] ?></h2>
    <button onclick="expandAll()">Expand All</button>
    <button onclick="collapseAll()">Collapse All</button>
<?php
$messages = [];

foreach ($report['plays'] as $playIndex => $play) : ?>
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
