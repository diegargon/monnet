<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;
?>

<canvas id="graficoLatencia" width="400" height="200"></canvas>
<button id="zoomInButton">Ampliar</button>
<button id="zoomOutButton">Reducir</button>
<script>
    var datatime_graph_format = "<?= $cfg['datatime_graph_format'] ?>";
    var timezone = "<?= $cfg['timezone'] ?>";
    var charset = "<?= $cfg['graph_charset'] ?>";

    var ctx = document.getElementById('graficoLatencia').getContext('2d');

    var data = <?php echo json_encode($tdata); ?>;

    var fechas = data.map(function (item) {
        return new Date(item.date).getTime();
    });

    var valores = data.map(function (item) {
        return item.value;
    });

    var myChart = new Chart(ctx, {
        // line, bar,radar doughnut, pie ,polarArea, bubble
        type: 'bar',
        data: {
            labels: fechas,
            datasets: [{
                    label: 'Latencia',
                    data: valores,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    fill: false
                }]
        },
        options: {
            scales: {
                x: {
                    type: 'time',
                    ticks: {
                        callback: function (value, index, values) {
                            var date = new Date(value);
                            var options = {timeZone: timezone, hour12: false, hour: 'numeric', minute: 'numeric', hourCycle: 'h23'};
                            return date.toLocaleString(charset, options);
                        }
                    },
                    time: {
                        unit: 'minute'
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)' // grid x
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)' // grid y
                    }
                }
            },

            plugins: {
                legend: {
                    labels: {
                        color: 'white' // Legend text
                    }
                }
            },
            elements: {
                point: {
                    radius: 4, // Points
                    backgroundColor: 'rgba(75, 192, 192, 1)', // Points color
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2
                }
            }
        }
    });


    var zoomLevel = 0;
    var removedData = [];

    document.getElementById('zoomOutButton').addEventListener('click', function () {
        if (zoomLevel < 23) {
            zoomLevel++;

            for (var i = 0; i < 10; i++) {
                removedData.push({
                    label: myChart.data.labels.pop(),
                    value: myChart.data.datasets[0].data.pop()
                });
            }
            myChart.update();
        }
    });

    document.getElementById('zoomInButton').addEventListener('click', function () {
        if (zoomLevel > 0 && removedData.length > 0) {
            zoomLevel--;

            for (var i = 0; i < 10; i++) {
                var dataToAdd = removedData.pop();
                myChart.data.labels.push(dataToAdd.label);
                myChart.data.datasets[0].data.push(dataToAdd.value);
            }
            myChart.update();
        }
    });
</script>