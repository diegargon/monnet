<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
function valid_array($array) {
    if (!empty($array) && is_array($array) && count($array) > 0) {
        return true;
    }

    return false;
}
