<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/26
 * Time: 15:13
 */

namespace app\controllers;


use app\controllers\base\BaseActiveController;
use app\models\follow\FollowGroup;
use app\models\message\action\Comment;
use app\models\message\action\Forward;
use app\models\message\action\Vote;
use app\models\message\Message;
use app\models\moment\Moment;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class ActionController extends BaseActiveController {
    public $modelClass = 'app\models\message\Message';

    /**
     * 只有关注和取关两种操作
     * @return array
     */
    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors = parent::requireCustomOrAdmin($behaviors, ['un-vote'], function () {
            $voteId = Yii::$app->request->get('id');
            $vote = Vote::findOne($voteId);
            if ($vote === null) {
                throw new NotFoundHttpException('vote id not found');
            }
            $messageId = Yii::$app->request->get('message_id');
            return $vote->message_id == $messageId && $vote->user_id == Yii::$app->user->identity->getId();
        });

        $behaviors = parent::requireCustomOrAdmin($behaviors, ['un-comment'], function () {
            $commentId = Yii::$app->request->get('id');
            $comment = Comment::findOne($commentId);
            if ($comment === null) {
                throw new NotFoundHttpException('comment id not found');
            }
            $messageId = Yii::$app->request->get('message_id');
            return $comment->message_id == $messageId && $comment->user_id == Yii::$app->user->identity->getId();
        });

        return $behaviors;
    }

    public function actions() {
        $actions = parent::actions();
        unset($actions['index'], $actions['view'], $actions['create'], $actions['update'], $actions['delete']);
        return $actions;
    }

    public function actionVote() {
        $messageId = Yii::$app->request->get('message_id');
        $message = Message::findOne($messageId);
        if ($message === null) {
            throw new NotFoundHttpException('message_id not found');
        }
        $vote = new Vote();
        $vote->message_id = $messageId;
        $vote->user_id = Yii::$app->user->identity->getId();
        $vote->save();

        $moment = Moment::findOne([
            'message_id' => $messageId
        ]);
        if ($moment !== null) {
            $moment->votes++;
            $moment->update();
        }
        return $vote;
    }

    public function actionUnVote() {
        Vote::findOne(Yii::$app->request->get('id'))->delete();
    }

    public function actionComment() {
        $messageId = Yii::$app->request->get('message_id');
        $message = Message::findOne($messageId);
        if ($message === null) {
            throw new NotFoundHttpException('message_id not found');
        }
        $text = Yii::$app->request->bodyParams['text'];
        if ($text === null || strlen($text) === 0) {
            throw new BadRequestHttpException('text can not be empty string');
        }
        $comment = new Comment();
        $comment->user_id = Yii::$app->user->identity->getId();
        $comment->message_id = $messageId;
        $comment->text = $text;
        $comment->save();

        $moment = Moment::findOne([
            'message_id' => $messageId
        ]);
        if ($moment !== null) {
            $moment->comments++;
            $moment->update();
        }

        return $comment;
    }

    public function actionUnComment() {
        Comment::findOne(Yii::$app->request->get('id'))->delete();
    }

    public function actionForward() {
        $messageId = Yii::$app->request->get('message_id');
        $message = Message::findOne($messageId);
        if ($message === null) {
            throw new NotFoundHttpException('message_id not found');
        }
        $platform = Yii::$app->request->bodyParams['platform'];
        if ($platform === null || strlen($platform) === 0) {
            throw new BadRequestHttpException('platform can not be empty string');
        }
        $forward = new Forward();
        $forward->user_id = Yii::$app->user->identity->getId();
        $forward->message_id = $messageId;
        $forward->platform = $platform;
        $forward->save();

        $moment = Moment::findOne([
            'message_id' => $messageId
        ]);
        if ($moment !== null) {
            $moment->forwards++;
            $moment->update();
        }
        return $forward;
    }
}