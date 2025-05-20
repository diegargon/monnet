<?php

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

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $db = new DBManager($ctx);
        $this->model = new ItemsModel($db);
        $userService = $ctx->get(UserService::class);
        $this->uid = $userService->getId();
        $this->items = $this->model->getAllByUser($this->uid);
    }

    public function getAll(?string $key_order = null, ?string $dir = 'asc'): array
    {
        $items = $this->items;
        if (!empty($key_order)) {
            ArrayUtils::order($items, $key_order, $dir);
        }
        return $items;
    }

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

    public function remove(int $id): bool
    {
        $ok = $this->model->delete($id);
        if ($ok) {
            $this->items = $this->model->getAllByUser($this->uid);
        }
        return $ok;
    }

    public function getByType(string $type, ?string $key_order = 'weight', ?string $dir = 'asc'): array
    {
        $result = $this->model->getByType($this->uid, $type);
        if (!empty($key_order)) {
            ArrayUtils::order($result, $key_order, $dir);
        }
        return $result;
    }

    public function getByCatID(int $category_id): array
    {
        return $this->model->getByCatID($this->uid, $category_id);
    }

    public function getTypes(): array
    {
        $types = array_column($this->items, 'type');
        $uniq_types = array_unique($types);
        return array_values($uniq_types);
    }

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