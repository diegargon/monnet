/**
 * 
 *  @author diego/@/envigo.net
 *  @package 
 *  @subpackage 
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
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

function changeBookmarksTab(tabId) {
    // Ocultar todos los contenidos de las pestañas
    const tabContents = document.querySelectorAll('.bookmarks-tab-content');
    tabContents.forEach(tabContent => tabContent.classList.remove('active'));
    // Resaltar el botón de la pestaña seleccionada
    const tabs = document.querySelectorAll('.bookmarks-tabs-head');
    tabs.forEach(tab => tab.classList.remove('active'));
    // Mostrar el contenido de la pestaña seleccionada
    const selectedTabContent = document.getElementById(tabId);
    selectedTabContent.classList.add('active');
    // Resaltar el botón de la pestaña seleccionada
    const selectedTab = document.querySelector(`button[onclick="changeBookmarksTab('${tabId}')"]`);
    selectedTab.classList.add('active');
    refresh('change_bookmarks_tab', tabId);
}

$(document).ready(function () {
    // add bookmark "Popup"
    $("#addBookmark").on("click", function () {
        $("#add-bookmark-container").css("display", "block");
    });
    $("#close_addbookmark").on("click", function () {
        $("#add-bookmark-container").css("display", "none");
    });
    $("#toggleItemsSettings").on("click", function () {
        $(".delete_bookmark").toggle();
        $(".item-container .item_link").toggleClass("disabled-link");
    });
    // add network "popup"
    $("#addNetwork").on("click", function () {
        $("#add-network-container").css("display", "block");
    });
    $("#close_addnetwork").on("click", function () {
        $("#add-network-container").css("display", "none");
    });
    // Show cat

    var clicked = false;
    var timer;
    var catID;
    $(document).on("dblclick", ".show_host_cat", function (event) {
        event.preventDefault(); // Evitar la acción predeterminada del doble clic

        catID = $(this).data('catid');
        refresh('show_host_only_cat', catID);
        // Reiniciar la variable clicked
        clicked = false;
        clearTimeout(timer); // Limpiar el temporizador para evitar que se ejecute el clic normal
    });

    $(document).on("click", ".show_host_cat", function (event) {
        event.preventDefault();

        if (clicked) {
            clearTimeout(timer);
            return false;
        }
        catID = $(this).data('catid');

        clicked = true;
        timer = setTimeout(function () {
            // Reiniciar clicked después de un intervalo de tiempo
            clicked = false;
            refresh('show_host_cat', catID);
        }, 300);

    });

    $(document).on("click", "#close_host_details", function () {
        $("#host-details").css("display", "none");
    });

    $(document).on("change", "#chkHighlight", function () {
        var host_id = $('#host_id').val();

        var value = 0;
        if (this.checked) {
            value = 1;
        }
        refresh('setHighlight', value, host_id);
    });

    $(document).on("change", "#checkports_enabled", function () {
        var host_id = $('#host_id').val();
        var value = 1;

        if (this.checked) {
            value = 2;
        }
        refresh('setCheckPorts', value, host_id);
    });

    $(document).on("click", "#submitPorts", function () {
        var portsValue = $('#checkports').val();
        var host_id = $('#host_id').val();
        if (portsValue && host_id) {
            refresh('submitScanPorts', portsValue, host_id);
        }
    });
    $(document).on("click", "#submitTitle", function () {
        var titleValue = $('#host-title').val();
        var host_id = $('#host_id').val();
        if (titleValue && host_id) {
            refresh('submitTitle', titleValue, host_id);
        }
    });

    $(document).on("click", "#submitCat", function () {
        var catValue = $('#hostcat_id').val();
        var host_id = $('#host_id').val();
        if (catValue && host_id) {
            refresh('submitCat', catValue, host_id);
        }
    });

    $(document).on("click", "#submitManufacture", function () {
        var mValue = $('#manufacture').val();
        var host_id = $('#host_id').val();
        if (mValue && host_id) {
            refresh('submitManufacture', mValue, host_id);
        }
    });
    $(document).on("click", "#submitOS", function () {
        var osValue = $('#os').val();
        var host_id = $('#host_id').val();
        if (osValue && host_id) {
            refresh('submitOS', osValue, host_id);
        }
    });
    $(document).on("click", "#submitSystemType", function () {
        var stValue = $('#system_type').val();
        var host_id = $('#host_id').val();
        if (stValue && host_id) {
            refresh('submitSystemType', stValue, host_id);
        }
    });
    $(document).on("click", "#submitHostToken", function () {        
        var host_id = $('#host_id').val();
        if (host_id) {
            refresh('submitHostToken', host_id);
        }
    });    
//Checkbox trigger
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
//Network Checkboxes           
//Prevent disable all networks
            const checkedNetworks = document.querySelectorAll('input[type="checkbox"].option_network:checked');
            if ($(this).hasClass('option_network')) {
                if (checkedNetworks.length === 1) {
                    console.log("Lenght es 1");
                    checkboxes.forEach(cb => {
                        if (cb.checked) {
                            cb.disabled = true;
                        }
                    });
                } else if (checkedNetworks.length > 1) {
                    checkboxes.forEach(cb => {
                        if (cb !== this) {
                            cb.disabled = false;
                        }
                    });
                }
            }
//Send event to refresher
            if ($(this).hasClass('option_network')) {
                if (this.checked) {
                    refresh('network_select', this.value);
                } else {
                    refresh('network_unselect', this.value);
                }
            }
//END Netrwork Checkboxes
        });
    });
}
);
