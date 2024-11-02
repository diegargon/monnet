<?php
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
/**
 * In frontend->getTpl()
 * @var array<string> $cfg
 * @var array<string> $lng
 * @var array<mixed> $tdata
 */
?>

<?php
if (valid_array($tdata['search_engines'])) {
    ?>
    <div class="search_container">
        <h1 class="title gradiant"><a href=""><?= $tdata['head_name'] ?></a></h1>
        <div class="search-wrapper">
            <form target="_blank"  action="<?= $tdata['search_engines'][0]['url'] ?>" method="GET">
                <input type="text" name="<?= $tdata['search_engines'][0]['name'] ?>" required
                       class="search-box" placeholder="Google" autofocus/>
                <button class="close-icon" type="reset"></button>
            </form>
        </div>
    </div>
<?php } ?>
