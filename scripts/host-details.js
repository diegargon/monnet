$(document).ready(function () {
    var hostId = $('#host_id').val();
    var logType = $('#log_type').val();
    var logSize = $('#log_size').val();
    $(document).on("click", "#logs_reload_btn", function () {
        requestHostDetails('logs-reload', {id: hostId, log_type: logType, log_size: logSize});
    });

});

function requestHostDetails(command, command_values = []) {
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
                //console.log("Content-Type:", contentType);

                var jsonData;

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

                console.log(jsonData);
                if (jsonData.login === "fail") {
                    location.href = '';
                }

                if (jsonData.command_receive === 'logs-reload') {
                    if (jsonData.command_success === 1) {
                        $('#term_output').html(jsonData.response_msg);
                    } else {
                        $('#term_output').html('Error');
                    }
                }
            })
            .fail(function (xhr, status, error) {
                console.error('Error en la solicitud AJAX:', status, error);
            });
}
