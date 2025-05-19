<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

use App\Core\AppContext;
use App\Core\ConfigService;

class Categories
{
    /**
     * @phpstan-ignore-next-line
     */
    private AppContext $ctx;

    /**
     *
     * @var Database
     */
    private Database $db;
    /**
     * @var array<int, array<string, mixed>> $categories
     */
    private array $categories = [];

    /**
     * @var array<string> $cat_types
     */
    private array $cat_types = [];

    /**
     * @var array<string> $lng
     */
    private array $lng = [];

    /**
     *
     * @var ConfigService
     */
    private ConfigService $ncfg;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->ncfg = $ctx->get(ConfigService::class);
        $this->db = $ctx->get('Mysql');
        $this->lng = $ctx->get('lng');

        $results = $this->db->select('categories', '*');
        $this->categories = $this->db->fetchAll($results);
        $this->cat_types = $this->ncfg->get('cat_types');
    }

    /**
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAll(): array
    {

        return $this->categories;
    }

    /**
     *
     * @return array<int, string>
     */
    public function getTypes(): array
    {

        return $this->cat_types;
    }
    /**
     *
     * @param int $type
     * @return  array<array<string, mixed>|string>
     */
    public function getByType(int $type): array
    {
        /**
         * @var array<string> $categories_by_type
         */
        $categories_by_type = [];

        foreach ($this->categories as $cat) {
            if ($cat['cat_type'] == $type) {
                $categories_by_type[] = $cat;
            }
        }

        return $categories_by_type;
    }

    /**
     *
     * @param int $id
     * @return int|bool
     */
    public function getTypeByID(int $id): int|bool
    {
        foreach ($this->categories as $cat) {
            if ($cat['id'] == $id) {
                return $cat['cat_type'];
            }
        }

        return false;
    }

    /**
     *
     * @param int $type
     * @return array<array<string, mixed>>
     */
    public function prepareCats(int $type): array
    {
        $categories_by_type = $this->getByType($type);
        if (empty($categories_by_type)) :
            return [];
        endif;

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

    /**
     *
     * @param int $cat_type
     * @param string $value
     * @return array<string,int|string>
     */
    public function create(int $cat_type, string $value): array
    {
        $query_value = $this->db->valQuote($value);
        $query = "SELECT `cat_name` FROM categories WHERE `cat_type` = $cat_type AND `cat_name` = $query_value";

        if ($this->db->queryExists($query)) {
            $response['sucess'] = -1;
            $response['msg'] = $this->lng['L_VALUE_EXISTS'];
        } else {
            $this->db->insert('categories', ['cat_name' => $value, 'cat_type' => $cat_type]);
            $response['success'] = 1;
            $response['msg'] = $this->lng['L_OK'];
        }

        return $response;
    }

    /**
     *
     * @param int $id
     * @return bool
     */
    public function remove(int $id): bool
    {
        return $this->db->delete('categories', ['id' => $id], 'LIMIT 1');
    }

    /**
     *
     * @param int $default_category
     * @param int $old_category
     * @return bool
     */
    public function updateToDefault(int $default_category, int $old_category): bool
    {
        return $this->db->update('items', ['category' => $default_category], ['cat_id' => $old_category]);
    }
}
