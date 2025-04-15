/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
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
        command: 'submitform',
        command_values: commandValues
    };
    $.post('submitter.php', requestData, function (response) {
        console.log('Respuesta del servidor:', response);
    });
}