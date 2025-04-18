<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
/**
 * In frontend->getTpl()
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
                    console.log(data);
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

                    if (jsonData.command_receive === 'submitBookmarkCat') {
                        if(jsonData.response_msg) {
                            $('#stdbox-status-msg').append(jsonData.response_msg);
                        }
                        if(jsonData.command_success === 1) {
                            closeStdContainer();
                        }
                    }

                    if (
                            jsonData.command_receive === 'removeBookmarkCat' &&
                            jsonData.command_error === 0 &&
                            jsonData.response_msg > 0
                    ) {
                        var elementSelector = "#bookmarks_tab_" + jsonData.response_msg;
                        $(elementSelector).hide();
                    }

                    if (
                            jsonData.command_receive === 'removeBookmark' &&
                            jsonData.command_error === 0 &&
                            jsonData.response_msg > 0
                    ) {
                        var elementSelector = "#item_num_" + jsonData.response_msg;
                        $(elementSelector).hide();
                    }

                    if (jsonData.command_receive === 'remove_host' && !jsonData.command_error) {
                        $('#host-details').remove();
                    }

                    if (jsonData.command_receive === 'submitHost' ) {
                        if (!jsonData.command_error) {
                          closeStdContainer();
                        } else {
                            $('#stdbox_status_msg').append(jsonData.command_error_msg);
                        }
                    }

                    if (jsonData.command_receive === 'show_host_cat' && jsonData.command_success) {
                            let newState = jsonData.response_msg;
                            let imgSrc = `/tpl/<?= $ncfg->get('theme') ?>/img/${newState ? 'green.png' : 'red.png'}`;
                            $('.show_host_cat[data-catid="' + jsonData.id + '"] img').attr('src', imgSrc);
                    }

                    /* Host Cat Double Click */
                    if (jsonData.command_receive === 'show_host_only_cat' && jsonData.command_success) {
                        let excludedCategory = '[data-catid="' + jsonData.id + '"]';

                        // Select all red icons not in the current category
                        let redIcons = $('.show_host_cat img')
                            .not(excludedCategory)
                            .filter('[src$="red.png"]');

                        // Select all containers not in the current category
                        let otherContainers = $('.show_host_cat')
                            .not(excludedCategory);

                        // Check if all other containers have red icons
                        let allOtherRed = redIcons.length === otherContainers.length;

                        if (allOtherRed) {
                            // if all red except clicked category turn on all
                            $('.show_host_cat img').attr('src', '/tpl/<?= $ncfg->get('theme') ?>/img/green.png');
                        } else {
                            // if not turn all off except the clicked category
                            $('.show_host_cat img').not('[data-catid="' + jsonData.id + '"]').attr('src', '/tpl/<?= $ncfg->get('theme') ?>/img/red.png');
                            $('.show_host_cat[data-catid="' + jsonData.id + '"] img').attr('src', '/tpl/<?= $ncfg->get('theme') ?>/img/green.png');
                        }
                    }
                    if (
                        jsonData.command_receive === 'report_ansible' ||
                        jsonData.command_receive === 'report_ansible_hosts' ||
                        jsonData.command_receive === 'report_ansible_hosts_off' ||
                        jsonData.command_receive === 'report_ansible_hosts_fail' ||
                        jsonData.command_receive === 'report_agents_hosts' ||
                        jsonData.command_receive === 'report_agents_hosts_off' ||
                        jsonData.command_receive === 'report_agents_hosts_missing_pings' ||
                        jsonData.command_receive === 'report_alerts' ||
                        jsonData.command_receive === 'report_warns' ||
                        jsonData.command_receive === 'showAlarms' ||
                        jsonData.command_receive === 'showEvents'
                    ) {
                        closeStdContainer();
                        if(jsonData.response_msg) {
                            $("#stdbox-title").html(jsonData.command_receive);
                            $("#stdbox-container").css({
                                "display": "block",
                                "max-width": "50vw"
                            });
                            $('#stdbox-status-msg').append(jsonData.response_msg);
                        }

                    }

                    /* Reboot / Poweroff */
                    if (
                            jsonData.command_receive === 'reboot' ||
                            jsonData.command_receive === 'shutdown'
                    ) {
                        console.log(jsonData.response_msg);
                        closeStdContainer();
                        $("#stdbox-title").html(jsonData.command_receive);
                        $("#stdbox-container").css({
                            "display": "block",
                            "max-width": "50vw"
                        });

                        if (jsonData.command_success) {
                            $("#stdbox-content").html(
                                    '<pre>' + JSON.stringify(jsonData.response_msg, null, 2) + '</pre>'
                        );
                        } else if (jsonData.command_error) {
                            var f_error = jsonData.command_error_msg.replace(/\n/g, '<br>');
                            $("#stdbox-content").html(f_error);
                        }
                        $("#stdbox-content").css({
                            "max-width": "50vw",
                            "word-wrap": "break-word",
                            "white-space": "normal",
                            "overflow": "auto"
                        });
                    }
                    /* Mgmt Bookmark */
                    if (
                            jsonData.command_receive === 'mgmtBookmark' &&
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
                    /* Mgmt Networks */
                    if (jsonData.command_receive === 'mgmtNetworks') {
                        if (jsonData.command_success > 0) {
                            $('#mgmt-network-container').remove();
                            if ($.isEmptyObject(jsonData.mgmt_networks.cfg)) {
                                console.log('Error en la solicitud mgmt-bookmark:');
                                return;
                            }
                            let position = jsonData.mgmt_networks.cfg.place;
                            $(position).prepend(jsonData.mgmt_networks.data);
                            var mgmtNetwork = $(position).find("#mgmt-network-container");
                            makeDraggable(mgmtNetwork);
                            mgmtNetwork.css('display', 'block');
                        }
                        if (jsonData.command_error_msg){
                            $("#network_status_msg").html(jsonData.command_error_msg);
                        }
                    }
                    if (jsonData.command_receive === 'requestPool') {
                        if (jsonData.command_success > 0) {
                            $('#pool-container').remove();
                            if ($.isEmptyObject(jsonData.pool.cfg)) {
                                console.log('Error en la solicitud pool:');
                                return;
                            }
                            let position = jsonData.pool.cfg.place;
                            $(position).prepend(jsonData.pool.data);
                            var requestPool = $(position).find("#pool-container");
                            makeDraggable(requestPool);
                            requestPool.css('display', 'block');
                        }
                        if (jsonData.command_error_msg){
                            $("#pool_status_msg").html(jsonData.command_error_msg);
                        }
                    }
                    if (jsonData.command_receive === 'submitPoolReserver') {
                        if (jsonData.command_success > 0) {
                            const ip = jsonData.command_value;
                            const msg = jsonData.response_msg + ' ' + ip;
                            const button = $(`.submitPoolReserver[data-ip="${ip}"]`);
                            button.prop('disabled', true)
                                .text('Reserved')
                                .css('background-color', 'darkred')
                                .css('color', 'white');
                            $("#pool_status_msg").html(msg);
                        }
                        if (jsonData.command_error_msg){
                            $("#pool_status_msg").html(jsonData.command_error_msg);
                        }
                    }
                    /* Success */
                    if (
                            (jsonData.command_receive === 'updateBookmark' ||
                            jsonData.command_receive === 'addBookmark') &&
                            jsonData.command_success > 0 &&
                            jsonData.command_error === 0
                    ) {
                        $('#mgmt-bookmark-container').remove();
                    }
                    /* Error */
                    if (
                            jsonData.command_receive === 'addBookmark' &&
                            jsonData.command_error > 0
                    ) {
                        $('#error_msg').append(jsonData.command_error_msg);
                    }

                    if ("host_details" in jsonData) {
                        $('#host-details').remove();
                        if ($.isEmptyObject(jsonData.host_details.cfg)) {
                            console.log('Error en la solicitud host details: submitter.tpl');
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

                    if("force_hosts_refresh" in jsonData){
                        refresh(1);
                    }

                })

                .fail(function (xhr, status, error) {
                    console.error('Error en la solicitud AJAX: submiter-js.tpl', status, error);
                    console.error('Respuesta completa:', xhr.responseText);
                });
    }

</script>
