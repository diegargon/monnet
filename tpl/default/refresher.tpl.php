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
<?php /* $.get('../refresher.php', {show_applinks: <?= $cfg['show_applinks'] ?>, this_system: <?= $cfg['this_system'] ?>}) */ ?>
        $.get('refresher.php')
                .done(function (data) {
                    console.log(data);
                    var jsonData = JSON.parse(data);
                    //console.log(jsonData);
                    if ("rest_hosts" in jsonData) {
                        if ($('#rest-hosts').length === 0) {
                            $('#right_container').prepend(jsonData.rest_hosts);
                        } else {
                            $('#rest-hosts').remove();
                            $('#right_container').prepend(jsonData.rest_hosts);
                        }
                    }
                    if ("highlight_hosts" in jsonData) {
                        if ($('#highlight-hosts').length === 0) {
                            $('#right_container').prepend(jsonData.highlight_hosts);
                        } else {
                            $('#highlight-hosts').remove();
                            $('#right_container').prepend(jsonData.highlight_hosts);
                        }
                    }
                });


        setTimeout(refresh, 5000);
    }


</script>