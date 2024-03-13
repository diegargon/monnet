/**
 * 
 *  @author diego/@/envigo.net
 *  @package 
 *  @subpackage 
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */

window.onload = function () {
    document.getElementById("loading_wrap").style.display = "none";
};
function show_loading() {
    document.getElementById("loading_wrap").style.display = "block";
};

function makeDraggable(element) {
    var isDragging = false;
    var offsetX, offsetY;

    $(element).find(".dragbar").on("mousedown", function (e) {
        e.preventDefault();
        isDragging = true;
        offsetX = e.clientX - $(element).offset().left;
        offsetY = e.clientY - $(element).offset().top;
        element.css("z-index", zIndexCounter++);

    });

    $(document).on("mousemove", function (e) {
        if (isDragging) {
            var newLeft = e.clientX - offsetX;
            var newTop = e.clientY - offsetY;
            $(element).css({left: newLeft + "px", top: newTop + "px"});
        }
    });

    $(document).on("mouseup", function () {
        isDragging = false;
    });
}

var zIndexCounter = 1;

$(document).ready(function () {
    $(".draggable").each(function () {
        makeDraggable($(this));
    });
});