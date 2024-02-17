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
                    borderWidth: 2,
                }]
        },
        options: {
            responsive: true,
            barPercentage: 1.0,
            categoryPercentage: 1.0,

            scales: {
                x: {
                    type: 'time',
                    offset: true,
                    ticks: {
                        callback: function (value, index, values) {
                            var date = new Date(value);
                            var options = {timeZone: timezone, hour12: false, hour: 'numeric', minute: 'numeric', hourCycle: 'h23'};
                            return date.toLocaleString(charset, options);
                        }
                    },
                    time: {
                        stacked: true,
                        //stepSize: 20,
                        //unitStepSize: 20,
                        //round: 'hour',
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
            layout: {
                padding: {
                    left: 0,
                    right: 0,
                    top: 0,
                    bottom: 0
                }
            },
            elements: {
                bar: {
                    borderWidth: 1,
                    barThickness: 'flex' // 'flex' significa que el ancho se ajustará automáticamente
                }
            }

        }
    });


    var zoomLevel = 0;
    var removedData = [];

    document.getElementById('zoomOutButton').addEventListener('click', function () {
        if (zoomLevel < 50) {
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