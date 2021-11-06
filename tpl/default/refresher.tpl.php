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

    function refresh(command, command_value) {
        if (typeof command === 'undefined' || typeof command_value === 'undefined') {
            command = false;
            command_value = false;
        }
        $.get('refresher.php', {order: command, order_value: command_value})
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
                    if ("host_details" in jsonData) {
                        if ($('#host-details').length === 0) {
                            $('#center_container').prepend(jsonData.host_details);
                        } else {
                            $('#host-details').remove();
                            $('#center_container').prepend(jsonData.host_details);
                        }
                    }
                });

        // avoid launch timer when command FIX:better way for not launch timers, disable timer and allow launch
        if (command === false) {
            setTimeout(refresh, 5000);
        }

    }
</script>