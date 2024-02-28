<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

class Categories {

    private AppCtx $ctx;
    private array $cfg;
    private Database $db;
    private array $categories = [];
    private array $cat_types;
    private array $lng = [];

    public function __construct(AppCtx $ctx) {
        $this->ctx = $ctx;
        $this->cfg = $ctx->getAppCfg();
        $this->db = $ctx->getAppDb();
        $this->lng = $ctx->getAppLang();

        $results = $this->db->select('categories', '*');
        $this->categories = $this->db->fetchAll($results);
        $this->cat_types = $this->cfg['cat_types'];
    }

    public function getAll() {

        return $this->categories;
    }

    public function getTypes() {

        return $this->cat_types;
    }

    public function getByType($type) {
        $categories_by_type = [];

        foreach ($this->categories as $cat) {
            if ($cat['cat_type'] == $type) {
                $categories_by_type[] = $cat;
            }
        }

        return !empty($categories_by_type) ? $categories_by_type : false;
    }

    public function getTypeByID($id) {
        foreach ($this->categories as $cat) {
            if ($cat['id'] == $id) {
                return $cat['cat_type'];
            }
        }

        return false;
    }

    public function prepareCats(int $type) {
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
}
