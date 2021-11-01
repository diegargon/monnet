<?php
/**
 *
 *  @author dieg/o/@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
?>

<div class="login_page">
    <div class="profile_box">
        <form  method="POST" action="?page=login">
            <div class="profile_name">
                <input size="18"  onfocus="this.value = ''" placeholder="<?= $tdata['username_placeholder'] ?>" class="login_username" type="text" name="username" value=""/>
            </div>
            <div class="profile_password">
                <input size="18"  onfocus="this.value = ''" placeholder="<?= $tdata['password_placeholder'] ?>" class="login_password" type="password" name="password" value=""/>
            </div>
            <input type="submit" class="login_button" name="submit" value="<?= $tdata['log_in'] ?>"/>
        </form>
    </div>
</div>