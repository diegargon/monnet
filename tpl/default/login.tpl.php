<?php
/**
 *
 *  @author dieg/o/@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
?>
<div class="header">
    <div class="head_align_center">
        <div id="head_left"><div class="head_left_content"></div></div>
        <div id="head_center">
            <div class="head_center_content">
                <div class="search_container">
                    <h1 class='title gradiant'><a href=""><?= strtoupper($cfg['app_name']) ?></a></h1>
                    <div class="search-wrapper">
                        <form target="_blank"  action="https://gooogle.com/search" method="GET">
                            <input type="text" name="q" required class="search-box" placeholder="Google" />
                            <button class="close-icon" type="reset"></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div id="head_right"><div class="head_right_content"></div></div>
    </div>
</div>

<div class="login_page">
    <div class="profile_box">
        <form  method="POST" action="?page=login">
            <div class="profile_name">
                <input size="18"  onfocus="this.value = ''" placeholder="<?= $tdata['username_placeholder'] ?>" class="login_username" type="text" name="username" value="<?= $tdata['username'] ?>" <?= $tdata['set_username_focus'] ? 'autofocus' : null ?>/>
            </div>
            <div class="profile_password">
                <input size="18"  onfocus="this.value = ''" placeholder="<?= $tdata['password_placeholder'] ?>" class="login_password" type="password" name="password" value="" <?= isset($tdata['set_pass_focus']) ? 'autofocus' : null ?>/>
            </div>
            <input type="submit" class="login_button" name="submit" value="<?= $tdata['log_in'] ?>"/>
        </form>
    </div>
</div>