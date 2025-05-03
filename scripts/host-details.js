/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

$(document).ready(function () {
    let autoReloadStates = {}; // Button state
    let autoReloadIntervals = {}; // Button Intevals

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

    // Toggle visibility ipv6 ports
    $(document).on("change", "#display_ipv6", function () {
        $('.port_ipv6').css('display', this.checked ? 'inline-flex' : 'none');
    });

    // Toggle visibility local ports
    $(document).on("change", "#display_local", function () {
        $('.port_local').css('display', this.checked ? 'inline-flex' : 'none');
    });

    $(document).off("click", "#logs_reload_btn").on("click", "#logs_reload_btn", function () {
        var hostId = $('#host_id').val();
        var logLevel = $('#log_level').val();
        var logSize = $('#log_size').val();
        requestHostDetails('logs-reload', {id: hostId, log_level: logLevel, log_size: logSize});
    });

    $(document).on("change", "#log_level", function () {
        $("#logs_reload_btn").trigger("click");
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
            $("#email_feedback").text(""); // Clean
        }

        //console.log("Valid emails:", validEmails);

        if (validEmails.length > 0) {
            submitCommand("updateAlertEmailList", {id: hostId, value: validEmails});
        }
    });

    $(document).on("click", "#addvar_btn", function() {
        var container = $(this).closest(".ansible_vars");

        var data = {
            host_id: container.find("input[type=hidden]").data("hid"),
            var_type: container.find("#ans_var_type").val(),
            var_name: container.find("input[data-name='ans_var_name']").val(),
            var_value: container.find("input[data-name='ans_var_value']").val()
        };

        if (!data.var_name || !data.var_value) {
            alert("Both 'Var name' and 'Var value' are required.");
            return; // Detener ejecución
        }

        requestHostDetails('add_ansible_var', data);
    });

    $(document).on("click", "#delete_var_btn", function() {

        let selectedOption = $('#ans_var_list option:selected');
        let selectedValue = selectedOption.val();
        let data = {
            id: selectedValue
        };
        if (selectedValue) {
            requestHostDetails('del_ansible_var', data);
        }
    });

    $(document).on("click", "#del_var_btn", function() {
        var container = $(this).closest(".ansible_vars");

        var data = {
            command: 'del_ansible_var',
            id: container.find("input[type=hidden]").data("hid"),
            var_type: container.find("#ans_var_type").val()
        };

        requestHostDetails('del_ansible_var', data);
    });

    $(document).on('click', '#tab15 button[type="submit"]', function (e) {
        e.preventDefault();

        let $row = $(this).closest('tr');
        let taskId = $row.data('id');

        if (taskId === undefined || taskId === null) {
            console.error('Error:  Task id not found');
            return;
        }
        let hid = $row.find('[name^="hid"]').val();
        let taskName = $row.find('[name^="task_name"]').val();
        let taskTrigger = $row.find('[name^="task_trigger"]').val();
        let playbook = $row.find('[name^="playbooks"]').val();
        let disableTask = $row.find('[name^="disable_task"]').is(':checked');
        let nextTask = $row.find('[name^="next_task"]').val();
        let conditional = $row.find('[name^="conditional"]').val();
        let groups = $row.find('[name^="ansible_groups"]').val();

        let action = $(this).data('action');

        let requestData = {
            id: taskId,
            hid: hid,
            task_name: taskName,
            task_trigger: taskTrigger,
            playbook: playbook,
            disable_task: disableTask,
            next_task: nextTask,
            conditional: conditional,
            groups: groups
        };

        /*
        console.log(`Action: ${action}`);
        console.log(`Task ID: ${taskId}`);
        console.log(`Task Name: ${taskName}`);
        console.log(`Task Trigger: ${taskTrigger}`);
        console.log(`Playbook: ${playbook}`);
        console.log(`Disable Task: ${disableTask}`);
        console.log(`Next Task: ${nextTask}`);
        */

        switch (action) {
            case 'create_host_task':
                requestHostDetails('create_host_task', requestData);
                break;
            case 'delete_host_task':
                requestHostDetails('delete_host_task', { id: taskId });
                break;
            case 'update_host_task':
                requestHostDetails('update_host_task', requestData);
                break;
            case 'force_exec_task':
                requestHostDetails('force_exec_task', { id: taskId });
                break;
            default:
                console.error('Acción desconocida:', action);
        }
    });
 });

function initTasks() {
    $(document).on('change', '[name^="task_trigger["], #task_trigger', function() {
        console.log('Cambio detectado en', this.name || this.id);

        const selectedValue = Number($(this).val());
        const row = $(this).closest('tr'); // Obtiene la fila actual
        const eventData = document.getElementById("event_data");
        const conditionalField = row.find('[id^="conditional_field"]')[0];

        console.log("Selected Triggered");

        // Lógica para el trigger con valor 2 o 3
        if (selectedValue === 3) {
            const events = JSON.parse(eventData.getAttribute("data-input-events"));
            conditionalField.innerHTML = "";
            const dynamicSelect = document.createElement("select");
            dynamicSelect.name = `conditional[${row.data('id')}]`; // Mantiene el ID de la tarea

            for (const [key, value] of Object.entries(events)) {
                const option = document.createElement("option");
                option.value = value;
                option.textContent = key;
                dynamicSelect.appendChild(option);
            }
            conditionalField.appendChild(dynamicSelect);
        } else if (selectedValue === 4) {
            const inputText = document.createElement("input");
            conditionalField.innerHTML = "";
            inputText.type = "text";
            inputText.size = 15;
            inputText.name = `conditional[${row.data('id')}]`;
            inputText.placeholder = "Cron time * * * * *";
            conditionalField.appendChild(inputText);
        } else if (selectedValue === 5) {
            const inputText = document.createElement("input");
            conditionalField.innerHTML = "";
            inputText.type = "text";
            inputText.size = 5;
            inputText.name = `conditional[${row.data('id')}]`;
            inputText.placeholder = "Interval 5m/1h/1w/1mo/1y";
            conditionalField.appendChild(inputText);
        } else {
            conditionalField.innerHTML = "";
        }
    });

}

function initializePlaybookSelect(playbooksMap) {
    $('#playbook_select').change(function () {
        const selectedPlaybookId = $(this).val();
        const playbook = playbooksMap[selectedPlaybookId];

        if (playbook) {
            $('#playbook_desc').text(playbook.description || '');
            $('#vars_container').empty();

            if (playbook.vars && playbook.vars.length > 0) {
                playbook.vars.forEach(function (variable) {
                    // Tooltip cration
                    let tooltipText = variable.description || variable.name;

                    if (variable.default !== undefined) {
                        tooltipText += `\nDefault: ${variable.default}`;
                    }

                    // Element creation
                    const varElement = $('<span>', {
                        class: 'variable-tag' + (variable.required ? ' required' : ''),
                        text: variable.name,
                        title: tooltipText,
                        css: {
                            'cursor': 'help'
                        }
                    });

                    $('#vars_container').append(varElement);
                });
            }
        } else {
            // Limpiar si no hay playbook seleccionado
            $('#playbook_desc').empty();
            $('#vars_container').empty();
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
        requestData.extra_vars = JSON.stringify(extraVars);
    }

    requestHostDetails(pb_cmd, requestData);
}

function filterPlaybooks() {
    const selectedTags = $('#tags_filter input:checked').map(function() {
        return $(this).data('tag');
    }).get();

    console.log('Tags seleccionados:', selectedTags);

    let visibleCount = 0;
    $('#playbook_select option').each(function() {
        if($(this).val() === '') return;

        // Show own if none
        if(selectedTags.length === 0) {
            $(this).show();
            visibleCount++;
            return;
        }

        // Parse tags
        let tags = [];
        try {
            const tagsData = $(this).attr('data-tags');
            tags = tagsData ? JSON.parse(tagsData) : [];
        } catch(e) {
            console.error("Error parsing tags for:", $(this).val(), "Error:", e);
            tags = [];
        }

        //console.log(`Playbook ${$(this).val()} tiene tags:`, tags);

        // Show selected
        const shouldShow = tags.some(tag => selectedTags.includes(tag));
        $(this).toggle(shouldShow);
        if(shouldShow) visibleCount++;
    });

    $('#playbook_count').text(visibleCount);
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

                    if (host_details['iowait_stats']) {
                        $('#iowait_container').html(host_details['iowait_stats']);
                    }
                    if (host_details['load_avg']) {
                        $('#load_container').html(host_details['load_avg']);
                    }
                    if (host_details['mem_info'] || host_details['disks_info']) {
                        $('#bars_container').html('');
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
                    (jsonData.command_receive === 'changeHDTab' && jsonData.command_value === 'logs-reload')
                    ) {
                    if (jsonData.command_success === 1) {
                        $('#term_output').html(jsonData.response_msg);
                    } else {
                        $('#term_output').html('Error');
                    }
                }

                /* Tex Notes */
                if (
                        jsonData.command_receive === 'load_notes' ||
                        (jsonData.command_receive === 'changeHDTab' && jsonData.command_value === 'load_notes')
                ) {
                    $('#textnotes').html(jsonData.response_msg);
                }
                /* Syslog Journald load */
                if (
                    jsonData.command_receive === 'syslog-load' ||
                    jsonData.command_receive === 'journald-load'
                    ) {
                    if (jsonData.command_success === 1) {
                        $('#term_output').html(jsonData.response_msg);
                    } else if (jsonData.command_error_msg) {
                        $('#term_output').html(jsonData.command_error_msg);
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
                        if (jsonData.response_msg.reports_list) {
                            $('#reports-table').html(jsonData.response_msg.reports_list);
                        }
                        if (jsonData.response_msg.ansible_vars) {
                            //console.log("Ansible Vars:", jsonData.response_msg.ansible_vars);
                            let optionsHtml = jsonData.response_msg.ansible_vars.map(option => {
                                return option.vtype === 1
                                    ? `<option value="${option.id}">${option.vkey}: ****</option>`
                                    : `<option value="${option.id}">${option.vkey}: ${option.vvalue}</option>`;
                            }).join('');
                            $('#ans_var_list').html(optionsHtml);
                        }
                        if (jsonData.response_msg.playbooks_metadata) {
                            const $playbookSelect = $('#playbook_select').empty();
                            const playbooksMap = {};

                            // Default
                            $playbookSelect.append('<option value="" selected>Selecciona un playbook</option>');

                            // Procesar playbooks
                            jsonData.response_msg.playbooks_metadata
                                .sort((a, b) => a.id === 'std-install-monnet-agent-systemd' ? -1 : b.id === 'std-install-monnet-agent-systemd' ? 1 : 0)
                                .forEach(playbook => {
                                    playbooksMap[playbook.id] = playbook;

                                    $playbookSelect.append(
                                        $('<option>', {
                                            value: playbook.id,
                                            text: playbook.name,
                                            'data-tags': JSON.stringify(Array.isArray(playbook.tags) ? playbook.tags : []),
                                            'data-description': playbook.description || ''
                                        })
                                    );
                                });
                            console.log(playbooksMap);
                            // Tags
                            const allTags = [...new Set(
                                jsonData.response_msg.playbooks_metadata.flatMap(p => Array.isArray(p.tags) ? p.tags : [])
                            )];

                            const $tagsContainer = $('#tags_filter');
                            $tagsContainer.html('');
                            allTags.forEach(tag => {
                                $tagsContainer.append(`
                                    <label class="checkbox-inline">
                                        <input type="checkbox" data-tag="${tag}"> <span>${tag}<span>
                                    </label>
                                `);
                            });
                            // Event Filter
                            $('#tags_filter').on('change', 'input[type="checkbox"]', filterPlaybooks);

                            // Filter
                            filterPlaybooks();
                            initializePlaybookSelect(playbooksMap);
                        }
                    } else {
                        $('#reports-table').html(jsonData.command_error_msg);
                    }
                }
                if (jsonData.command_receive === 'changeHDTab'  && jsonData.command_value === 'tab15') {
                    if (jsonData.command_success === 1) {
                        $('#tasks-list').html(jsonData.response_msg.tasks_list);
                        $('#playbooks').html(jsonData.response_msg.pb_sel);
                    } else {
                        $('#tasks-list').html(jsonData.command_error_msg);
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
                /* Tasks */
                if (
                    ['delete_host_task', 'create_host_task',
                     'update__host_task', 'force_exec_task']
                    .includes(jsonData.command)
                ) {
                    if(jsonData.response_msg) {
                        $('#tasks_status_msg').html(jsonData.response_msg);
                    }
                    if(jsonData.command_error_msg) {
                        $('#tasks_status_msg').html(jsonData.command_error_msg);
                    }
                }
                /* Delete port */
                if (jsonData.command_receive === 'deleteHostPort') {
                    $('.status_msg').html(jsonData.response_msg);

                    if (jsonData.command_success === 1 && jsonData.port_id) {
                        $('.current_agent_ports option[value="' + jsonData.port_id + '"]').remove();
                    }
                }
            })
            .fail(function (xhr, status, error) {
                console.error('Error en la solicitud AJAX: host-details.js', status, error);
            });
}
