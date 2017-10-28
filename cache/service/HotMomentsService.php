<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/27
 * Time: 11:40
 */

namespace app\cache\service;


use app\cache\RedisZSetManager;
use app\models\moment\Moment;
use app\models\page\PageVO;
use app\util\DBUtil;

class HotMomentsService {
    private $manager;

    public function __construct() {
        $this->manager = new RedisZSetManager('moment.hot');
    }

    public function createMoment($momentId) {
        $this->manager->addElement($momentId, 0);
    }

    public function removeMoment($momentId) {
        $this->manager->removeElement($momentId);
    }

    public function referMoment($momentId) {
        $this->manager->changeScore($momentId, 1);
    }

    public function unReferMoment($momentId) {
        $this->manager->changeScore($momentId, -1);
    }

    public function getMomentScore($momentId) {
        return $this->manager->getScore($momentId);
    }

    public function show($page, $per_page) {
        $pageDTO = $this->manager->indexDesc($page, $per_page);
        return new PageVO(DBUtil::orderByField($pageDTO->ids,Moment::find()->where(['id' => $pageDTO->ids])->all(),'id'), $pageDTO->_meta);
    }
}