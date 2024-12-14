/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */

function changeBookmarksTab(tabId) {
    // Ocultar todos los contenidos de las pestañas
    const tabContents = document.querySelectorAll('.bookmarks-tab-content');
    tabContents.forEach(tabContent => tabContent.classList.remove('active'));
    // Resaltar el botón de la pestaña seleccionada
    const tabs = document.querySelectorAll('.bookmarks-tabs-head');
    tabs.forEach(tab => tab.classList.remove('active'));
    // Mostrar el contenido de la pestaña seleccionada
    const selectedTabContent = document.getElementById(`bookmark_content_tab_${tabId}`);
    selectedTabContent.classList.add('active');
    // Resaltar el botón de la pestaña seleccionada
    const selectedTab = document.querySelector(`button[onclick="changeBookmarksTab(${tabId})"]`);
    selectedTab.classList.add('active');
    submitCommand('change_bookmarks_tab', {id: tabId});
}

function closeStdContainer() {
    var std_container = document.getElementById("stdbox-container");
    var std_content = document.getElementById("stdbox-content");
    var std_title = document.getElementById("stdbox-title");
    var std_status_msg = document.getElementById("stdbox-status-msg");

    if (std_container) {
        std_container.style.display = "none";
        if (std_content) {
            $(std_content).empty();
        }
        if (std_title) {
            $(std_title).empty();
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

function updateThumbnail(select) {
    const selectedOption = select.options[select.selectedIndex];
    const thumbnailSrc = selectedOption.getAttribute('data-thumbnail');
    const thumbnailImg = document.getElementById('thumbnail');
    const imageName = document.getElementById('imageName');
    const field_img = document.getElementById("field_img");
    const image_type = document.getElementById("image_type");

    if (thumbnailSrc) {
        thumbnailImg.src = thumbnailSrc;
        thumbnailImg.style.display = 'inline';
        imageName.textContent = selectedOption.text;
        field_img.value = selectedOption.value;
        image_type.selectedIndex = 0;
    } else {
        thumbnailImg.style.display = 'none';
        imageName.textContent = '';
    }
}

$(document).ready(function () {
    $("#hosts_footer_dropdown_btn").on("click", function () {
        const dropdown = $("#footer_hosts_dropdown");
        if (dropdown.css("display") === "none") {
            dropdown.css("display", "inline-flex");
        } else {
            dropdown.css("display", "none");
        }
    });

    $("#close_mgmtbookmark").on("click", function () {
        $("#mgmt-bookmark-container").css("display", "none");
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
        $(".edit_bookmark").toggle();
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
        submitCommand('show_host_only_cat', {id: catID});
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
            submitCommand('show_host_cat', {id: catID});
        }, 300);

    });

    $(document).on("click", "#close_host_details", function () {
        $("#host-details").css("display", "none");
    });
    $(document).on("click", "#close_mgmtbookmark", function () {
        $("#mgmt-bookmark-container").css("display", "none");
    });
    $(document).on("change", "#disable_ping", function () {
        var hostId = $('#host_id').val();

        if (this.checked) {
            value = 1;
        } else {
            value = 0;
        }
        submitCommand('toggleDisablePing', {id: hostId, value: value});
    });

    $(document).on("change", "#checkports_enabled", function () {
        var hostId = $('#host_id').val();
        var value = 1;

        if (this.checked) {
            value = 2;
        }
        submitCommand('setCheckPorts', {id: hostId, value: value});
    });

    $(document).on("click", "#submitPorts", function () {
        var portsValue = $('#checkports').val();
        var hostId = $('#host_id').val();
        if (portsValue && hostId) {
            submitCommand('submitScanPorts', {id: hostId, value: portsValue});
        }
    });
    $(document).on("click", "#submitTitle", function () {
        var titleValue = $('#host-title').val();
        var hostId = $('#host_id').val();
        if (titleValue && hostId) {
            submitCommand('submitTitle', {id: hostId, value: titleValue});
        }
    });

    $(document).on("click", "#submitCat", function () {
        var catValue = $('#hostcat_id').val();
        var hostId = $('#host_id').val();
        if (catValue && hostId) {
            submitCommand('submitCat', {id: hostId, value: catValue});
        }
    });

    $(document).on("click", "#submitManufacture", function () {
        var manuValue = $('#manufacture').val();
        var hostId = $('#host_id').val();
        if (manuValue && hostId) {
            submitCommand('submitManufacture', {id: hostId, value: manuValue});
        }
    });
    $(document).on("click", "#submitOS", function () {
        var osValue = $('#os').val();
        var hostId = $('#host_id').val();
        if (osValue && hostId) {
            submitCommand('submitOS', {id: hostId, value: osValue});
        }
    });
    $(document).on("click", "#submitSystemType", function () {
        var stValue = $('#system_type').val();
        var hostId = $('#host_id').val();
        if (stValue && hostId) {
            submitCommand('submitSystemType', {id: hostId, value: stValue});
        }
    });
    $(document).on("click", "#submitHostToken", function () {
        var hostId = $('#host_id').val();
        if (hostId) {
            submitCommand('submitHostToken', {id: hostId});
        }
    });

    $(document).on("click", "#submitHostsCat", function () {
        var catValue = $('#hostsCat').val();
        if (catValue) {
            submitCommand('submitHostsCat', {id: 0, value: catValue});
        }
    });

    $(document).on("click", "#submitBookmarkCat", function () {
        var bookValue = $('#bookmarkCat').val();
        if (bookValue) {
            submitCommand('submitBookmarkCat', {id: 0, value: bookValue});
        }
    });

    $(document).on("click", "#submitHost", function () {
        var hostValue = $('#addedHost').val();
        if (hostValue) {
            submitCommand('submitHost', {id: 0, value: hostValue});
        }
    });
    $(document).on("click", "#submitOwner", function () {
        var ownerValue = $('#host_owner').val();
        var hostId = $('#host_id').val();
        if (ownerValue && hostId) {
            submitCommand('submitOwner', {id: hostId, value: ownerValue});
        }
    });
    $(document).on("click", "#submitAccessLink", function () {
        var accessLinkValue = $('#access_link').val();
        var hostId = $('#host_id').val();
        if (hostId) {
            submitCommand('submitAccessLink', {id: hostId, value: accessLinkValue});
        }
    });
    $(document).on("click", "#submitHostTimeout", function () {
        var timeoutValue = $('#host_timeout').val();
        var hostId = $('#host_id').val();
        if (timeoutValue && hostId) {
            submitCommand('submitHostTimeout', {id: hostId, value: timeoutValue});
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
            submitCommand('addNetwork', {id: 0, value: json_fields});
        }

    });
    $(document).on("click", "#addBookmark", function () {
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
            submitCommand('addBookmark', {id: 0, value: json_fields});
        } else {
            $('#error_msg').html('Mandatory fields empty');
        }

    });

    $(document).on("click", "#updateBookmark", function () {
        var fields = {};
        var id = null;

        $('#error_msg').html('');
        $('#status_msg').html('');
        fields.bookmark_id = $('#bookmark_id').val();
        fields.name = $('#bookmarkName').val();
        fields.cat_id = parseInt($('#cat_id').val());
        fields.urlip = $('#urlip').val();
        fields.image_type = $('#image_type').val();
        fields.field_img = $('#field_img').val();
        fields.weight = $('#weight').val();

        id = $('#bookmark_id').val();
        if (!id) {
            $('#error_msg').html('Empty Id field');
        } else if (fields.bookmarkName !== "" && fields.cat_id !== "" && fields.urlip !== "" && fields.image_type !== "") {
            json_fields = JSON.stringify(fields);
            submitCommand('updateBookmark', {id: id, value: json_fields});
        } else {
            $('#error_msg').html('Mandatory fields empty');
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
                    submitCommand('network_select', {id: 0, value: this.value});
                } else {
                    submitCommand('network_unselect', {id: 0, value: this.value});
                }
            }
//END Netrwork Checkboxes
        });
    });

    // Resize code
    $(document).ready(function () {
        const $left = $('#left-container');
        const $center = $('#center-container');
        const $hosts_cat = $('#hosts_cat');

        let startX, startWidthLeft, startWidthCenter;

        function resizeStart(e) {
            const touch = e.originalEvent.touches ? e.originalEvent.touches[0] : e;

            startX = touch.pageX;
            startWidthLeft = $left.width();
            startWidthCenter = $center.width();

            $(document).on('mousemove touchmove', resize);
            $(document).on('mouseup touchend', stopResize);
        }

        function resize(e) {
            const touch = e.originalEvent.touches ? e.originalEvent.touches[0] : e;
            const dx = touch.pageX - startX;
            const newLeftWidth = Math.max(100, startWidthLeft + dx);
            const newCenterWidth = Math.max(200, startWidthCenter - dx);

            if (newLeftWidth + newCenterWidth <= $(window).width()) {
                $left.css('width', `${newLeftWidth}px`);
                $center.css('width', `${newCenterWidth}px`);
            }
        }

        function stopResize() {
            $(document).off('mousemove touchmove', resize);
            $(document).off('mouseup touchend', stopResize);
        }

        $hosts_cat.on('mousedown touchstart', resizeStart);
    });

    // END Resize code
}
);
