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

    $(document).off("click", "#playbook_btn").on("click", "#playbook_btn", function () {
        var hostId = $('#host_id').val();
        var as_html = $('#as_html').prop('checked');

        var command = $('#playbook_select').val();

        var extraVars = {};

        $('#vars_container input').each(function () {
            var name = $(this).attr('name').match(/\[([^\]]+)\]/)[1]; // Extraer el nombre dentro de los corchetes
            var value = $(this).val();
            if (value) { // Solo añadir si hay un valor
                extraVars[name] = value;
            }
        });
        // Preparar el objeto de datos
        var requestData = {
            id: hostId,
            value: command,
            as_html: as_html
        };

        // Añadir extra_vars solo si tiene valores
        if (Object.keys(extraVars).length > 0) {
            requestData.extra_vars = extraVars;
        }

        // Llamar a requestHostDetails
        requestHostDetails('playbook_exec', requestData);
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
    const playbooks = $('#ansible_container').data('playbooks'); //devuelve un objeto

    // Llenar el dropdown con los nombres de los playbooks
    playbooks.forEach(function (playbook) {
        $('#playbook_select').append('<option value="' + playbook.name + '">' + playbook.name + '</option>');
    });

    // Evento para cuando se selecciona un playbook
    $('#playbook_select').change(function () {
        const selectedPlaybook = $(this).val();
        const playbook = playbooks.find(pb => pb.name === selectedPlaybook);

        if (playbook) {
            $('#playbook_desc').text(playbook.desc);
            $('#vars_container').empty();

            // Si existen string_vars, agregar los campos de texto
            if (playbook.string_vars) {
                playbook.string_vars.forEach(function (varName) {
                    $('#vars_container').append(`
                        <label for="${varName}">${varName}:</label>
                        <input type="text" id="${varName}" name="extra_vars[${varName}]" placeholder="Enter ${varName}">
                    `);
                });
            }
            if (playbook.password_vars) {
                playbook.password_vars.forEach(function (varName) {
                    $('#vars_container').append(`
                        <div class="password-container">
                        <label for="${varName}">${varName}:</label>
                        <input type="password" id="${varName}" name="extra_vars[${varName}]"  class="password-input" placeholder="Enter ${varName}">
                        <span class="toggle-password">👁️</span>
                        </div>
                    `);
                });

                $('#vars_container').off("click", ".toggle-password").on("click", ".toggle-password", function () {
                    const passwordInput = $(this).prev(".password-input");
                    const inputType = passwordInput.attr("type");
                    passwordInput.attr("type", inputType === "password" ? "text" : "password");
                });
            }
        } else {
            // Limpiar si no hay playbook seleccionado
            $('#playbook_desc').empty();
            $('vars_container').empty();
        }
    });
}

function toggleSection(id) {
    const section = document.getElementById(id);
    const toggleButton = document.querySelector(`[onclick="toggleSection('${id}')"]`);

    // Cambiar la visibilidad de la sección
    section.classList.toggle('hidden-section');

    // Cambiar el texto del botón [+] / [-]
    if (section.classList.contains('hidden-section')) {
        toggleButton.textContent = toggleButton.textContent.replace('[-] ', '[+] ');
    } else {
        toggleButton.textContent = toggleButton.textContent.replace('[+] ', '[-] ');
    }
}

function expandAll() {
    const sections = document.querySelectorAll('.hidden-section');
    sections.forEach(section => {
        section.classList.remove('hidden-section');
    });

    const toggleButtons = document.querySelectorAll('[onclick^="toggleSection"]');
    toggleButtons.forEach(button => {
        button.textContent = button.textContent.replace('[+] ', '[-] ');
    });
}

function collapseAll() {
    // Seleccionar solo las secciones desplegables con la clase "hidden-section" en su id asociado
    const sections = document.querySelectorAll('div[id]');
    sections.forEach(section => {
        if (!section.classList.contains('hidden-section') && section.querySelector('ul')) {
            section.classList.add('hidden-section');
        }
    });

    const toggleButtons = document.querySelectorAll('[onclick^="toggleSection"]');
    toggleButtons.forEach(button => {
        button.textContent = button.textContent.replace('[-] ', '[+] ');
    });
}

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
        requestHostDetails('changeHDTab', {id: id, value: tabId});
    }
    if (['tab20'].includes(tabId)) {
        initializePlaybookForm();
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
                        jsonData.command_receive === 'changeHDTab' && jsonData.command_value === 'tab9'
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
                if (jsonData.command_receive === 'changeHDTab' && jsonData.command_value === 'tab10') {
                    if (jsonData.command_success === 1) {
                        $('#ping_graph_container').html(jsonData.response_msg);
                    } else {
                        $('#ping_graph_container').html('Error');
                    }
                }

                /* Playbacks exec */
                if (jsonData.command_receive === 'playbook_exec') {
                    if (jsonData.command_success === 1) {
                        if (jsonData.as_html) {
                            $('#html_lines').html(jsonData.response_msg);
                            $('#html_lines').css({
                                "overflow": "auto",
                                "resize": "both",
                                "height": "200px",
                            });
                        } else {
                            $('#raw_lines').html(JSON.stringify(jsonData.response_msg, null, 2));
                            //$('#raw_lines').html(jsonData.response_msg);
                            $('#raw_lines').css({
                                "width": "600px",
                                "overflow": "auto",
                                "height": "200px",
                                "resize": "both"
                            });
                        }

                    } else {
                        $('#raw_lines').html(jsonData.command_error_msg);
                    }
                }
            })
            .fail(function (xhr, status, error) {
                console.error('Error en la solicitud AJAX: host-details.js', status, error);
            });
}
