<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/27
 * Time: 11:44
 */

namespace app\cache\service;


use app\cache\RedisZSetManager;
use app\models\moment\Moment;
use app\models\page\PageVO;
use app\util\DBUtil;

class HotMomentsByTagService {
    private $manager;

    public function __construct() {
        $this->manager = new RedisZSetManager('moment.hot.tag');
    }

    public function createMoment($momentId, $tagId) {
        $this->manager->addElement($momentId, 0, $tagId);
    }

    public function removeMoment($momentId, $tagId) {
        $this->manager->removeElement($momentId, $tagId);
    }

    public function referMoment($momentId, $tagId) {
        $this->manager->changeScore($momentId, 1, $tagId);
    }

    public function unReferMoment($momentId, $tagId) {
        $this->manager->changeScore($momentId, -1, $tagId);
    }

    public function show($tagId, $page, $per_page) {
        $pageDTO = $this->manager->indexDesc($page, $per_page, $tagId);
        return new PageVO(DBUtil::orderByField($pageDTO->ids,Moment::find()->where(['id' => $pageDTO->ids])->all(),'id'), $pageDTO->_meta);
    }
}