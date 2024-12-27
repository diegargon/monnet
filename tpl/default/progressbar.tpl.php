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
 * @var Config $ncfg
 * @var array<string> $lng
 * @var array<mixed> $tdata
 */
/*
$tdata = [
    'progress_bar_width' => '175',
    'progress_bar_data' => [
        1 => ['value' => 93,'legend' => 'Test 3', 'min' => 0, 'max' => 100],
        2 => ['value' => 33,'legend' => 'Test 4', 'min' => 0, 'max' => 100],
    ]
];
*/
?>
<style>
.progress-bar-container {
    width: 100%;
    max-width: <?= $progress_bar_width?>px;
    position: relative;
}

.pbar_legend {
    font-size: 11px;
    font-weight: bold;
    color: #333;
}

.progress-bar {
    height: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
    background-color: #f4f4f4;
    box-sizing: border-box;
    position: relative;
    overflow: hidden;
    box-shadow: 0 2px 3px rgba(0,0,0,.5) inset;
}
</style>

<?php
foreach ($tdata['progress_bar_data'] as $pbar) :
    $percentage = ($pbar['value'] / $pbar['max']) * 100;

    $gradientParts = [];

    if ($percentage > 0) {
        $yellowEnd = min($percentage, 25);
        $gradientParts[] = "yellow 0%, yellow {$yellowEnd}%";
    }

    if ($percentage > 25) {
        $greenStart = 25;
        $greenEnd = min($percentage, 80);
        $gradientParts[] = "green {$greenStart}%, green {$greenEnd}%";
    }


    if ($percentage > 80) {
        $redStart = 80;
        $redEnd = $percentage;
        $gradientParts[] = "red {$redStart}%, red {$redEnd}%";
    }

    if ($percentage < 100) {
        $blackStart = $percentage;
        $gradientParts[] = "#cacaca {$blackStart}%, #cacaca 100%";
    }

    $gradient = "linear-gradient(to right, " . implode(', ', $gradientParts) . ")";
?>
<div class="progress-bar-container">
    <div class="pbar_legend"><?= $pbar['legend'] ?> (<?= $pbar['value'] ?>%)</div>
    <div class="progress-bar" style="background: <?= $gradient ?>;"></div>
</div>
<?php
endforeach;

