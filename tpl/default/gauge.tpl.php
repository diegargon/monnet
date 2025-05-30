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
/*
    $tdata = [
        'gauge_container_width' => 100,
        'gauge_graphs' => [
            1 => ['value' => 93, 'legend' => 'Test 1', 'min' => 0, 'max' => 100],
            2 => ['value' => 43, 'legend' => 'Test 2', 'min' => 0, 'max' => 100],
            3 => ['value' => 20, 'legend' => 'Test 3', 'min' => 0, 'max' => 100],
            4 => ['value' => 80, 'legend' => 'Test 4', 'min' => 0, 'max' => 100],
        ],
    ];
 */

$gauge_container_width = $tdata['gauge_container_width'] ?? 100;
$numSize = $tdata['num_size'] ?? round($gauge_container_width / 4);
$textSize = $tdata['text_size'] ?? round($gauge_container_width / 8);
$unique_prefix = uniqid('gauge_', true);
?>
<style>
    .chart-container {
        width: <?= $gauge_container_width ?>px;
        height: <?= $gauge_container_width ?>px;
        border: 1px outset black;
        padding: 6px;
        background-color: #392a2a;
        margin-right: -8px;
    }
</style>

<?php foreach ($tdata['gauge_graphs'] as $id => $graph) : ?>
    <div class="chart-container">
        <canvas id="<?= $unique_prefix ?>_chart_<?= $id ?>" class="border border-gray-600"></canvas>
    </div>
<?php endforeach; ?>

<script>
    const chartsData = <?= json_encode($tdata['gauge_graphs']) ?>;
    const uniquePrefix = "<?= $unique_prefix ?>";
    const textSize = <?= $textSize ?>;
    const numSize = <?= $numSize ?>;

    Object.keys(chartsData).forEach(id => {
        const graph = chartsData[id];
        const ctx = document.getElementById(`${uniquePrefix}_chart_${id}`).getContext("2d");

        const gaugeNeedle = {
            id: "gaugeNeedle",
            afterDatasetDraw(chart, args, options) {
                const {
                    ctx,
                    data,
                    chartArea: {top, bottom, left, right}
                } = chart;

                ctx.save();
                const needleValue = data.datasets[0].needleValue;
                const angle = Math.PI + ((needleValue - graph.min) / (graph.max - graph.min)) * Math.PI;

                const cx = chart._metasets[0].data[0].x;
                const cy = chart._metasets[0].data[0].y;
                const outerRadius = chart._metasets[0].data[0].outerRadius;
                const innerRadius = chart._metasets[0].data[0].innerRadius;

                // Needle
                ctx.translate(cx, cy);
                ctx.rotate(angle);
                ctx.beginPath();
                ctx.fillStyle = "#4101ff";
                ctx.fillRect(innerRadius - 15, -2, 30, 4);

                // Needle Dot
                ctx.translate(-cx, -cy);
                ctx.beginPath();
                ctx.arc(cx, cy, 1, 0, 1 * Math.PI);
                ctx.fill();
                ctx.restore();

                ctx.font = `bold ${numSize}px sans-serif`;
                ctx.fillStyle = "#69c97a";
                ctx.textAlign = "center";
                ctx.fillText(needleValue, cx, cy - 0);

                ctx.font = `normal ${textSize}px sans-serif`;
                ctx.fillStyle = "#ffa9a9";
                ctx.fillText(graph.legend, cx, cy + 15);
            }
        };

        const getBackgroundColor = (value) => {
            if (value >= 0 && value <= 20) {
                return ["#f1c232", "#E0E0E0"];
            } else if (value > 20 && value <= 75) {
                return ["#69c97a", "#E0E0E0"];
            } else if (value > 75 && value <= 90) {
                return ["#e69138", "#E0E0E0"];
            } else {
                return ["#cc0000", "#E0E0E0"];
            }
        };

        new Chart(ctx, {
            type: "doughnut",
            data: {
                datasets: [
                    {
                        data: [graph.value, (graph.max - graph.value)],
                        backgroundColor: getBackgroundColor(graph.value),
                        borderColor: ["#69c97a", "#cdd5e1"],
                        needleValue: graph.value,
                        borderWidth: 1,
                        cutout: "75%",
                        circumference: 180,
                        rotation: -90
                    }
                ]
            },
            options: {
                layout: {
                    padding: 0
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: false
                    }
                },
                animation: false
            },
            plugins: [gaugeNeedle]
        });
    });
</script>
