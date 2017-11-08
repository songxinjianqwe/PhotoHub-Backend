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
use yii\db\IntegrityException;
use yii\rest\CreateAction;
use yii\rest\DeleteAction;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

class FollowController extends BaseActiveController {
    public $modelClass = 'app\models\follow\Follow';

    public function actions() {
        $actions = parent::actions();
        unset($actions['index'], $actions['view'], $actions['update'], $actions['delete'],$actions['create']);
        return $actions;
    }

    public function actionCreate() {
        $follow = new Follow();
        $follow->load(Yii::$app->getRequest()->getBodyParams(), '');
        try {
            $follow->save();
        } catch (IntegrityException $e) {
            throw new BadRequestHttpException('followedUser can not be allocated to more than one group');
        }
        return $follow;
    }

    /**
     * 只有关注和取关两种操作
     * @return array
     */
    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors = parent::requireAdminOrMySelf($behaviors, ['is-follow', 'delete']);
        $behaviors = parent::requireAdminOrMySelf($behaviors, ['create'], function () {
            $groupId = Yii::$app->request->bodyParams["group_id"];
            $group = FollowGroup::findOne($groupId);
            return $group->user_id == Yii::$app->user->identity->getId();
        });
        return $behaviors;
    }

    public function actionIsFollow() {
        $userId = Yii::$app->request->get('user_id');
        $targetId = Yii::$app->request->get('target_id');
        $follow = Follow::findOne(['user_id' => $userId, 'followed_user_id' => $targetId]);
        return $follow !== null;
    }

    public function actionDelete() {
        $userId = Yii::$app->request->get('user_id');
        $targetId = Yii::$app->request->get('target_id');
        $follow = Follow::findOne(['user_id' => $userId, 'followed_user_id' => $targetId]);
        if ($follow === null) {
            throw new BadRequestHttpException('follow relation does not exist');
        }
        $follow->delete();
    }
}