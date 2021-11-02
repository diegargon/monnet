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
<?php /* $.get('../refresher.php', {show_services: <?= $cfg['show_services'] ?>, this_system: <?= $cfg['this_system'] ?>}) */ ?>
        $.get('refresher.php')
                .done(function (data) {
                    //console.log(data);
                    var jsonData = JSON.parse(data);
                    console.log(jsonData);
                    if ("hosts" in jsonData) {
                        if ($('#hosts').length === 0) {
                            $('#right_container').prepend(jsonData.hosts);
                        } else {
                            $('#hosts').remove();
                            $('#right_container').prepend(jsonData.hosts);
                        }
                    }
                });


        setTimeout(refresh, 5000);
    }


</script>