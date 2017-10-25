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

    /**
     * 增加message的同时更新video和image表
     */
    public function actionCreate() {
        $body = Yii::$app->request->post();
        Yii::info($body);
        $message = new Message();
        $message->text = $body['text'];
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
        //先赋值text
        $message->text = $body['text'];
        self::updateImages($body['images'], $message);
        self::updateVideos($body['videos'], $message);
        $message->save();
        return $message;
    }

    /**
     * 批量更新images
     * 规定新增的只需要有一个url属性，不变的必须有id属性，修改的两个属性都要有，删除的不列出
     * @param $images
     * @param $message
     */
    private function updateImages($images, $message) {
        foreach ($images as $newImg) {
            
            foreach ($message->images as $oldImg){
                
            }
        }
    }

    /**
     * 批量更新videos
     * @param $videos
     * @param $message
     */
    private function updateVideos($videos, $message) {

    }

    public function actionDelete() {

    }


}