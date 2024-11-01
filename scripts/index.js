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

function closeStdContainer() {
    var std_container = document.getElementById("stdbox-container");
    var std_content = document.getElementById("stdbox-content");
    var std_title = document.getElementById("stdbox-title");
    var std_error_msg = document.getElementById("stdbox-error-msg");
    var std_status_msg = document.getElementById("stdbox-status-msg");

    if (std_container) {
        std_container.style.display = "none";
        if (std_content) {
            $(std_content).empty();
        }
        if (std_title) {
            $(std_title).empty();
        }
        if (std_error_msg) {
            $(std_error_msg).empty();
        }
        if (std_status_msg) {
            $(std_status_msg).empty();
        }
    } else {
        console.error("Stdbox not found.");
    }
}

function addHostsCat(title) {
    var element = document.getElementById("stdbox-container");
    var title_element = document.getElementById("stdbox-title");
    var std_content = document.getElementById("stdbox-content");
    if (element) {
        element.style.display = "block";
        title_element.innerHTML = title;
        std_content.innerHTML = '<input id="hostsCat" type="text"/><button id="submitHostsCat" type="submit">+</button>';
    } else {
        console.error("Stdbox not found.");
    }
}

function addBookmarkCat(title) {
    var element = document.getElementById("stdbox-container");
    var title_element = document.getElementById("stdbox-title");
    var std_content = document.getElementById("stdbox-content");
    if (element) {
        element.style.display = "block";
        title_element.innerHTML = title;
        std_content.innerHTML = '<input id="bookmarkCat" type="text"/><button id="submitBookmarkCat" type="submit">+</button>';
    } else {
        console.error("Stdbox not found.");
    }
}

$(document).ready(function () {
    // add bookmark "Popup"
    $("#addBookmark").on("click", function () {
        $("#add-bookmark-container").css("display", "block");
    });
    $("#close_addbookmark").on("click", function () {
        $("#add-bookmark-container").css("display", "none");
    });

    $("#addHostBox").on("click", function () {
        var title = $(this).data("title");
        $("#stdbox-container").css("display", "block");
        $("#stdbox-title").html(title);
        $("#stdbox-content").html('<input id="addedHost" type="text"  value=""/><button id="submitHost" type="submit">+</button>');
    });

    $("#toggleItemsSettings").on("click", function () {
        $(".item-container .item_link").toggleClass("disabled-link");
        $(".categories_container").toggleClass("disabled-link");
        $(".delete_bookmark").toggle();
        $(".delete_cat_btn").toggle();
        $(".add_cat_btn").toggle();

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

    $(document).on("click", "#submitHostsCat", function () {
        var value = $('#hostsCat').val();
        if (value) {
            refresh('submitHostsCat', value);
        }
    });

    $(document).on("click", "#submitBookmarkCat", function () {
        var value = $('#bookmarkCat').val();
        if (value) {
            refresh('submitBookmarkCat', value);
        }
    });

    $(document).on("click", "#submitHost", function () {
        var value = $('#addedHost').val();
        if (value) {
            refresh('submitHost', value);
        }
    });
    $(document).on("click", "#submitOwner", function () {
        var ownerValue = $('#host_owner').val();
        var host_id = $('#host_id').val();
        if (ownerValue && host_id) {
            refresh('submitOwner', ownerValue, host_id);
        }
    });
    $(document).on("click", "#submitHostTimeout", function () {
        var timeoutValue = $('#host_timeout').val();
        var host_id = $('#host_id').val();
        if (timeoutValue && host_id) {
            refresh('submitHostTimeout', timeoutValue, host_id);
        }
    });
    $(document).on("click", "#submitNetwork", function () {
        var fields = {};

        fields.networkName = $('#networkName').val();
        fields.network = $('#network').val();
        fields.networkCIDR = parseInt($('#network_cidr').val());
        fields.networkScan = parseInt($('input[name="networkScan"]:checked').val() || 0);
        fields.networkVLAN = parseInt($('#network_vlan').val());
        if (fields.networkName !== "" && fields.network !== "" && fields.networkCIDR !== "" && fields.networkVLAN !== "") {
            json_fields = JSON.stringify(fields);
            refresh('addNetwork', json_fields);
        }

    });
    $(document).on("click", "#submitBookmark", function () {
        var fields = {};
        $('#error_msg').html('');
        $('#status_msg').html('');
        fields.name = $('#bookmarkName').val();
        fields.cat_id = parseInt($('#cat_id').val());
        fields.urlip = $('#urlip').val();
        fields.image_type = $('#image_type').val();
        fields.field_img = $('#field_img').val();
        fields.weight = $('#weight').val();

        if (fields.bookmarkName !== "" && fields.cat_id !== "" && fields.urlip !== "" && fields.image_type !== "") {
            json_fields = JSON.stringify(fields);
            refresh('addBookmark', json_fields);
        } else {
            $('#error_msg').html('Empty field');
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
