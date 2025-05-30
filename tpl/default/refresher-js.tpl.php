<?php
/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
/**
 * In frontend->getTpl()
 * @var Config $ncfg
 * @var array<string> $lng
 * @var array<mixed> $tdata
 */
?>
<script>
    $(document).ready(function () {
        refresh();
    });

    function updatePingStatus(responseData) {
        const maxLatencyWarning = 2;  // ms - amarillo/naranja
        const maxLatencyDanger = 5;   // ms - rojo
        const led = $('#heartbeatLed');

        // Clean all except base led
        $('.gateway-led').removeClass().addClass('gateway-led');
        led.attr('title', 'Gateway Status: ' + responseData.latency_ms + 'ms' + ' Version: ' + responseData.version);
        if (responseData.latency_ms >= maxLatencyDanger) {
            led.addClass('led-red blink3');
        } else if (responseData.latency_ms >= maxLatencyWarning) {
            led.addClass('led-orange blink3');
        } else if (responseData.latency_ms > 0) {
            led.addClass('led-green blink3');
        }
    }

    function updatePingStatusToError() {
        const led = $('#heartbeatLed');
        $('.led').removeClass().addClass('led led-rd-on');
    }

    function refresh(force = 0) {
        var requestData = {};

        $.post('refresher.php', requestData)
                .done(function (data, textStatus, xhr) {
                    var contentType = xhr.getResponseHeader('Content-Type');
                    //console.log(data);

                    // Verificamos si el Content-Type es JSON
                    if (contentType && contentType.toLowerCase().includes('application/json')) {
                        jsonData = (typeof data === 'object') ? data : JSON.parse(data);
                        //}
                        // Si el Content-Type es HTML o texto
                        //else if (contentType && contentType.includes('text/html')) {
                        //    jsonData = {response_msg: data};  // Parseamos la respuesta como texto HTML
                    } else {
                        console.warn("Tipo de contenido inesperado:", contentType);
                        return; // Terminamos si el tipo de contenido no es el esperado
                    }

                    //console.log(jsonData);
                    if (jsonData.login === "fail") {
                        location.href = '';
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

                    if ("misc" in jsonData) {
                        $('#host_totals').html(jsonData.misc.totals);
                        $('#host_onoff').html(jsonData.misc.onoff);
                        $('#last_refresher').html(jsonData.misc.last_refresher);
                        $('#cli_last_run').html(jsonData.misc.cli_last_run);
                        $('#discovery_last_run').html(jsonData.misc.discovery_last_run);
                    }
                    if ("footer_dropdown" in jsonData) {
                        // Obtener el contenedor donde se agregará el HTML
                        const container = document.getElementById("footer-dropdown-item-container");


                        container.innerHTML = '';

                        // Iterar sobre cada item en footer_dropdown
                        jsonData.footer_dropdown.forEach(item => {
                            // Crear el div del item
                            const itemDiv = document.createElement("div");
                            itemDiv.classList.add("footer-dropdown-item");

                            const numberDiv = document.createElement("div");
                            // Crear el enlace
                            const link = document.createElement("a");
                            link.href = "#";  //  href como # o vacío para evitar recarga de la página
                            link.textContent = item.value;
                            link.classList.add("footer-number");
                            // Asignar el evento click al enlace para llamar a la función
                            link.addEventListener("click", function(event) {
                                event.preventDefault();  // Evitar que el enlace haga su acción predeterminada
                                submitCommand('report_' + item.report_type, {id: 0});
                            });

                            if (item["number-color"] === "blue") {
                                link.classList.add("footer-number-blue");
                            } else if (item["number-color"] === "red") {
                                link.classList.add("footer-number-red");
                            } else if (item["number-color"] === "orange") {
                                link.classList.add("footer-number-orange");
                            }
                            numberDiv.appendChild(link);

                            const textDiv = document.createElement("div");
                            textDiv.classList.add("footer-text");
                            textDiv.textContent = item.desc;

                            itemDiv.appendChild(numberDiv);
                            itemDiv.appendChild(textDiv);
                            container.appendChild(itemDiv);
                        });
                    }

                    if (
                        "ping" in jsonData &&
                        "status" in jsonData.ping &&
                        jsonData.ping.status === 'success'
                    ) {
                        updatePingStatus(jsonData.ping);
                    } else {
                        updatePingStatusToError();
                    }
                })
                .fail(function (xhr, status, error) {
                    console.error('Error en la solicitud AJAX: refresher', status, error);
                });
        // Avoid set auto-refresh when a force call is execute
        if (!force) {
            setTimeout(refresh, <?= $ncfg->get('refresher_time') * 60000 ?>);
        }
    }
</script>
