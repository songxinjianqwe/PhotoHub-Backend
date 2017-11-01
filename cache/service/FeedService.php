<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/27
 * Time: 11:35
 */

namespace app\cache\service;


use app\cache\RedisZSetManager;
use app\models\follow\Follow;
use app\models\moment\Moment;
use app\models\page\PageVO;
use app\util\DBUtil;
use Yii;
use yii\db\Expression;

class FeedService {
    private $manager;

    public function __construct() {
        $this->manager = new RedisZSetManager('feed');
    }

    public function addMoment($userId, $momentId) {
        Yii::info('FeedService::addMoment: userId' . $userId . '  momentId:' . $momentId);
        $followers = Follow::find()->where(['followed_user_id' => $userId])->all();
        foreach ($followers as $follower) {
            $this->manager->addElement($momentId, time(), $follower->user_id);
        }
        //除了给关注了自己的人发，也给自己发
        $this->manager->addElement($momentId, time(),$userId);
    }
    
    public function removeMoment($userId, $momentId) {
        Yii::info('FeedService::removeMoment: userId' . $userId . '  momentId:' . $momentId);
        $followers = Follow::find()->where(['followed_user_id' => $userId])->all();
        foreach ($followers as $follower) {
            $this->manager->removeElement($momentId, $follower->user_id);
        }
    }
    
    public function show($userId, $page, $per_page) {
        $pageDTO = $this->manager->indexDesc($page, $per_page, $userId);
        return new PageVO(DBUtil::orderByField($pageDTO->ids,Moment::find()->where(['id' => $pageDTO->ids])->all(),"id"), $pageDTO->_meta);
    }
}