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

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function addBookmark(array $command_values): array
    {
        $value_command = $this->filter->varJson($command_values['value']);
        $decodedJson = json_decode($value_command, true);

        if ($decodedJson === null) {
            return Response::stdReturn(false, 'JSON Invalid');
        }

        $new_bookmark = [];
        foreach ($decodedJson as $key => $value) {
            $new_bookmark[$key] = trim($value);
        }

        // Validar campos del bookmark
        if (!$this->filter->varString($new_bookmark['name'])) {
            return Response::stdReturn(false, 'Name is empty or invalid');
        }

        // Lógica para agregar el bookmark
        $result = $this->cmdBookmarkModel->add($new_bookmark);

        if ($result) {
            return Response::stdReturn(true, 'Bookmark added successfully');
        } else {
            return Response::stdReturn(false, 'Error adding bookmark');
        }
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function updateBookmark(array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);
        $value_command = $this->filter->varJson($command_values['value']);
        $decodedJson = json_decode($value_command, true);

        if ($decodedJson === null) {
            return Response::stdReturn(false, 'JSON Invalid');
        }

        $bookmark = ['id' => $target_id];
        foreach ($decodedJson as $key => $value) {
            $bookmark[$key] = trim($value);
        }

        // Validar campos del bookmark
        if (!$this->filter->varString($bookmark['name'])) {
            return Response::stdReturn(false, 'Name is empty or invalid');
        }

        // Lógica para actualizar el bookmark
        $result = $this->cmdBookmarkModel->update($bookmark);

        if ($result) {
            return Response::stdReturn(true, 'Bookmark updated successfully');
        } else {
            return Response::stdReturn(false, 'Error updating bookmark');
        }
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function removeBookmark(array $command_values): array
    {
        $target_id = $this->filter->varInt($command_values['id']);

        if ($this->cmdBookmarkModel->removeByID($target_id)) {
            return Response::stdReturn(true, 'Bookmark removed successfully');
        } else {
            return Response::stdReturn(false, 'Error removing bookmark');
        }
    }
}
