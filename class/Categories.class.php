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

    private $cfg;
    private $db;
    private $categories = [];
    private $cat_types;
    private $lng = [];

    public function __construct(array &$cfg, array $lng, Database &$db) {
        $this->cfg = &$cfg;
        $this->db = &$db;
        $this->lng = $lng;

        $results = $db->select('categories', '*');
        $this->categories = $db->fetchAll($results);
        $this->cat_types = $cfg['cat_types'];
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

    public function getOnByType($type) {
        $by_type_return = [];

        $categories_by_type = $this->getByType($type);

        foreach ($categories_by_type as $typecat) {
            if ($typecat['on']) {
                $by_type_return[] = $typecat;
            }
        }

        return valid_array($by_type_return) ? $by_type_return : false;
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
