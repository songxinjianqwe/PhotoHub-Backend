<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/27
 * Time: 20:37
 */

namespace app\models\page;


class PageDTO {
    public $ids;
    public $_meta;
    
    public function __construct($ids, $pageInfo) {
        $this->ids = $ids;
        $this->_meta = $pageInfo;
    }
}