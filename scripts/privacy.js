/**
 * 
 *  @author diego/@/envigo.net
 *  @package 
 *  @subpackage 
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */

$(document).ready(function () {
    cambiarFondo();
});

function cambiarFondo() {
    // Ruta de la nueva imagen de fondo
    var nuevaImagen = 'url("./tpl/default/img/privacy_background.jpg"';

    $('body').css('background-image', nuevaImagen);
}