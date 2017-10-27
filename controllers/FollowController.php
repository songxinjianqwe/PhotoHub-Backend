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
        unset($actions['index'], $actions['view'], $actions['update'], $actions['create'], $actions['delete']);
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

    /**
     * 同步更新Feed
     * @return \yii\db\ActiveRecordInterface
     */
    public function actionCreate() {
        //实际的create
        $action = new CreateAction('create', $this, ['modelClass' => $this->modelClass]);
        $model = $action->run();
        //后置的同步更新
        $followed_user_id = Yii::$app->request->bodyParams["followed_user_id"];
        $user = User::findOne($followed_user_id);
        $user->followers++;
        $user->update();
        return $model;
    }

    /**
     * 同步更新Feed
     */
    public function actionDelete() {
        $followId = Yii::$app->request->get('id');
        $follow = Follow::findOne($followId);
        $user = User::findOne($follow->followed_user_id);
        $user->followers--;
        $user->update();
        $follow->delete();
    }

}