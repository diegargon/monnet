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

    function confirmRefresh(action, param) {
        var confirmacion = confirm('<?= $lng['L_AREYOUSURE'] ?>');

        if (confirmacion) {
            refresh(action, param);
        }
    }

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
                    //console.log(requestData);
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

                    if ('categories_host' in jsonData) {
                        $('#hosts_cat').remove();
                        position = jsonData.categories_host.cfg.place;
                        $(position).prepend(jsonData.categories_host.data);
                    }
                    if ("term_logs" in jsonData) {
                        $('#term_container').remove();
                        position = jsonData.term_logs.cfg.place;
                        $(position).append(jsonData.term_logs.data);
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
                            $('#highlight-hosts').remove();
                            position = jsonData.highlight_hosts.cfg.place;
                            $(position).prepend(jsonData.highlight_hosts.data);
                        }
                    }

                    if ("host_details" in jsonData) {
                        $('#host-details').remove();
                        if ($.isEmptyObject(jsonData.host_details.cfg)) {
                            return;
                        }
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

                    if ("misc" in jsonData) {
                        $('#host_totals').html(jsonData.misc.totals);
                        $('#host_onoff').html(jsonData.misc.onoff);
                        $('#last_refresher').html(jsonData.misc.last_refresher);
                    }

                })
                .fail(function (xhr, status, error) {
                    console.error('Error en la solicitud AJAX:', status, error);
                });
        
        //Prevent launch another timeout on command
        if (command === false) {
            setTimeout(refresh, <?= $cfg['refresher_time'] * 60000 ?>);
    }

    }
</script>
