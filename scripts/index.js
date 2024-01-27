/**
 * 
 *  @author diego/@/envigo.net
 *  @package 
 *  @subpackage 
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */

function changeTab(tabId) {
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
    const selectedTab = document.querySelector(`button[onclick="changeTab('${tabId}')"]`);
    selectedTab.classList.add('active');
}

$(document).ready(function () {
    $("#addBookmark").on("click", function () {
        $("#add-bookmark-container").css("display", "block");
    });
    $("#close_addbookmark").on("click", function () {
        $("#add-bookmark-container").css("display", "none");
    });
    // Dynamic
    $(document).on("click", "#close_host_details", function () {
        $("#host-details").css("display", "none");
    });
});
