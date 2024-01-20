<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;
?>
<canvas id="graficoLatencia" width="400" height="200"></canvas>
<script>
    var datatime_graph_format = "<?= $cfg['datatime_graph_format'] ?>";
    var timezone = "<?= $cfg['timezone'] ?>";
    var charset = "<?= $cfg['graph_charset'] ?>";

    var ctx = document.getElementById('graficoLatencia').getContext('2d');

    var data = <?php echo json_encode($tdata); ?>;

    var fechas = data.map(function (item) {
        return new Date(item.date).getTime()
    });

    var valores = data.map(function (item) {
        return item.value;
    });

    var myChart = new Chart(ctx, {
        type: 'line',
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
</script>    