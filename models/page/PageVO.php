<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/27
 * Time: 20:38
 */

namespace app\models\page;


class PageVO {
    public $items;
    public $_meta;

    public function __construct($items, $_meta) {
        $this->items = $items;
        $this->_meta = $_meta;
    }
}   