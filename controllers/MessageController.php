<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/25
 * Time: 16:39
 */

namespace app\controllers;


use app\controllers\base\BaseActiveController;
use app\models\message\action\Comment;
use app\models\message\action\Forward;
use app\models\message\action\Vote;
use app\models\message\Image;
use app\models\message\Message;
use app\models\message\Video;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 *
 * 注意：所有包含message的实体（activity，activity_reply，moment）在增改删之前，都要先增改删对应的message
 * 这一点由前端来保证
 * Class MessageController
 * @package app\controllers
 */
class MessageController extends BaseActiveController {
    public $modelClass = 'app\models\message\Message';

    /**
     * @return array
     */
    public function actions() {
        $actions = parent::actions();
        unset($actions['index'], $actions['view'], $actions['create'], $actions['update'], $actions['delete']);
        return $actions;
    }

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors = parent::requireNone($behaviors, ['vote', 'comment', 'forward']);
        $behaviors = parent::requireAdminOrMySelf($behaviors, ['create', 'update']);
        return $behaviors;
    }

    /**
     * 增加message的同时更新video和image表
     */
    public function actionCreate() {
        $body = Yii::$app->request->post();
        Yii::info($body);
        $message = new Message();
        $message->text = $body['text'];
        $message->user_id = Yii::$app->user->identity->getId();
        if ($message->save()) {
            return $message;
        } elseif (!$message->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }
    }
    
    public function actionUpdate() {
        $body = Yii::$app->request->post();
        Yii::info($body);

        $message = Message::findOne($body['id']);
        if ($message === null) {
            throw new NotFoundHttpException('message_id not found');
        }
        //先赋值text
        $message->text = $body['text'];
        $message->update();
        return $message;
    }

    public function actionDelete() {
        $id = Yii::$app->request->get('id');
        $message = Message::findOne($id);
        if ($message === null) {
            throw new NotFoundHttpException("id not found");
        }
        if ($message->user_id != Yii::$app->user->identity->getId()) {
            throw new ForbiddenHttpException();
        }
        $message->delete();
    }
}

