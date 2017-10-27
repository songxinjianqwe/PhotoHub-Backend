<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/25
 * Time: 14:36
 */

namespace app\controllers;


use app\controllers\base\BaseActiveController;
use app\models\follow\Follow;
use app\models\follow\FollowGroup;
use app\models\user\User;
use Yii;
use yii\rest\CreateAction;
use yii\rest\DeleteAction;
use yii\web\ForbiddenHttpException;

class FollowController extends BaseActiveController {
    public $modelClass = 'app\models\follow\Follow';

    public function actions() {
        $actions = parent::actions();
        unset($actions['index'], $actions['view'], $actions['update']);
        return $actions;
    }

    /**
     * 只有关注和取关两种操作
     * @return array
     */
    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors = parent::requireAdminOrMySelf($behaviors, ['create'], function () {
            $groupId = Yii::$app->request->bodyParams["group_id"];
            $group = FollowGroup::findOne($groupId);
            return $group->user_id == Yii::$app->user->identity->getId();
        });
        
        $behaviors = parent::requireAdminOrMySelf($behaviors, ['delete'], function () {
            $followId = Yii::$app->request->get('id');
            $follow = Follow::findOne($followId);
            $followGroup = FollowGroup::findOne($follow->group_id);
            return $followGroup->user_id == Yii::$app->user->identity->getId();
        });
        return $behaviors;
    }
}