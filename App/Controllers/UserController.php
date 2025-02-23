<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 *
*/

namespace App\Controllers;

use App\Services\Filter;
use App\Helpers\Response;

/*
 * Temp Wrap pre rewrite User
 */

class  UserController
{
    private $ctx;
    private Filter $filter;
    private \User $user;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->user = $ctx->get('User');
        $this->filter = new Filter();
    }

    /**
     *
     * @param string $command
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function setPref(string $command, array $command_values): array
    {
        switch ($command):
            case 'network_select':
            case 'network_unselect':
                $num = $this->filter->varInt($command_values['value']);
                if (!is_numeric($num)) {
                    return Response::stdReturn(true, $command . ': fail');
                } else {
                    if ($command === 'network_select') {
                        $pref_name = 'network_select_' . $num;
                        $value = 1;
                    } else {
                        $pref_name = 'network_select_' . $num;
                        $value = 0;
                    }
                }
                $this->user->setPref($pref_name, $value);

                return Response::stdReturn(true, $command . ': success', true);
        endswitch;
    }

    /**
     *
     * @param string $command
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function toggleHostsCat(string $command, array $command_values): array
    {
        $id = $this->filter->varInt($command_values['id']);
        $response = $this->user->toggleHostsCat($id);

        return Response::stdReturn(true, $response, true);
    }

}
