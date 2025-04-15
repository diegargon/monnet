<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
/**
 * @var Config $ncfg
 * @var array<string> $lng Language data
 * @var array<mixed> $tdata Template Data
 */
!defined('IN_WEB') ? exit : true;
?>
<div class="options-container">
    <!-- Visibility -->
    <fieldset class="ctrl_fieldset">
        <legend class="ctrl_legend"><?= $lng['L_VISIBILITY'] ?></legend>
        <form method="POST" name="visibility_form">
            <div class="hosts_highlight_ctrl">
                <input type="hidden" name="show_highlight_hosts" value="0" />
                <input class="check" type="checkbox" value="1"  name="show_highlight_hosts"
                <?= $tdata['controls']['show_highlight_hosts_status'] ? ' checked ' : null ?>
                       onchange="this.form.submit()" />
                <span class="opt_labels"><?= $lng['L_HIGHLIGHT_HOSTS']; ?></span>
            </div>
            <div class="hosts_ctrl">
                <input type="hidden" name="show_rest_hosts" value="0" />
                <input class="check" type="checkbox" value="1" name="show_rest_hosts"
                <?= $tdata['controls']['show_other_hosts_status'] ? ' checked ' : null ?>
                       onchange="this.form.submit()" />
                <span class="opt_labels"><?= $lng['L_OTHERS'] ?></span>
            </div>
            <div class="bookmarks_ctrl">
                <input type="hidden" name="show_bookmarks" value="0" />
                <input class="check" type="checkbox" value="1" name="show_bookmarks"
                <?= $tdata['controls']['show_bookmarks_status'] ? ' checked ' : null; ?>
                       onchange="this.form.submit()" />
                <span class="opt_labels"><?= $lng['L_BOOKMARKS'] ?></span>
            </div>
            <div class="termlog_ctrl">
                <input type="hidden" name="show_termlog" value="0" />
                <input class="check" type="checkbox" value="1" name="show_termlog"
                <?= $tdata['controls']['show_termlog_status'] ? ' checked ' : null ?>
                       onchange="this.form.submit()" />
                <span class="opt_labels"><?= $lng['L_TERMLOG'] ?></span>
            </div>
        </form>
    </fieldset>
</div>

