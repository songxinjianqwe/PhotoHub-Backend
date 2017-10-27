<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/27
 * Time: 11:35
 */

namespace app\cache\service;


use app\cache\RedisZSetManager;

class FeedService {
    private $manager;

    /**
     * @inheritDoc
     */
    public function __construct() {
        $this->manager = new RedisZSetManager('feed');
    }

    public function addMoment($userId, $momentId) {
        
    }

    public function removeMoment($userId, $momentId) {

    }

    public function follow($followUserId, $followedUserId) {

    }

    public function unFollow($followUserId, $followedUserId) {

    }


}