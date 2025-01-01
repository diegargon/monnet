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
        1 => ['value' => 93,'legend' => 'Test 3', 'min' => 0, 'max' => 100, 'tooltip' => 'test'],
        2 => ['value' => 33,'legend' => 'Test 4', ...],
    ]
];
*/
$progress_bar_width = $tdata['progress_bar_width'] ?? '175'
?>
<style>
.progress-bar-container {
    width: 100%;
    max-width: <?= $progress_bar_width?>px;
    position: relative;
}
</style>

<?php
foreach ($tdata['progress_bar_data'] as $pbar) :
    $percentage = ($pbar['value'] / $pbar['max']) * 100;

    $gradientParts = [];

    if ($percentage > 0) :
        $yellowEnd = min($percentage, 25);
        $gradientParts[] = "yellow 0%, yellow {$yellowEnd}%";
    endif;

    if ($percentage > 25) :
        $greenStart = 25;
        $greenEnd = min($percentage, 80);
        $gradientParts[] = "green {$greenStart}%, green {$greenEnd}%";
    endif;


    if ($percentage > 80) :
        $redStart = 80;
        $redEnd = $percentage;
        $gradientParts[] = "red {$redStart}%, red {$redEnd}%";
    endif;

    if ($percentage < 100) :
        $blackStart = $percentage;
        $gradientParts[] = "#cacaca {$blackStart}%, #cacaca 100%";
    endif;

    $gradient = "linear-gradient(to right, " . implode(', ', $gradientParts) . ")";
    ?>
<div class="progress-bar-container">
    <div class="pbar_legend"><?= $pbar['legend'] ?></div>
    <div class="progress-bar"
         style="background: <?= $gradient ?>;"
         data-tooltip="<?= $pbar['tooltip'] ?? '' ?>"
         >
    </div>
</div>
    <?php
endforeach;

