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
use app\models\user\User;
use app\util\DBUtil;
use Yii;

class TagTalentService {
    private $manager;

    public function __construct() {
        $this->manager = new RedisZSetManager('tag.talent');
    }

    //tag.talent.tag_id
    //点赞评论转发时被调用
    public function referMoment($moment) {
        foreach ($moment->tags as $tag) {
            Yii::info('给tag:'.$tag->id.'对应的用户:'.$moment->user_id.'加一分');
            $this->manager->changeScore($moment->user_id, 1, $tag->id);
        }
    }

    //删除点赞、删除评论时被调用
    public function unReferMoment($moment) {
        foreach ($moment->tags as $tag) {
            Yii::info('给tag:'.$tag->id.'对应的用户:'.$moment->user_id.'减一分');
            $this->manager->changeScore($moment->user_id, -1, $tag->id);
        }
    }

    //删除动态时被调用
    public function removeMoment($moment, $referTimes) {
        foreach ($moment->tags as $tag) {
            Yii::info('给tag:'.$tag->id.'对应的用户:'.$moment->user_id.'减'.$referTimes.'分');
            $this->manager->changeScore($moment->user_id, -$referTimes, $tag->id);
        }
    }
    
    public function show($tagId, $page, $per_page) {
        $pageDTO = $this->manager->indexDesc($page, $per_page, $tagId);
        return new PageVO(DBUtil::orderByField($pageDTO->ids,User::find()->where(['id' => $pageDTO->ids])->all(),'id'), $pageDTO->_meta);
    }
}