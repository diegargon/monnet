<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
?>

<script>
    $(document).ready(function () {
        refresh();
    });

    function refresh() {

        $.get('../refresher.php', {show_services: <?= $cfg['show_services'] ?>, this_system: <?= $cfg['this_system'] ?>})
                .done(function (data) {
                    //console.log(data);
                    var jsonData = JSON.parse(data);
                    //console.log(jsonData);
                    if ($('#services').length === 0) {
                        $('#right_container').prepend(jsonData.services);
                    } else {
                        $('#services').remove();
                        $('#right_container').prepend(jsonData.services);
                    }
                    $('#this_system_container').html(jsonData.this_system);
                });


        setTimeout(refresh, <?= $cfg['services_update'] ?>);
    }


</script>