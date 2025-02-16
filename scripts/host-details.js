/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

$(document).ready(function () {
    let autoReloadStates = {}; // Guardar el estado de cada botón
    let autoReloadIntervals = {}; // Guardar los intervalos de cada botón

    $(document).off("click", "button[id^='auto_reload_']").on("click", "button[id^='auto_reload_']", function () {
        const buttonId = $(this).attr('id'); // Obtener el ID del botón
        autoReloadStates[buttonId] = !autoReloadStates[buttonId]; // Alternar estado

        if (autoReloadStates[buttonId]) {
            // Activar AutoReload
            const currentText = $(this).text(); // Texto actual del botón
            const baseText = currentText.replace(/: ON|: OFF/, ''); // Eliminar ON/OFF
            $(this).text(`${baseText}: ON`);

            autoReloadIntervals[buttonId] = setInterval(() => {
                const hostId = $('#host_id').val();

                // Detenemos el intervalo si hostId ya no existe  (se cierra la ventana)
                if (!hostId || isNaN(hostId) || !Number.isInteger(Number(hostId))) {
                    // Detener AutoReload si hostId no es un entero válido
                    clearInterval(autoReloadIntervals[buttonId]);
                    delete autoReloadIntervals[buttonId];
                    autoReloadStates[buttonId] = false;
                    $(this).text(`${baseText}: OFF`);
                    return;
                }
                // Obtener las variables dinámicas
                const params = {};
                $(`input[data-btn="${buttonId}"]`).each(function () {
                    const key = $(this).attr('name'); // Nombre del parámetro
                    const value = $(this).val(); // Valor del parámetro
                    params[key] = value;
                });
                // Enviar solicitud con ID del botón como acción
                requestHostDetails(buttonId, { id: hostId, ...params });
            }, 5000); // Cada 5 segundos
        } else {
            // Desactivar AutoReload
            const currentText = $(this).text(); // Texto actual del botón
            const baseText = currentText.replace(/: ON|: OFF/, ''); // Eliminar ON/OFF
            $(this).text(`${baseText}: OFF`);

            clearInterval(autoReloadIntervals[buttonId]); // Limpiar el intervalo
        }
    });

    $('#display_ipv6').on('change', function () {
        $('.port_ipv6').css('display', this.checked ? 'inline-flex' : 'none');
    });

    // Toggle para Local Ports
    $('#display_local').on('change', function () {
        $('.port_local').css('display', this.checked ? 'inline-flex' : 'none');
    });

    $(document).off("click", "#logs_reload_btn").on("click", "#logs_reload_btn", function () {
        var hostId = $('#host_id').val();
        var logLevel = $('#log_level').val();
        var logSize = $('#log_size').val();
        requestHostDetails('logs-reload', {id: hostId, log_level: logLevel, log_size: logSize});
    });

    $(document).off("click", "#pbqueue_btn").on("click", "#pbqueue_btn", function () {
        executePlaybookAction('pbqueue');
    });

    $(document).off("click", "#pbexec_btn").on("click", "#pbexec_btn", function () {
        executePlaybookAction('playbook_exec');
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

    $(document).on("click", "#submitTitle", function () {
        var titleValue = $('#host-title').val();
        var hostId = $('#host_id').val();
        if (titleValue && hostId) {
            submitCommand('submitTitle', {id: hostId, value: titleValue});
        }
    });
    $(document).on("click", "#submitHostname", function () {
        var titleValue = $('#host-name').val();
        var hostId = $('#host_id').val();
        if (titleValue && hostId) {
            submitCommand('submitHostname', {id: hostId, value: titleValue});
        }
    });

    $(document).on("change", "#chkHighlight", function () {
        var hostId = $('#host_id').val();
        var value = this.checked ? 1 : 0;

        submitCommand('setHighlight', {id: hostId, value: value});
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
        if (hostId) {
            submitCommand(command, {id: hostId, value: value});
        } else {
            submitCommand(command, {id: 0, value: value});
        }
    });

    $(document).on("change", "#checkports_enabled", function () {
        var hostId = $('#host_id').val();
        var value = 1;

        if (this.checked) {
            value = 2;
        }
        requestHostDetails('setCheckPorts', {id: hostId, value: value});
    });

    $(document).on("click", "#submitHostPort", function () {
        var portProtocol = $('#port_protocol').val();
        var portNumber = $('#port_number').val();
        var hostId = $('#host_id').val();
        if (portNumber && portProtocol && hostId) {
            requestHostDetails('submitHostPort', {id: hostId, value: portNumber, protocol: portProtocol});
        }
    });

    $(document).on("click", ".deleteRemoteHostPort", function () {
        var portId = $('.current_remote_ports').val();

        if (portId) {
            requestHostDetails('deleteHostPort', {id: portId});
        }
    });

    $(document).on("change", ".current_agent_ports", function () {
        var selectedOption = $(this).find('option:selected');
        var customService = selectedOption.data('cservice');

        $("#custom_service_name").val(customService);
    });


    $(document).on("click", ".deleteAgentHostPort", function () {
        var portId = $('.current_agent_ports').val();

        if (portId) {
            requestHostDetails('deleteHostPort', {id: portId});
        }
    });

    $(document).on("click", ".submitCustomServiceName", function () {
        var portId = $('.current_agent_ports').val();
        var portCustomServiceName = $('#custom_service_name').val();
        if (portId && portCustomServiceName) {
            requestHostDetails('submitCustomServiceName', {id: portId, value: portCustomServiceName});
        }
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

function initTasks() {
   $('#task_trigger').change(function() {
      const selectedValue = $(this).val();
      const eventData = document.getElementById("event_data");
      const conditionalField = document.getElementById("conditional_field");

      console.log("Triggered");
      // Replace the content of the "what" container based on the selected value
      if (selectedValue == 2) {
        const events = JSON.parse(eventData.getAttribute("data-input-events"));
        conditionalField.innerHTML = "";

        const dynamicSelect = document.createElement("select");
        dynamicSelect.id = "what";
        dynamicSelect.name = "what";

        // Agregar las opciones al <select>
        for (const [key, value] of Object.entries(events)) {
          const option = document.createElement("option");
          option.value = value;
          option.textContent = key;
          dynamicSelect.appendChild(option);
        }

        conditionalField.appendChild(dynamicSelect);
      } else {
        $('#conditional_field').html('');
      }
    });
}

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
                        <input type="text" id="${varName}" size="10" name="extra_vars[${varName}]" placeholder="Enter ${varName}">
                    `);
                });
            }
            if (playbook.numeric_vars) {
                playbook.numeric_vars.forEach(function (varName) {
                    $('#vars_container').append(`
                        <label for="${varName}">${varName}:</label>
                        <input type="number" id="${varName}" size="5" name="extra_vars[${varName}]" placeholder="Enter ${varName}">
                    `);
                });
            }
            if (playbook.password_vars) {
                playbook.password_vars.forEach(function (varName) {
                    $('#vars_container').append(`
                        <div class="password-container">
                        <label for="${varName}">${varName}:</label>
                        <input type="password" id="${varName}" size="10" name="extra_vars[${varName}]"  class="password-input" placeholder="Enter ${varName}">
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
    // Seleccionar solo los div asociados con toggleSection
    const sections = document.querySelectorAll('div[id].hidden-section');
    sections.forEach(section => {
        section.classList.remove('hidden-section');
    });

    const toggleButtons = document.querySelectorAll('[onclick^="toggleSection"]');
    toggleButtons.forEach(button => {
        if (button.textContent.includes('[+] ')) {
            button.textContent = button.textContent.replace('[+] ', '[-] ');
        }
    });
}

function collapseAll() {
    // Seleccionar solo los div asociados con toggleSection
    const sections = document.querySelectorAll('div[id]');
    sections.forEach(section => {
        // Asegurarse de que sea una sección desplegable
        const button = document.querySelector(`[onclick="toggleSection('${section.id}')"]`);
        if (button && !section.classList.contains('hidden-section')) {
            section.classList.add('hidden-section');
        }
    });

    const toggleButtons = document.querySelectorAll('[onclick^="toggleSection"]');
    toggleButtons.forEach(button => {
        if (button.textContent.includes('[-] ')) {
            button.textContent = button.textContent.replace('[-] ', '[+] ');
        }
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
    if (['tab3', 'tab9', 'tab10','tab15', 'tab20'].includes(tabId)) {
        requestHostDetails('changeHDTab', {id: id, value: tabId});
    }
    if (['tab20'].includes(tabId)) {
        initializePlaybookForm();
    }
    if (['tab15'].includes(tabId)) {
        initTasks();
    }
}

function executePlaybookAction(pb_cmd) {
    const hostId = $('#host_id').val();
    const as_html = $('#as_html').prop('checked');
    const command = $('#playbook_select').val();

    const extraVars = {};

    $('#vars_container input').each(function() {
        const nameMatch = $(this).attr('name').match(/\[([^\]]+)\]/);
        if (nameMatch && nameMatch[1]) {
            const value = $(this).val();
            if (value) extraVars[nameMatch[1]] = value;
        }
    });

    const requestData = {
        id: hostId,
        value: command,
        as_html: as_html
    };

    if (Object.keys(extraVars).length > 0) {
        requestData.extra_vars = extraVars;
    }

    requestHostDetails(pb_cmd, requestData);
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

                if (jsonData.command_receive === 'auto_reload_host_details') {
                    host_details = jsonData.host_details;

                    if (host_details['disk_info'] || host_details['mem_info']) {
                        $('#bars_container').html('');
                    }
                    if (host_details['iowait_stats']) {
                        $('#iowait_container').html(host_details['iowait_stats']);
                    }
                    if (host_details['load_avg']) {
                        $('#load_container').html(host_details['load_avg']);
                    }
                    if (host_details['mem_info']) {
                        $('#bars_container').append(host_details['mem_info']);
                    }
                    if (host_details['disks_info']) {
                        $('#bars_container').append(host_details['disks_info']);
                    }
                }
                /* Logs reload */
                if (
                    jsonData.command_receive === 'logs-reload' ||
                    jsonData.command_receive === 'auto_reload_logs' ||
                    (jsonData.command_receive === 'changeHDTab' && jsonData.command_value === 'tab9')
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

                if (jsonData.command_receive === 'submitHostname') {
                    if (jsonData.command_success === 1) {
                        $('#config_status_msg').html(jsonData.response_msg);
                    } else if (jsonData.command_error_msg) {
                        $('#config_status_msg').html(jsonData.command_error_msg);
                    }
                }
                /* Change Host Details Tab */
                if (jsonData.command_receive === 'changeHDTab' && jsonData.command_value === 'tab10') {
                    if (jsonData.command_success === 1) {
                        $('#graphs_container').html(jsonData.response_msg);
                    } else {
                        $('#graphs_container').html('Error');
                    }
                }
                if (jsonData.command_receive === 'changeHDTab'  && jsonData.command_value === 'tab20') {
                    if (jsonData.command_success === 1) {
                        $('#reports-table').html(jsonData.response_msg);
                    } else {
                        $('#reports-table').html(jsonData.command_error_msg);
                    }
                }

                if (jsonData.command_receive === 'submitDeleteReport') {
                    if (jsonData.command_success === 1 && jsonData.response_id) {
                        $('#report_status_msg').html(jsonData.response_msg);
                        let rowId = `#report_row_${jsonData.response_id}`;
                        $(rowId).remove();
                    } else {
                        $('#report_status_msg').html(jsonData.command_error_msg);
                    }
                }

                if (jsonData.command_receive === 'submitViewReport') {
                    if (jsonData.command_success === 1) {
                        $('#playbook_content').css({
                            "width": "50vw",
                            "max-height": "50vh",
                            "overflow-x": "auto",
                            "overflow-y": "auto",
                            "resize": "both"
                        });
                        $('#playbook_content').html(jsonData.response_msg);

                    }
                }
                /* Playbacks exec */
                if (jsonData.command_receive === 'playbook_exec') {
                    if (jsonData.command_success === 1) {
                        if (jsonData.as_html) {
                            $('#playbook_content').html(jsonData.response_msg);
                        } else {
                            // Raw Json
                            $('#playbook_content').html(`<pre>${JSON.stringify(jsonData.response_msg, null, 2)}</pre>`);
                        }

                        $('#playbook_content').css({
                            "max-width": "80vw",
                            "max-height": "50vh",
                            "overflow-y": "auto",
                            "resize": "both"
                        });
                    } else {
                        $('#playbook_content').html(jsonData.command_error_msg);
                    }
                }
            })
            .fail(function (xhr, status, error) {
                console.error('Error en la solicitud AJAX: host-details.js', status, error);
            });
}
