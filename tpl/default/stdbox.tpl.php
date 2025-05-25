<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
/**
 * In frontend->getTpl()
 * @var array<string> $lng
 * @var array<mixed> $tdata
 */
!defined('IN_WEB') ? exit : true;
?>
<div id="stdbox-container" class="draggable" style="resize: both; overflow: auto; min-width: 250px; min-height: 120px;">
    <div class="stdbox-bar dragbar">
        <button id="close_stdcontainer" onclick="closeStdContainer()" class="button-ctrl" type="submit">
            <img class="close_link" src="./tpl/<?= $ncfg->get('theme') ?>/img/close.png" title="<?= $lng['L_CLOSE'] ?>">
        </button>
        <div id="stdbox-title"></div>
    </div>
    <div class="form_container">
        <div id="stdbox-status-msg"></div>
    </div>
    <div id="stdbox-content">

    </div>
</div>
