<?php
/**
 * Service for managing user items (CRUD operations, filtering, etc).
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
namespace App\Services;

use App\Core\AppContext;
use App\Core\DBManager;

use App\Services\UserService;
use App\Models\ItemsModel;
use App\Utils\ArrayUtils;

class ItemsService
{
    private AppContext $ctx;
    private ItemsModel $model;
    private int $uid;
    private array $items = [];

    /**
     * ItemsService constructor.
     *
     * @param AppContext $ctx Application context.
     */
    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $db = new DBManager($ctx);
        $this->model = new ItemsModel($db);
        $userService = $ctx->get(UserService::class);
        $this->uid = $userService->getId();
        $this->items = $this->model->getAllByUser($this->uid);
    }

    /**
     * Get all items for the current user, optionally ordered by a key.
     *
     * @param string|null $key_order Field to order by.
     * @param string|null $dir Order direction ('asc' or 'desc').
     * @return array List of items.
     */
    public function getAll(?string $key_order = null, ?string $dir = 'asc'): array
    {
        $items = $this->items;
        if (!empty($key_order)) {
            ArrayUtils::order($items, $key_order, $dir);
        }
        return $items;
    }

    /**
     * Add a new item of the specified type.
     *
     * @param string $item_type The type of item (e.g., 'bookmarks').
     * @param array $item_data The item data.
     * @return bool True on success, false on failure.
     */
    public function addItem(string $item_type, array $item_data): bool
    {
        if ($item_type == 'bookmarks') {
            $conf = [
                'url' => $item_data['urlip'],
                'image_type' => $item_data['image_type'],
                'image_resource' => $item_data['field_img']
            ];
            $en_conf = json_encode($conf);
            if ($en_conf === false) {
                // Manejo de error (puedes usar un logger aquí)
                return false;
            }
            $set = [
                'uid' => $this->uid,
                'cat_id' => $item_data['cat_id'],
                'type' => 'bookmarks',
                'title' => $item_data['name'],
                'conf' => $en_conf,
                'weight' => $item_data['weight']
            ];
            $ok = $this->model->insert($set);
            if ($ok) {
                $this->items = $this->model->getAllByUser($this->uid);
                return true;
            }
        }
        return false;
    }

    /**
     * Update an existing item of the specified type.
     *
     * @param string $item_type The type of item (e.g., 'bookmarks').
     * @param array $item_data The item data (must include 'id').
     * @return bool True on success, false on failure.
     */
    public function updateItem(string $item_type, array $item_data): bool
    {
        if ($item_type == 'bookmarks') {
            $id = $item_data['id'];
            $conf = [
                'url' => $item_data['urlip'],
                'image_type' => $item_data['image_type'],
                'image_resource' => $item_data['field_img']
            ];
            $set = [
                'uid' => $this->uid,
                'cat_id' => $item_data['cat_id'],
                'type' => 'bookmarks',
                'title' => $item_data['name'],
                'conf' => json_encode($conf),
                'weight' => $item_data['weight']
            ];
            $ok = $this->model->update($id, $set);
            if ($ok) {
                $this->items = $this->model->getAllByUser($this->uid);
                return true;
            }
        }
        return false;
    }

    /**
     * Get an item by its ID, merging configuration data.
     *
     * @param int $id Item ID.
     * @return array Item data, or empty array if not found.
     */
    public function getById(int $id): array
    {
        $item = $this->model->getById($id);
        if ($item) {
            $confArray = json_decode($item['conf'], true);
            if (is_array($confArray)) {
                $item = array_merge($item, $confArray);
                unset($item['conf']);
            }
            return $item;
        }
        return [];
    }

    /**
     * Remove an item by its ID.
     *
     * @param int $id Item ID.
     * @return bool True on success, false on failure.
     */
    public function remove(int $id): bool
    {
        $ok = $this->model->delete($id);
        if ($ok) {
            $this->items = $this->model->getAllByUser($this->uid);
        }
        return $ok;
    }

    /**
     * Get items by type for the current user (and global items), optionally ordered.
     *
     * @param string $type Item type.
     * @param string|null $key_order Field to order by.
     * @param string|null $dir Order direction ('asc' or 'desc').
     * @return array List of items.
     */
    public function getByType(string $type, ?string $key_order = 'weight', ?string $dir = 'asc'): array
    {
        $result = $this->model->getByType($this->uid, $type);
        if (!empty($key_order)) {
            ArrayUtils::order($result, $key_order, $dir);
        }
        return $result;
    }

    /**
     * Get items by category ID for the current user.
     *
     * @param int $category_id Category ID.
     * @return array List of items.
     */
    public function getByCatID(int $category_id): array
    {
        return $this->model->getByCatID($this->uid, $category_id);
    }

    /**
     * Get all unique item types for the current user.
     *
     * @return array List of unique item types.
     */
    public function getTypes(): array
    {
        $types = array_column($this->items, 'type');
        $uniq_types = array_unique($types);
        return array_values($uniq_types);
    }

    /**
     * Change all items of a given type and removed category to the default category.
     *
     * @param string $item_type Item type.
     * @param int $removed_cat_id Category ID to be replaced.
     * @return bool True on success, false on failure.
     */
    public function changeToDefaultCat(string $item_type, int $removed_cat_id): bool
    {
        if ($item_type == 'bookmarks') {
            $default_cat = 50;
        } else {
            return false;
        }
        // Actualiza todos los items de ese tipo y categoría
        $items = $this->model->getByType($this->uid, $item_type);
        $ok = true;
        foreach ($items as $item) {
            if ($item['cat_id'] == $removed_cat_id) {
                $ok = $ok && $this->model->update($item['id'], ['cat_id' => $default_cat]);
            }
        }
        if ($ok) {
            $this->items = $this->model->getAllByUser($this->uid);
        }
        return $ok;
    }
}