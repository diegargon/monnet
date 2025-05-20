<?php

/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Services;

use App\Core\AppContext;
use App\Core\DBManager;
use App\Core\ConfigService;

use App\Models\CategoriesModel;

class CategoriesService
{
    private AppContext $ctx;
    private CategoriesModel $model;
    private array $categories = [];
    private array $cat_types = [];
    private array $lng = [];
    private ConfigService $ncfg;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $db = new DBManager($ctx);
        $this->ncfg = $ctx->get(ConfigService::class);
        $this->lng = $ctx->get('lng');
        $this->model = new CategoriesModel($db);
        $this->categories = $this->model->getAll();
        $this->cat_types = $this->ncfg->get('cat_types');
    }

    public function getAll(): array
    {
        return $this->categories;
    }

    public function getTypes(): array
    {
        return $this->cat_types;
    }

    public function getByType(int $type): array
    {
        return $this->model->getByType($type);
    }

    public function getTypeByID(int $id): int|bool
    {
        return $this->model->getTypeByID($id);
    }

    public function prepareCats(int $type): array
    {
        $categories_by_type = $this->getByType($type);
        if (empty($categories_by_type)) {
            return [];
        }
        foreach ($categories_by_type as &$typecat) {
            if (
                (strpos($typecat['cat_name'], 'L_') === 0 ) &&
                isset($this->lng[$typecat['cat_name']])
            ) {
                $typecat['cat_name'] = $this->lng[$typecat['cat_name']];
            }
        }
        return $categories_by_type;
    }

    public function create(int $cat_type, string $value): array
    {
        // Check if category exists
        $exists = $this->model->getByType($cat_type);
        foreach ($exists as $cat) {
            if ($cat['cat_name'] === $value) {
                return [
                    'success' => -1,
                    'msg' => $this->lng['L_VALUE_EXISTS'] ?? 'Value exists'
                ];
            }
        }
        $ok = $this->model->create($cat_type, $value);
        if ($ok) {
            return [
                'success' => 1,
                'msg' => $this->lng['L_OK'] ?? 'OK'
            ];
        } else {
            return [
                'success' => 0,
                'msg' => $this->lng['L_ERROR'] ?? 'Error'
            ];
        }
    }

    public function remove(int $id): bool
    {
        return $this->model->remove($id);
    }

    public function updateToDefault(int $default_category, int $old_category): bool
    {
        return $this->model->updateToDefault($default_category, $old_category);
    }
}
