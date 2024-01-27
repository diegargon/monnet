/**
 * 
 *  @author diego/@/envigo.net
 *  @package 
 *  @subpackage 
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */

$(document).ready(function () {
    var urlParams = new URLSearchParams(window.location.search);
    var pageValue = urlParams.get('page');

    if (pageValue) {
        changeBG(pageValue);
    } else {
        changeBG('index');
    }
});

function changeBG(page) {
    // Ruta de la nueva imagen de fondo
    if (page === 'privacy') {
        var newBgImg = 'url("./tpl/default/img/privacy-bg.png")';
    } else if (page === 'index') {
        var newBgImg = 'url("./tpl/default/img/block-bg.jpg")';
    } else {
        return;
    }

    $('body').css({
        'background-image': newBgImg,
        'background-repeat': 'no-repeat',
        'background-position': 'center center',
        'background-attachment': 'fixed',
        'background-size': 'cover'
    });
}