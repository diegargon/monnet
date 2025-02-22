<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Models;

class LogModel {
    private \AppContext $ctx;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
    }
}
