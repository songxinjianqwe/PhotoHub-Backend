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

class LatestActivitiesService {
    private $manager;

    public function __construct() {
        $this->manager = new RedisZSetManager('activity.latest');
    }

    public function createActivity($activityId) {
        $this->manager->addElement($activityId, time());
    }

    public function removeActivity($activityId) {
        $this->manager->removeElement($activityId);
    }

    public function show($page, $per_page) {
        $pageDTO = $this->manager->indexDesc($page, $per_page);
        return new PageVO(DBUtil::orderByField($pageDTO->ids,Activity::find()->where(['id' => $pageDTO->ids])->all(),'id'), $pageDTO->_meta);
    }

}