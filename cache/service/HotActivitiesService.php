<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/27
 * Time: 11:42
 */

namespace app\cache\service;


use app\cache\RedisZSetManager;
use app\models\activity\Activity;
use app\models\page\PageVO;
use app\util\DBUtil;

class HotActivitiesService {
    private $manager;

    public function __construct() {
        $this->manager = new RedisZSetManager('activity.hot');
    }
    
    public function createActivity($activityId) {
        $this->manager->addElement($activityId, 0);
    }
    
    public function removeActivity($activityId) {
        $this->manager->removeElement($activityId);
    }

    public function createReply($activityId) {
        $this->manager->changeScore($activityId, 1);
    }

    public function removeReply($activityId) {
        $this->manager->changeScore($activityId, -1);
    }

    public function show($page, $per_page) {
        $pageDTO = $this->manager->indexDesc($page, $per_page);
        return new PageVO(DBUtil::orderByField($pageDTO->ids,Activity::find()->where(['id' => $pageDTO->ids])->all(),'id'), $pageDTO->_meta);
    }

}