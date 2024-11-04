<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
/**
 * In frontend->getTpl()
 * @var array<int|string, mixed> $cfg
 * @var array<string> $lng
 * @var array<mixed> $tdata
 */
?>
<script>
    $(document).ready(function () {
        refresh();
    });

    function confirmRefresh(action, param) {
        event.stopPropagation();
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
        //console.log(requestData);
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

                    if (jsonData.command_receive === 'removeBookmark') {
                        var commandValue = jsonData.command_value;
                        var elementSelector = "#item_num_" + commandValue;
                        $(elementSelector).hide();
                    }
                    if (jsonData.command_receive === 'submitScanPorts') {
                        if (jsonData.command_success === 1) {
                            $('#config_status_msg').html('OK');
                            $('#host-title').val(jsonData.command_success);
                        } else {
                            $('#config_status_msg').html('Error');
                        }
                    }
                    if (jsonData.command_receive === 'addNetwork') {
                        $('#network_status_msg').html('');
                        $('#network_error_msg').html('');
                        if (jsonData.command_success && jsonData.response_msg) {
                            $('#networkName').html('');
                            $('#network').html('');
                            $('#networkCIDR').html('');
                        }
                        if (jsonData.command_error_msg) {
                            $('#network_error_msg').html(jsonData.command_error_msg);
                        }
                    }
                    if (jsonData.command_receive === 'addBookmark') {
                        $('#status_msg').html('');
                        $('#error_msg').html('');
                        if (jsonData.command_success && jsonData.response_msg) {
                            $('#status_msg').html(jsonData.response_msg);
                            $('#bookmarkName').val('');
                            $('#urlip').val('');
                            $('#field_img').val('');
                        }
                        if (jsonData.command_error_msg) {
                            $('#error_msg').html(jsonData.command_error_msg);
                        }
                    }
                    //Bookmarks Hosts Config stdbox
                    if (
                            jsonData.command_receive === 'submitBookmarkCat' ||
                            jsonData.command_receive === 'submitHostsCat'
                    ) {
                        $('#stdbox-status-msg').html('');
                        $('#stdbox-error-msg').html('');
                        if (jsonData.command_success && jsonData.response_msg) {
                            $('#stdbox-status-msg').html(jsonData.response_msg);
                            $('#stdbox-content').empty();
                        }
                        if (jsonData.command_error_msg) {
                            $('#stdbox-error-msg').html(jsonData.command_error_msg);
                        }
                    }
                    //Add remote host
                    if (jsonData.command_receive === 'submitHost') {
                        $('#stdbox-status-msg').html('');
                        $('#stdbox-error-msg').html('');
                        if (jsonData.command_success && jsonData.response_msg) {
                            $('#stdbox-status-msg').html(jsonData.response_msg);
                            $('#addedHost').val('');
                        }
                        if (jsonData.command_error_msg) {
                            $('#stdbox-error-msg').html(jsonData.command_error_msg);
                        }
                    }
                    if (jsonData.command_receive === 'submitTitle') {
                        if (jsonData.command_success) {
                            $('#config_status_msg').html('Validated:' + jsonData.command_value);
                        } else {
                            $('#config_status_msg').html('Error: ' + jsonData.command_value);
                        }
                        $('#host-title').val(jsonData.command_value);
                    }
                    if (jsonData.command_receive === 'submitCat') {
                        if (jsonData.command_success === 1) {
                            $('#config_status_msg').html(jsonData.response_msg);
                        } else {
                            $('#config_status_msg').html('Error');
                        }
                    }
                    if (jsonData.command_receive === 'submitManufacture') {
                        if (jsonData.command_success === 1) {
                            $('#config_status_msg').html(jsonData.response_msg);
                        } else {
                            $('#config_status_msg').html('Error');
                        }
                    }
                    if (jsonData.command_receive === 'submitOS') {
                        if (jsonData.command_success === 1) {
                            $('#config_status_msg').html(jsonData.response_msg);
                        } else {
                            $('#config_status_msg').html('Error');
                        }
                    }
                    if (jsonData.command_receive === 'submitHostToken') {
                        if (jsonData.command_success === 1) {
                            $('#host_token').val(jsonData.response_msg);
                        } else {
                            $('#host_token').val('Error');
                        }
                    }
                    if (jsonData.command_receive === 'submitSystemType') {
                        if (jsonData.command_success === 1) {
                            $('#config_status_msg').html(jsonData.response_msg);
                        } else {
                            $('#config_status_msg').html('Error');
                        }
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
                        var hostDetails = $(position).find("#host-details");
                        makeDraggable(hostDetails);

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
                        $('#cli_last_run').html(jsonData.misc.cli_last_run);
                        $('#discovery_last_run').html(jsonData.misc.discovery_last_run);
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
