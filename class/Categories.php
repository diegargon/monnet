<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

class Categories
{
    /**
     * @phpstan-ignore-next-line
     */
    private AppContext $ctx;

    /**
     * @var array<string|int> $cfg
     */
    private array $cfg = [];
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

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->cfg = $ctx->get('cfg');
        $this->db = $ctx->get('Mysql');
        $this->lng = $ctx->get('lng');

        $results = $this->db->select('categories', '*');
        $this->categories = $this->db->fetchAll($results);
        $this->cat_types = $this->cfg['cat_types'];
    }

    public function getAll(): array
    {

        return $this->categories;
    }

    public function getTypes(): array
    {

        return $this->cat_types;
    }

    public function getByType(int $type): array|bool
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

        return !empty($categories_by_type) ? $categories_by_type : false;
    }

    public function getTypeByID(int $id): int|bool
    {
        foreach ($this->categories as $cat) {
            if ($cat['id'] == $id) {
                return $cat['cat_type'];
            }
        }

        return false;
    }

    public function prepareCats(int $type): array|bool
    {
        $categories_by_type = $this->getByType($type);
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
        $query_value = $this->db->valQuote($value);
        $query = "SELECT `cat_name` FROM categories WHERE `cat_type` = $cat_type AND `cat_name` = $query_value";

        if ($this->db->queryExists($query)) {
            $response['sucess'] = false;
            $response['msg'] = $this->lng['L_VALUE_EXISTS'];
        } else {
            $this->db->insert('categories', ['cat_name' => $value, 'cat_type' => $cat_type]);
            $response['success'] = true;
            $response['msg'] = $this->lng['L_OK'];
        }

        return $response;
    }

    public function remove(int $id): bool
    {
        $this->db->delete('categories', ['id' => $id], 'LIMIT 1');

        return true;
    }
}
