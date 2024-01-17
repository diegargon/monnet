<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
?>

<script>
    $(document).ready(function () {
        refresh();
    });

    function refresh(command, command_value) {
        if (typeof command === 'undefined') {
            command = false;
        }
        if (typeof command_value === 'undefined') {
            command_value = false;
        }
        $.get('refresher.php', {order: command, order_value: command_value})
                .done(function (data) {
                    console.log(data);
                    var jsonData = JSON.parse(data);
                    //console.log(jsonData);
                    if (jsonData.login === "fail") {
                        location.href = '';
                    }
                    if ("other_hosts" in jsonData) {
                        if ($('#other-hosts').length === 0) {
                            position = jsonData.other_hosts.cfg.place;
                            $(position).prepend(jsonData.other_hosts.data);
                        } else {
                            $('#other-hosts').remove();
                            position = jsonData.other_hosts.cfg.place;
                            $(position).prepend(jsonData.other_hosts.data);
                        }
                    }
                    if ("highlight_hosts" in jsonData) {
                        if ($('#highlight-hosts').length === 0) {
                            position = jsonData.highlight_hosts.cfg.place;
                            $(position).prepend(jsonData.highlight_hosts.data);
                        } else {
                            position = jsonData.highlight_hosts.cfg.place;
                            $('#highlight-hosts').remove();
                            $(position).prepend(jsonData.highlight_hosts.data);
                        }
                    }
                    if ("host_details" in jsonData) {
                        if ($('#host-details').length === 0) {
                            position = jsonData.host_details.cfg.place;
                            $(position).prepend(jsonData.host_details.data);
                            $('#tab1_btn').addClass('active');
                            $('#tab1').addClass('active');
                        } else {
                            $('#host-details').remove();
                            position = jsonData.host_details.cfg.place;
                            $(position).prepend(jsonData.host_details.data);
                            $('#tab1_btn').addClass('active');
                            $('#tab1').addClass('active');
                        }
                    }
                });

        // avoid launch timer when command FIX:better way for not launch timers, disable timer and allow launch
        if (command === false) {
            setTimeout(refresh, 150000);
        }

    }
</script>