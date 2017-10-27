<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/27
 * Time: 21:10
 */

namespace app\models\page;


class PageInfo {
    public $totalCount;
    public $pageCount;
    public $currentPage;
    public $perPage;

    /**
     * PageInfo constructor.
     * @param $totalCount
     * @param $pageCount
     * @param $currentPage
     * @param $perPage
     */
    public function __construct($totalCount, $pageCount, $currentPage, $perPage) {
        $this->totalCount = $totalCount;
        $this->pageCount = $pageCount;
        $this->currentPage = $currentPage;
        $this->perPage = $perPage;
    }
}