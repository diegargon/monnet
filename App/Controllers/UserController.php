<?php

/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 *
*/

namespace App\Controllers;

use App\Services\Filter;
use App\Services\UserService;
use App\Helpers\Response;
use App\Core\AppContext;

/*
 * Temp Wrap pre rewrite User
 */

class UserController
{
    private $ctx;
    private UserService $user;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->user = $ctx->get(UserService::class);
    }

    /**
     *
     * @param string $command
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function setPref(string $command, array $command_values): array
    {
        $num = Filter::varInt($command_values['value']);

        if (!is_numeric($num)) {
            return Response::stdReturn(true, $command . ': fail');
        }

        switch ($command) :
            case 'network_select':
            case 'network_unselect':
                if ($command === 'network_select') {
                    $pref_name = 'network_select_' . $num;
                    $value = $num;
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
        $id = Filter::varInt($command_values['id']);
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
        $id = Filter::varInt($command_values['id']);
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
        $id = Filter::varInt($command_values['id']);
        $this->user->setPref('default_bookmarks_tab', $id);

        return Response::stdReturn(true, 'ok');
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function updateProfile(array $command_values): array
    {
        $user = $this->user->getCurrentUser();
        if (empty($user['id'])) {
            return Response::stdReturn(false, 'No autenticado', true);
        }

        $current_password = $command_values['current_password'] ?? '';
        if (!$this->user->verify_password($current_password, $user['password'])) {
            return Response::stdReturn(false, 'Contraseña actual incorrecta', true);
        }

        $updateData = [];

        // Filtrar username (alphanum, min 3, max 32)
        if (!empty($command_values['username']) && $command_values['username'] !== $user['username']) {
            $username = Filter::varAlphanum($command_values['username'], 32, 3);
            if ($username === false) {
                return Response::stdReturn(false, 'Username inválido', true);
            }
            $updateData['username'] = $username;
        }
        // Filtrar email
        if (!empty($command_values['email']) && $command_values['email'] !== $user['email']) {
            $email = Filter::varEmail($command_values['email'], 128, 5);
            if ($email === false) {
                return Response::stdReturn(false, 'Email inválido', true);
            }
            $updateData['email'] = $email;
        }
        // Filtrar password (min 8)
        if (!empty($command_values['password'])) {
            $password = Filter::varPassword($command_values['password'], 128, 8);
            if ($password === false) {
                return Response::stdReturn(false, 'Contraseña inválida', true);
            }
            $updateData['password'] = $password;
        }

        if (empty($updateData)) {
            return Response::stdReturn(true, 'Sin cambios', true);
        }

        $result = $this->user->updateUser($user['id'], $updateData);

        if ($result === true) {
            return Response::stdReturn(true, 'Perfil actualizado', true);
        } else {
            return Response::stdReturn(false, $result, true);
        }
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function createUser(array $command_values): array
    {
        // Campos obligatorios
        $required = ['username', 'email', 'password', 'isAdmin'];
        $missing = [];
        foreach ($required as $field) {
            if (!isset($command_values[$field]) || $command_values[$field] === '' || $command_values[$field] === null) {
                $missing[] = $field;
            }
        }
        if (!empty($missing)) {
            return Response::stdReturn(false, 'Faltan campos obligatorios: ' . implode(', ', $missing), true);
        }

        // Filtrar username (alphanum, min 3, max 32)
        $username = Filter::varAlphanum($command_values['username'], 32, 3);
        if ($username === false) {
            return Response::stdReturn(false, 'Username inválido', true);
        }
        // Filtrar email
        $email = Filter::varEmail($command_values['email'], 128, 5);
        if ($email === false) {
            return Response::stdReturn(false, 'Email inválido', true);
        }
        // Filtrar password (min 8)
        $password = Filter::varPassword($command_values['password'], 128, 8);
        if ($password === false) {
            return Response::stdReturn(false, 'Contraseña inválida', true);
        }
        // Filtrar isAdmin (entero 0 o 1)
        $isAdmin = Filter::varInt($command_values['isAdmin'], 1);
        if ($isAdmin === null || ($isAdmin !== 0 && $isAdmin !== 1)) {
            return Response::stdReturn(false, 'isAdmin inválido', true);
        }

        // Comprobar si ya existe usuario con mismo username o email
        $userExists = false;
        $existing = $this->user->userModel->getByUsername($username);
        if ($existing) {
            $userExists = true;
        }
        if (!$userExists) {
            $existing = $this->user->userModel->getByEmail($email);
            if ($existing) {
                $userExists = true;
            }
        }

        if ($userExists) {
            return Response::stdReturn(false, 'Usuario o email ya existe', true);
        }

        $userData = [
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'isAdmin' => $isAdmin,
        ];

        try {
            $user = $this->user->register($userData);
            return Response::stdReturn(true, 'Usuario creado', true, ['user' => $user]);
        } catch (\Throwable $e) {
            return Response::stdReturn(false, $e->getMessage(), true);
        }
    }
}
