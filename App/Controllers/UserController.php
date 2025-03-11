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

class UserController
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
        $num = $this->filter->varInt($command_values['value']);

        if (!is_numeric($num)) {
            return Response::stdReturn(true, $command . ': fail');
        }

        switch ($command) :
            case 'network_select':
            case 'network_unselect':
                if ($command === 'network_select') {
                    $pref_name = 'network_select_' . $num;
                    $value = 1;
                } else {
                    $pref_name = 'network_select_' . $num;
                    $value = 0;
                }
                break;
            case 'footer_dropdown_status':
                $pref_name = $command;
                $value = $num;
                break;
            default:
                return Response::stdReturn(false, $command . ': Command unknown', true);
        endswitch;

        $this->user->setPref($pref_name, $value);
        return Response::stdReturn(true, $command . ': success', true);
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
        $extra = [
            'command_receive' => $command,
            'id' => $id,
        ];
        return Response::stdReturn(true, $response, true, $extra);
    }

    /**
     *
     * @param string $command
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function onlyOneHostsCat(string $command, array $command_values): array
    {
        $id = $this->filter->varInt($command_values['id']);
        $categories_state = $this->user->getHostsCatState();

        $ones = 0;
        foreach ($categories_state as $state) :
            $state == 1 ? $ones++ : null;
        endforeach;

        if (empty($categories_state) || $ones == 1) :
            $this->user->turnHostsCatsOn();
        else :
            $this->user->turnHostsCatsOff();
            $this->user->toggleHostsCat($id);
        endif;
        $extra = [
            'command_receive' => $command,
            'id' => $id,
        ];

        return Response::stdReturn(true, 'ok', true, $extra);
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function changeBookmarksTab(array $command_values): array
    {
        $id = $this->filter->varInt($command_values['id']);
        $this->user->setPref('default_bookmarks_tab', $id);

        return Response::stdReturn(true, 'ok');
    }
}
