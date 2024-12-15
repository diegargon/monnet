/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */

$(document).ready(function () {
    $(document).off("click", "#logs_reload_btn").on("click", "#logs_reload_btn", function () {
        var hostId = $('#host_id').val();
        var logLevel = $('#log_level').val();
        var logSize = $('#log_size').val();
        requestHostDetails('logs-reload', {id: hostId, log_level: logLevel, log_size: logSize});
    });

    $(document).off("click", "#facts_reload_btn").on("click", "#facts_reload_btn", function () {
        var hostId = $('#host_id').val();
        requestHostDetails('facts-reload', {id: hostId});
    });

    $(document).off("click", "#playbook_btn").on("click", "#playbook_btn", function () {
        var hostId = $('#host_id').val();
        var command = $('#playbook_select').val();
        requestHostDetails('playbook_exec', {id: hostId, value: command});
    });

    $(document).off("click", "#syslog_btn").on("click", "#syslog_btn", function () {
        var hostId = $('#host_id').val();
        var logSize = $('#log_size').val();
        requestHostDetails('syslog-load', {id: hostId, value: logSize});
    });

    $(document).off("click", "#journald_btn").on("click", "#journald_btn", function () {
        var hostId = $('#host_id').val();
        var logSize = $('#log_size').val();
        requestHostDetails('journald-load', {id: hostId, value: logSize});
    });

    $(document).on("change", "#chkHighlight", function () {
        var hostId = $('#host_id').val();
        var value = this.checked ? 1 : 0;

        submitCommand('setHighlight', {id: hostId, value: value});
    });

    $(document).on("change", "#vm_machine", function () {
        var hostId = $('#host_id').val();
        var value = this.checked ? 1 : 0;

        submitCommand('toggleVMMachine', {id: hostId, value: value});
    });

    $(document).on("change", "#hypervisor_machine", function () {
        var hostId = $('#host_id').val();
        var value = this.checked ? 1 : 0;

        submitCommand('toggleHypervisorMachine', {id: hostId, value: value});
    });

    //Ansible host enable
    $(document).on("change", "#ansible_enabled", function () {
        var hostId = $('#host_id').val();
        var value = this.checked ? 1 : 0;

        submitCommand('setHostAnsible', {id: hostId, value: value});
    });

    $(document).on("change", "input[type='checkbox'][data-command]", function () {
        var hostId = $('#host_id').val();
        var value = this.checked ? 1 : 0;
        var command = $(this).data('command');

        submitCommand(command, {id: hostId, value: value});
    });

    $(document).on("input", "#alarm_emails", function () {
        var hostId = $('#host_id').val();

        var emailInput = $(this).val();

        // Divide por comas y limpia espacios extra
        var emailList = emailInput.split(",").map(email => email.trim()).filter(email => email);

        // Validacion correo
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        var validEmails = [];
        var invalidEmails = [];

        emailList.forEach(email => {
            if (emailRegex.test(email)) {
                validEmails.push(email);
            } else {
                invalidEmails.push(email);
            }
        });

        //retroalimentacion
        if (invalidEmails.length > 0) {
            $("#email_feedback").text("Invalid emails: " + invalidEmails.join(", "));
        } else {
            $("#email_feedback").text(""); // Limpiar retroalimentación si todo es válido
        }

        //console.log("Valid emails:", validEmails);

        if (validEmails.length > 0) {
            submitCommand("updateAlertEmailList", {id: hostId, value: validEmails});
        }
    });

});

function initializePlaybookForm() {
    // Obtener el array de playbooks directamente desde el atributo data-playbooks
    const playbooks = $('#ansible_container').data('playbooks'); // Esto ya te devuelve un objeto

    // Llenar el dropdown con los nombres de los playbooks
    playbooks.forEach(function(playbook) {
        $('#playbook_select').append('<option value="' + playbook.name + '">' + playbook.name + '</option>');
    });

    // Evento para cuando se selecciona un playbook
    $('#playbook_select').change(function() {
        const selectedPlaybook = $(this).val();
        const playbook = playbooks.find(pb => pb.name === selectedPlaybook);

        if (playbook) {
            // Mostrar la descripción
            $('#playbook_desc').text(playbook.desc);

            // Limpiar cualquier campo previo de string_vars
            $('#string_vars_container').empty();

            // Si existen string_vars, agregar los campos de texto
            if (playbook.string_vars) {
                playbook.string_vars.forEach(function(varName) {
                    $('#string_vars_container').append(`
                        <label for="${varName}">${varName}:</label>
                        <input type="text" id="${varName}" name="${varName}" placeholder="Enter ${varName}">
                    `);
                });
            }
        } else {
            // Limpiar si no hay playbook seleccionado
            $('#playbook_desc').empty();
            $('#string_vars_container').empty();
        }
    });
}

// Llamar a la función después de que el DOM esté listo
$(function() {
    initializePlaybookForm();
});

function changeHDTab(id, tabId) {
    // Ocultar todos los contenidos de las pestañas
    const tabContents = document.querySelectorAll('.host-details-tab-content');
    tabContents.forEach(tabContent => tabContent.classList.remove('active'));
    // Resaltar el botón de la pestaña seleccionada
    const tabs = document.querySelectorAll('.host-details-tabs-head');
    tabs.forEach(tab => tab.classList.remove('active'));
    // Mostrar el contenido de la pestaña seleccionada
    const selectedTabContent = document.getElementById(tabId);
    selectedTabContent.classList.add('active');
    // Resaltar el botón de la pestaña seleccionada
    const selectedTab = document.querySelector(`button[onclick="changeHDTab(${id}, '${tabId}')"]`);
    selectedTab.classList.add('active');
    if (['tab9', 'tab10'].includes(tabId)) {
        requestHostDetails('changeHDTab', { id: id, value: tabId});
    }
}

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
                /* Logs reload */
                if (
                        jsonData.command_receive === 'logs-reload' ||
                        jsonData.command_receive === 'changeHDTab' && jsonData.command_value ===  'tab9'
                ) {
                    if (jsonData.command_success === 1) {
                        $('#term_output').html(jsonData.response_msg);
                    } else {
                        $('#term_output').html('Error');
                    }
                }

                /* Syslog Journald load */
                if (
                        jsonData.command_receive === 'syslog-load' ||
                        jsonData.command_receive === 'journald-load'
                ) {
                    if (jsonData.command_success === 1) {
                        $('#term_output').html(jsonData.response_msg);
                    } else {
                        $('#term_output').html('Error');
                    }
                }

                /* Change Host Details Tab */
                if (jsonData.command_receive === 'changeHDTab' && jsonData.command_value ===  'tab10') {
                    if (jsonData.command_success === 1) {
                        $('#ping_graph_container').html(jsonData.response_msg);
                    } else {
                        $('#ping_graph_container').html('Error');
                    }
                }

                /* Facts reload */
                if (jsonData.command_receive === 'facts-reload') {
                    if (jsonData.command_success === 1) {
                        $('#raw_lines').html(JSON.stringify(jsonData.response_msg, null, 2));
                        $('#raw_lines').css({
                            "width": "600px",
                            "overflow": "auto",
                            "max-height": "200px",
                            "resize": "both"
                        });
                    } else {
                        $('#raw_lines').html(jsonData.command_error_msg);
                    }
                }
                /* Playbacks exec */
                if (jsonData.command_receive === 'playbook_exec') {
                    if (jsonData.command_success === 1) {
                        $('#raw_lines2').html(JSON.stringify(jsonData.response_msg, null, 2));
                        $('#raw_lines2').css({
                            "width": "600px",
                            "overflow": "auto",
                            "max-height": "200px",
                            "resize": "both"
                        });
                    } else {
                        $('#raw_lines2').html(jsonData.command_error_msg);
                    }
                }
            })
            .fail(function (xhr, status, error) {
                console.error('Error en la solicitud AJAX: host-details.js', status, error);
            });
}
