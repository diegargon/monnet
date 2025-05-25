<?php
/**
 * Router for handling  Bookmark related commands.
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
namespace App\Router;

use App\Core\AppContext;
use App\Controllers\CmdBookmarksController;

class BookmarkCommandRouter
{
    private AppContext $ctx;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
    }

    public function handle(string $command, array $command_values): array
    {
        $bookmarksController = new CmdBookmarksController($this->ctx);
        switch ($command) {
            case 'addBookmark':
                return $bookmarksController->addBookmark($command_values);
            case 'updateBookmark':
                return $bookmarksController->updateBookmark($command_values);
            case 'removeBookmark':
                return $bookmarksController->removeBookmark($command_values);
            case 'mgmtBookmark':
                return $bookmarksController->mgmtBookmark($command, $command_values);
            case 'submitBookmarkCat':
                return $bookmarksController->submitBookmarkCat($command_values);
            case 'removeBookmarkCat':
                return $bookmarksController->removeBookmarkCat($command_values);
            default:
                return [
                    'command_error' => 1,
                    'command_error_msg' => 'Comando no reconocido (bookmark): ' . $command,
                ];
        }
    }
}
