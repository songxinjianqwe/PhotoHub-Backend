<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/26
 * Time: 20:37
 */

namespace app\controllers;


use app\controllers\base\BaseActiveController;
use app\models\activity\ActivityReply;
use Yii;
use yii\data\ActiveDataProvider;

/**
 *
 * 注意：活动回复的更新就是对应message的更新，只更新message即可，活动回复不能修改
 * Class ActivityReplyController
 * @package app\controllers
 */
class ActivityReplyController extends BaseActiveController {
    public $modelClass = 'app\models\activity\ActivityReply';
    private $hotActivitiesService;

    /**
     * @inheritDoc
     */
    public function init() {
        $this->hotActivitiesService = Yii::$container->get('app\cache\service\HotActivitiesService');
    }


    public function actions() {
        $actions = parent::actions();
        unset($actions['index'], $actions['update'], $actions['create'], $actions['delete']);
        return $actions;
    }

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors = parent::requireNone($behaviors, ['index', 'view']);
        $behaviors = parent::requireAdminOrMySelf($behaviors, ['create']);
        $behaviors = parent::requireCustomOrAdmin($behaviors, ['delete'], function () {
            $id = Yii::$app->request->get('id');
            $reply = ActivityReply::findOne($id);
            return $reply->user_id == Yii::$app->user->identity->getId();
        });
        return $behaviors;
    }

    public function actionIndex() {
        $activityId = Yii::$app->request->get('activity_id');
        return Yii::createObject([
            'class' => ActiveDataProvider::className(),
            'query' => ActivityReply::find()->where(['activity_id' => $activityId])
        ]);
    }
    //TODO
    public function actionCreate(){
        
    }
    
    //TODO
    public function actionDelete(){
        
    }


}