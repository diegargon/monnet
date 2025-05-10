/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

function changeSettingsTab(tabId) {
    // Ocultar todos los contenidos de las pestañas
    const tabContents = document.querySelectorAll('.settings-tab-content');
    tabContents.forEach(tabContent => tabContent.classList.remove('active'));
    // Resaltar el botón de la pestaña seleccionada
    const tabs = document.querySelectorAll('.settings-tabs-head');
    tabs.forEach(tab => tab.classList.remove('active'));
    // Mostrar el contenido de la pestaña seleccionada
    const selectedTabContent = document.getElementById(`settings_content_tab_${tabId}`);
    selectedTabContent.classList.add('active');
    // Resaltar el botón de la pestaña seleccionada
    const selectedTab = document.querySelector(`button[onclick="changeSettingsTab(${tabId})"]`);
    selectedTab.classList.add('active');
    //submitCommand('change_settings_tab', {id: tabId});
}

function sendFormData(button) {
    event.preventDefault();
    // Encontrar el formulario padre del botón que disparó el evento
    let form = $(button).closest('form');

    // Obtener los datos del formulario como un array de objetos
    let formData = form.serializeArray();

    let commandValues = {};
    formData.forEach(function (item) {
        commandValues[item.name] = item.value;
    });
    commandValues.id = 0;

    var requestData = {
        command: 'submitConfigform',
        command_values: commandValues
    };
    $.post('submitter.php', requestData, function (response, textStatus, xhr) {
        console.log(response);
        var jsonData;
        var contentType = xhr.getResponseHeader('Content-Type');

        if (contentType && contentType.toLowerCase().includes('application/json')) {
            jsonData = (typeof response === 'object') ? response : JSON.parse(response);
        } else {
            console.warn("Tipo de contenido inesperado:", contentType);
            return;
        }

        if (jsonData.login === "fail") {
            location.href = '';
        }

        if (jsonData.command_error) {
            $('#status_msg').html(jsonData.error_msg);
            return;
        }

        if (jsonData.command_success && jsonData.response_msg) {
            success_msg = 'Changed ' + jsonData.response_msg + ' values';
            $('#status_msg').html(success_msg);
        }
    });
}
