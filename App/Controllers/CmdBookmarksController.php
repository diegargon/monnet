<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Controllers;

use App\Services\Filter;
use App\Services\TemplateService;
use App\Helpers\Response;

class CmdBookmarksController
{
    private Filter $filter;
    private \AppContext $ctx;
    private TemplateService $templateService;

    public function __construct(\AppContext $ctx)
    {
        $this->filter = new Filter();
        $this->ctx = $ctx;
        $this->templateService = new TemplateService($ctx);
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function addBookmark(array $command_values): array
    {
        $lng = $this->ctx->get('lng');

        $value_command = $this->filter->varJson($command_values['value']);
        $decodedJson = json_decode($value_command, true);

        if ($decodedJson === null) {
            return Response::stdReturn(false, 'JSON Invalid');
        }

        $new_bookmark = [];
        foreach ($decodedJson as $key => $value) {
            $new_bookmark[$key] = trim($value);
        }

        // Validar campos
        if (!$this->filter->varString($new_bookmark['name'])) {
            return Response::stdReturn(false, "{$lng['L_NAME']}: {$lng['L_ERROR_EMPTY_INVALID']}");
        }
        if (!$this->filter->varString($new_bookmark['image_type'])) {
            return Response::stdReturn(false, "{$lng['L_IMAGE_TYPE']}: {$lng['L_ERROR_EMPTY_INVALID']}");
        }
        if (!$this->filter->varInt($new_bookmark['cat_id'])) {
            return Response::stdReturn(false, "{$lng['L_TYPE']}: {$lng['L_ERROR_EMPTY_INVALID']}");
        }
        if (
            !$this->filter->varUrl($new_bookmark['urlip']) &&
            !$this->filter->varIP($new_bookmark['urlip'])
        ) {
            return Response::stdReturn(false, "{$lng['L_URLIP']}:{$lng['L_ERROR_EMPTY_INVALID']}");
        }
        if (
            !$this->filter->varInt($new_bookmark['weight']) &&
            ($this->filter->varInt($new_bookmark['weight']) !== 0)
        ) {
            return Response::stdReturn(false, "{$lng['L_WEIGHT']}: {$lng['L_ERROR_EMPTY_INVALID']}");
        }

        if ($new_bookmark['image_type'] === 'local_img') {
            if (empty($new_bookmark['field_img'])) {
                return Response::stdReturn(false, "{$lng['L_LINK']}: {$lng['L_ERROR_EMPTY_INVALID']}");
            } else {
                if (
                        !$this->filter->varCustomString($new_bookmark['field_img'], '.', 255) ||
                        !file_exists('bookmarks_icons/')
                ) {
                    return Response::stdReturn(false, "{$lng['L_LINK']}: {$lng['L_ERROR_EMPTY_INVALID']}");
                }
            }
        }

        if ($new_bookmark['image_type'] == 'url' && !empty($new_bookmark['field_img'])) {
            if (!$this->filter->varUrl($new_bookmark['field_img'])) {
                return Response::stdReturn(false, "{$lng['L_ERROR_URL_INVALID']}");
            }
        }

        // TODO BookmarkModel?
        $result = $this->ctx->get('Items')->addItem('bookmarks', $new_bookmark);

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
        $lng = $this->ctx->get('lng');
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
        if (!$this->filter->varInt($bookmark['bookmark_id'])) {
            return Response::stdReturn(false, "{$lng['L_TYPE']}: {$lng['L_ERROR_EMPTY_INVALID']}");
        }
        if (!$this->filter->varString($bookmark['name'])) {
            return Response::stdReturn(false, "{$lng['L_NAME']}: {$lng['L_ERROR_EMPTY_INVALID']}");
        }
        if (!$this->filter->varString($bookmark['image_type'])) {
            return Response::stdReturn(false, "{$lng['L_IMAGE_TYPE']}: {$lng['L_ERROR_EMPTY_INVALID']}");
        }
        if (!$this->filter->varInt($bookmark['cat_id'])) {
            return Response::stdReturn(false, "{$lng['L_TYPE']}: {$lng['L_ERROR_EMPTY_INVALID']}");
        }
        if (
            !$this->filter->varUrl($bookmark['urlip']) &&
            !$this->filter->varIP($bookmark['urlip'])
        ) {
            return Response::stdReturn(false, "{$lng['L_URLIP']}:{$lng['L_ERROR_EMPTY_INVALID']}");
        }
        if (
                !$this->filter->varInt($bookmark['weight']) &&
                ($this->filter->varInt($bookmark['weight']) !== 0)
        ) {
            return Response::stdReturn(false, "{$lng['L_WEIGHT']}: {$lng['L_ERROR_EMPTY_INVALID']}");
        }

        if ($bookmark['image_type'] === 'local_img') :
            if (empty($bookmark['field_img'])) {
                return Response::stdReturn(false, "{$lng['L_LINK']}: {$lng['L_ERROR_EMPTY_INVALID']}");
            } else {
                if (
                        !$this->filter->varCustomString($bookmark['field_img'], '.', 255) ||
                        !file_exists('bookmarks_icons/')
                ) {
                    return Response::stdReturn(false, "{$lng['L_LINK']}: {$lng['L_ERROR_EMPTY_INVALID']}");
                }
            }
        endif;

        if ($bookmark['image_type'] == 'url' && !empty($bookmark['field_img'])) :
            if (!$this->filter->varUrl($bookmark['field_img'])) {
                return Response::stdReturn(false, "{$lng['L_ERROR_URL_INVALID']}");
            }
        endif;

        // TODO BookmarkModel?
        $result = $this->ctx->get('Items')->updateItem('bookmarks', $bookmark);

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

        if ($this->ctx->get('Items')->remove($target_id)) {
            return Response::stdReturn(true, 'Bookmark removed successfully');
        } else {
            return Response::stdReturn(false, 'Error removing bookmark');
        }
    }

    /**
     *
     * @param string $command
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function mgmtBookmark(string $command, array $command_values): array
    {
        $categories = $this->ctx->get('Categories');
        $target_id = $this->filter->varInt($command_values['id']);
        $tdata = [];
        $items = $this->ctx->get('Items');
        $cfg =  $this->ctx->get('cfg');
        $lng =  $this->ctx->get('lng');


        if (isset($command_values['action']) && $command_values['action'] === 'edit') {
            $tdata = $items->getById($target_id);
        }
        $tdata['web_categories'] = [];
        if (!empty($tdata['conf'])) {
            $conf = json_decode($tdata, true);
            $tdata['image_resource'] = $conf['image_resource'];
            $tdata['image_type'] = $conf['image_type'];
        }
        if ($categories !== null) {
            $tdata['web_categories'] = $categories->getByType(2);
        }

        $tdata['local_icons'] = getLocalIconsData($cfg, 'bookmarks_icons/');
        if (isset($command_values['action']) && $command_values['action'] === 'edit') {
            $tdata['bookmark_buttonid'] = 'updateBookmark';
            $tdata['bookmark_title'] = $lng['L_EDIT'];
        } elseif (isset($command_values['action']) && $command_values['action'] === 'add') {
            $tdata['bookmark_buttonid'] = 'addBookmark';
            $tdata['bookmark_title'] = $lng['L_ADD'];
        }

        $extra = [
            'command_receive' => $command,
            'mgmt_bookmark' => [
                'cfg' => ['place' => '#left-container'],
                'data' => $this->templateService->getTpl('mgmt-bookmark', $tdata)
            ]
        ];

        return Response::stdReturn(true, $target_id, false, $extra);
    }

    /**
     *
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function submitBookmarkCat(array $command_values): array
    {
        $value = $this->filter->varString($command_values['value']);
        $response = $this->ctx->get('Categories')->create(2, $value);

        if ($response['success'] == 1) {
            return Response::stdReturn(true, $response['msg']);
        }

        return Response::stdReturn(false, $response['msg']);
    }

    /**
     * Remove bookmark category
     * 50 default bookmark Cat L_OTHERS
     * @param array<string, string|int> $command_values
     * @return array<string, string|int>
     */
    public function removeHostsCat(array $command_values): array
    {
        $lng = $this->ctx->get('lng');
        $target_id = $this->filter->varInt($command_values['id']);

        if ($target_id === 50) {
            return Response::stdReturn(false, $lng['L_ERR_CAT_NODELETE']);
        }

        $categories = $this->ctx->get('Categories');
        if ($categories->remove($target_id)) {
            //Change remain utems to default category
            if ($categories->updateToDefault(50, $target_id)) {
                return Response::stdReturn(true, 'ok: ' . $target_id);
            }
        }

        return Response::stdReturn(false, $lng['L_ERROR']);
    }
}
