<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/25
 * Time: 16:39
 */

namespace app\controllers;


use app\controllers\base\BaseActiveController;
use app\models\message\Image;
use app\models\message\Message;
use app\models\message\Video;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

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
        $behaviors = parent::requireAdminOrMySelf($behaviors, ['create','update']);
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
            foreach ($body['images'] as $img) {
                $image = new Image();
                $image->message_id = $message->id;
                $image->url = $img;
                $image->save();
            }
            foreach ($body['videos'] as $vdo) {
                $video = new Video();
                $video->message_id = $message->id;
                $video->url = $vdo;
                $video->save();
            }
        } elseif (!$message->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }
        return $message;
    }


    public function actionUpdate() {
        $body = Yii::$app->request->post();
        Yii::info($body);
        if ($body['images'] === null) {
            $body['images'] = [];
        }
        if ($body['videos'] === null) {
            $body['videos'] = [];
        }

        $message = Message::findOne($body['id']);
        if ($message === null) {
            throw new NotFoundHttpException('message_id not found');
        }
        //先赋值text
        $message->text = $body['text'];
        self::updateImages($body['images'], $message);
        self::updateVideos($body['videos'], $message);
        $message->save();
        return Message::findOne($body['id']);
    }

    /**
     * 批量更新images
     * 规定新增的只需要有一个url属性，不变的必须有id属性，修改的两个属性都要有，删除的不列出
     * @param $images
     * @param $message
     */
    private function updateImages($images, $message) {
        Yii::info('新的images:');
        foreach ($images as $img) {
            Yii::info($img);
        }
        Yii::info('旧的images:' . implode(";", $message->images));
        $containedImages = [];
        foreach ($images as $newImg) {
            //新增的情况
            if ($newImg['id'] === null) {
                $image = new Image();
                $image->message_id = $message->id;
                $image->url = $newImg['url'];
                Yii::info('保存新image  :' . $image->url);
                $image->save();
            } else {
                foreach ($message->images as &$oldImg) {
                    //只要id相等，说明一定不是删除
                    if ($newImg['id'] === $oldImg->id) {
                        //修改的情况
                        if ($newImg['url'] !== null && $newImg['url'] !== $oldImg->url) {
                            $image = new Image();
                            $image->id = $newImg['id'];
                            $image->message_id = $message->id;
                            $image->url = $newImg['url'];
                            Yii::info('修改image :' . $image->url);
                            $image->update();
                        }
                        Yii::info('旧数组中去掉 id:' . $oldImg->id);
                        //这个数组里的元素都不会被删除
                        array_push($containedImages, $oldImg->id);
                    }
                }
            }
        }
        foreach ($message->images as &$img) {
            if (!in_array($img->id, $containedImages)) {
                Yii::info('删除image: id:' . $img->id);
                $img->delete();
            }
        }
    }

    /**
     * 批量更新videos
     * @param $videos
     * @param $message
     */
    private function updateVideos($videos, $message) {
        Yii::info('新的videos:');
        foreach ($videos as $video) {
            Yii::info($video);
        }
        Yii::info('旧的videos:' . implode(";", $message->videos));
        $containedVideos = [];
        foreach ($videos as $newVideo) {
            //新增的情况
            if ($newVideo['id'] === null) {
                $video = new Video();
                $video->message_id = $message->id;
                $video->url = $newVideo['url'];
                Yii::info('保存新video  :' . $video->url);
                $video->save();
            } else {
                foreach ($message->videos as &$oldVideo) {
                    //只要id相等，说明一定不是删除
                    if ($newVideo['id'] === $oldVideo->id) {
                        //修改的情况
                        if ($newVideo['url'] !== null && $newVideo['url'] !== $oldVideo->url) {
                            $video = new Video();
                            $video->id = $newVideo['id'];
                            $video->message_id = $message->id;
                            $video->url = $newVideo['url'];
                            Yii::info('修改video :' . $video->url);
                            $video->update();
                        }
                        Yii::info('旧数组中去掉 id:' . $oldVideo->id);
                        //这个数组里的元素都不会被删除
                        array_push($containedVideos, $oldVideo->id);
                    }
                }
            }
        }
        foreach ($message->videos as &$video) {
            if (!in_array($video->id, $containedVideos)) {
                Yii::info('删除video: id:' . $video->id);
                $video->delete();
            }
        }
    }

    public function actionDelete() {
        $id = Yii::$app->request->get('id');
        $message = Message::findOne($id);
        if($message === null){
            throw new NotFoundHttpException("id not found");
        }
        if($message->user_id != Yii::$app->user->identity->getId()){
            throw new ForbiddenHttpException();
        }
        foreach ($message->images as $img) {
            $img->delete();
        }
        foreach ($message->videos as $video) {
            $video->delete();
        }
        $message->delete();
    }

}