<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
?>

<script>
    $(document).ready(function () {
        refresh();
    });

    function refresh(command, command_value, object_id = null) {
        var requestData = {order: command, order_value: command_value};

        if (object_id !== null) {
            requestData.object_id = object_id;
        }

        if (typeof command === 'undefined') {
            command = false;
        }
        if (typeof command_value === 'undefined') {
            command_value = false;
        }
        $.post('refresher.php', requestData)
                .done(function (data, textStatus, xhr) {
                    var contentType = xhr.getResponseHeader('Content-Type');
                    console.log(data);
                    if (typeof data === 'object') {
                        //console.log('ya es un objeto');
                        jsonData = data;
                    } else {
                        //console.log('no es un objeto');
                        var jsonData = JSON.parse(data);
                    }

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
                        $('#host-details').remove();
                        position = jsonData.host_details.cfg.place;
                        $(position).prepend(jsonData.host_details.data);
                        $('#tab1_btn').addClass('active');
                        $('#tab1').addClass('active');
                        var textNote = document.getElementById('textnotes');
                        var debounceTimeout;
                        var object_id = $('#host_note_id').val();

                        textNote.addEventListener('input', function () {
                            clearTimeout(debounceTimeout);
                            debounceTimeout = setTimeout(function () {
                                $.post('refresher.php', {
                                    order: 'saveNote',
                                    order_value: encodeURIComponent(textNote.value.replace(/[']/g, '"')),
                                    object_id: object_id
                                })
                                        .done(function (response) {
                                            console.log(response);
                                        })
                                        .fail(function (error) {
                                            console.error('Error:', error);
                                        });
                            }, 600);
                        });

                    }

                })
                .fail(function (xhr, status, error) {
                    console.error('Error en la solicitud AJAX:', status, error);
                });


        // avoid launch timer when command FIX:better way for not launch timers, disable timer and allow launch
        if (command === false) {
            setTimeout(refresh, 75000);
    }

    }
</script>