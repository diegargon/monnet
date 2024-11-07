$(document).ready(function () {
    var host_id = $('#host_id').val();
    $(document).on("click", "#logs_reload_btn", function () {
        requestHostDetails('logs-reload', host_id);
    });

});

function requestHostDetails(command, command_value, object_id = null) {
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
    $.post('submitter.php', requestData)
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