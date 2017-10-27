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

class HotActivitiesService {
    private $manager;

    public function __construct() {
        $this->manager = new RedisZSetManager('activity.hot');
    }

    private function createActivity($activityId) {
        $this->manager->addElement($activityId, 0);
    }

    private function removeActivity($activityId) {
        $this->manager->removeElement($activityId);

    }

    private function createReply($activityId) {
        $this->manager->changeScore($activityId, 1);
    }

    private function removeReply($activityId) {
        $this->manager->changeScore($activityId, -1);
    }

    public function getHotActivities($page, $per_page) {
        $pageDTO = $this->manager->indexDesc($page, $per_page);
        return new PageVO(Activity::find()->where(['id' => $pageDTO->ids])->orderBy('id desc')->all(), $pageDTO->_meta);
    }

}