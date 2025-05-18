<?php

namespace App\Services;
use App\Models\CategoriesModel;

class CategoriesService
{
    private \AppContext $ctx;
    private CategoriesModel $model;
    private array $categories = [];
    private array $cat_types = [];
    private array $lng = [];
    private \Config $ncfg;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
        $db = $this->ctx->get('DBManager');
        $this->ncfg = $ctx->get('Config');
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
        $result = $this->model->create($cat_type, $value);
        if ($result['success'] === -1) {
            $result['msg'] = $this->lng['L_VALUE_EXISTS'];
        } else {
            $result['msg'] = $this->lng['L_OK'];
        }
        return $result;
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