<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
/**
 * In frontend->getTpl()
 * @var array<int|string, mixed> $cfg
 * @var array<string> $lng
 * @var array<mixed> $tdata
 */
?>
<script>
    function confirmSubmit(action, param) {
        event.stopPropagation();
        var confirmacion = confirm('<?= $lng['L_AREYOUSURE'] ?>');

        if (confirmacion) {
            submitCommand(action, param);
        }
    }

    function submitCommand(command, command_values = {}) {
        var requestData = {
            command: command,
            command_values: command_values
        };

        if (typeof command === 'undefined') {
            command = false;
        }
        if (typeof command_values === 'undefined' || command_values === null) {
            command_values = {};
        }
        console.log(requestData);
        $.post('submitter.php', requestData)
                .done(function (data, textStatus, xhr) {
                    var contentType = xhr.getResponseHeader('Content-Type');
                    var jsonData;

                    // Verificamos si el Content-Type contiene "application/json"
                    if (contentType && contentType.toLowerCase().includes('application/json')) {
                        jsonData = (typeof data === 'object') ? data : JSON.parse(data);
                    //}
                    // Verificamos si el Content-Type contiene "text/html"
                    //else if (contentType && contentType.toLowerCase().includes('text/html')) {
                    //    jsonData = {response_msg: data};  // Parseamos la respuesta como texto HTML
                    //    console.log(jsonData);
                    //    return;
                    } else {
                        console.warn("Tipo de contenido inesperado:", contentType);
                        return;
                    }
                    console.log(jsonData);
                    // Procesamiento de jsonData
                    if (jsonData.login === "fail") {
                        location.href = '';
                    }

                    if ('categories_host' in jsonData) {
                        $('#hosts_cat').remove();
                        let position = jsonData.categories_host.cfg.place;
                        $(position).prepend(jsonData.categories_host.data);
                    }
                    if ("term_logs" in jsonData) {
                        $('#term_container').remove();
                        let position = jsonData.term_logs.cfg.place;
                        $(position).append(jsonData.term_logs.data);
                    }

                    if (jsonData.command_receive === 'removeBookmark') {
                        var commandValue = jsonData.command_value;
                        var elementSelector = "#item_num_" + commandValue;
                        $(elementSelector).hide();
                    }

                    if (
                            jsonData.command_receive === 'editBookmark' &&
                            jsonData.command_success > 0
                    ) {
                        $('#mgmt-bookmark-container').remove();
                        if ($.isEmptyObject(jsonData.mgmt_bookmark.cfg)) {
                            console.log('Error en la solicitud mgmt-bookmark:');
                            return;
                        }
                        let position = jsonData.mgmt_bookmark.cfg.place;
                        $(position).prepend(jsonData.mgmt_bookmark.data);
                        var mgmtBookmark = $(position).find("#mgmt-bookmark-container");
                        makeDraggable(mgmtBookmark);
                        mgmtBookmark.css('display', 'block');
                    }

                    if ("host_details" in jsonData) {
                        $('#host-details').remove();
                        if ($.isEmptyObject(jsonData.host_details.cfg)) {
                            console.log('Error en la solicitud host details:');
                            return;
                        }
                        let position = jsonData.host_details.cfg.place;
                        $(position).prepend(jsonData.host_details.data);
                        var hostDetails = $(position).find("#host-details");
                        makeDraggable(hostDetails);

                        $('#tab1_btn').addClass('active');
                        $('#tab1').addClass('active');
                        var textNote = document.getElementById('textnotes');
                        var debounceTimeout;
                        var note_id = $('#host_note_id').val();

                        textNote.addEventListener('input', function () {
                            clearTimeout(debounceTimeout);
                            debounceTimeout = setTimeout(function () {
                                $.post('submitter.php', {
                                    command: 'saveNote',
                                    command_values:{
                                        id: note_id,
                                        value: encodeURIComponent(textNote.value.replace(/[']/g, '"'))
                                    }
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

                    if("force_host_refresh" in jsonData){
                        refresh(1);
                    }

                })

                .fail(function (xhr, status, error) {
                    console.error('Error en la solicitud AJAX:', status, error);
                });
    }

</script>
