<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Models;

class CmdBookmarksModel
{
    private \AppContext $ctx;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
    }

    public function add($bookmark)
    {
        $db = $this->ctx->get('DBManager');
        return $db->insert('bookmarks', $bookmark);
    }

    public function update($bookmark)
    {
        $db = $this->ctx->get('DBManager');
        return $db->update('bookmarks', $bookmark, ['id' => $bookmark['id']]);
    }

    public function remove($target_id)
    {
        $db = $this->ctx->get('DBManager');
        return $db->delete('bookmarks', ['id' => $target_id]);
    }
}
