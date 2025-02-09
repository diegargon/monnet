<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Controllers;

use App\Models\CmdBookmarkModel;
use App\Services\Filter;

class CmdBookmarksController
{
    private CmdBookmarksController $cmdBookmarkModel;
    private Filter $filter;
    private \AppContext $ctx;

    public function __construct(\AppContext $ctx)
    {
        $this->cmdBookmarkModel = new CmdBookmarkModel($ctx);
        $this->filter = new Filter();
        $this->ctx = $ctx;
    }

    public function addBookmark($command_values)
    {
        $value_command = $this->filter->varJson($command_values['value']);
        $decodedJson = json_decode($value_command, true);

        if ($decodedJson === null) {
            return [
                'command_error' => 1,
                'command_error_msg' => 'JSON Invalid',
            ];
        }

        $new_bookmark = [];
        foreach ($decodedJson as $key => $value) {
            $new_bookmark[$key] = trim($value);
        }

        // Validar campos del bookmark
        if (!$this->filter->varString($new_bookmark['name'])) {
            return [
                'command_error' => 1,
                'command_error_msg' => 'Name is empty or invalid',
            ];
        }

        // Lógica para agregar el bookmark
        $result = $this->cmdBookmarkModel->add($new_bookmark);

        if ($result) {
            return [
                'command_success' => 1,
                'response_msg' => 'Bookmark added successfully',
            ];
        } else {
            return [
                'command_error' => 1,
                'command_error_msg' => 'Error adding bookmark',
            ];
        }
    }

    public function updateBookmark($command_values)
    {
        $target_id = $this->filter->varInt($command_values['id']);
        $value_command = $this->filter->varJson($command_values['value']);
        $decodedJson = json_decode($value_command, true);

        if ($decodedJson === null) {
            return [
                'command_error' => 1,
                'command_error_msg' => 'JSON Invalid',
            ];
        }

        $bookmark = ['id' => $target_id];
        foreach ($decodedJson as $key => $value) {
            $bookmark[$key] = trim($value);
        }

        // Validar campos del bookmark
        if (!$this->filter->varString($bookmark['name'])) {
            return [
                'command_error' => 1,
                'command_error_msg' => 'Name is empty or invalid',
            ];
        }

        // Lógica para actualizar el bookmark
        $result = $this->cmdBookmarkModel->update($bookmark);

        if ($result) {
            return [
                'command_success' => 1,
                'response_msg' => 'Bookmark updated successfully',
            ];
        } else {
            return [
                'command_error' => 1,
                'command_error_msg' => 'Error updating bookmark',
            ];
        }
    }

    public function removeBookmark($command_values)
    {
        $target_id = $this->filter->varInt($command_values['id']);

        if ($this->cmdBookmarkModel->remove($target_id)) {
            return [
                'command_success' => 1,
                'response_msg' => 'Bookmark removed successfully',
            ];
        } else {
            return [
                'command_error' => 1,
                'command_error_msg' => 'Error removing bookmark',
            ];
        }
    }
}
