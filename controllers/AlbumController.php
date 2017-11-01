<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/25
 * Time: 14:22
 */

namespace app\controllers;


use app\controllers\base\BaseActiveController;
use app\models\album\Album;
use app\models\tag\Tag;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * Class AlbumController
 * @package app\controllers
 */
class AlbumController extends BaseActiveController {
    public $modelClass = 'app\models\album\Album';
    private $hotTagsService;

    /**
     * @inheritDoc
     */
    public function init() {
        $this->hotTagsService = Yii::$container->get('app\cache\service\HotTagsService');
    }


    /**
     * @return array
     */
    public function actions() {
        $actions = parent::actions();
        unset($actions['index'], $actions['create'], $actions['update'], $actions['delete']);
        return $actions;
    }

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors = parent::requireNone($behaviors, ['index', 'view']);
        $behaviors = parent::requireAdminOrMySelf($behaviors, ['create', 'update', 'delete']);
        Yii::info($behaviors);
        return $behaviors;
    }

    public function actionIndex() {
        $id = Yii::$app->request->get('user_id');
        $per_page = Yii::$app->request->get('per-page');
        if ($per_page !== null && $per_page == 0) {
            return Yii::createObject([
            'class' => ActiveDataProvider::className(),
            'pagination' => false,
            'query' => Album::find()->where(['user_id' => $id])
        ]);
        }
        return Yii::createObject([
            'class' => ActiveDataProvider::className(),
            'query' => Album::find()->where(['user_id' => $id])
        ]);
    }

    public function actionCreate() {
        $body = Yii::$app->request->post();
        $album = new Album();
        if ($album->load(['Album' => $body], 'Album')) {
            $album->save();
            if ($body['tags'] !== null) {
                $tags = $body['tags'];
                foreach ($tags as $tag) {
                    $this->hotTagsService->saveTag($tag, $album->id, "album");
                }
            }
        } else {
            throw new BadRequestHttpException();
        }
        return $album;
    }

    public function actionUpdate() {
        $body = Yii::$app->request->post();
        $album = Album::findOne($body['id']);
        $oldUserId = $album->user_id;
        if ($album->load(['Album' => $body], 'Album')) {
            //userid不可修改
            if ($oldUserId != $album->user_id) {
                throw new BadRequestHttpException('user id can not be changed');
            }
            $album->update();
        } else {
            throw new BadRequestHttpException();
        }
        if ($body['tags'] === null) {
            $this->hotTagsService->updateTags($album->tags, [], $album->id, "album");
        } else {
            $this->hotTagsService->updateTags($album->tags, $body['tags'], $album->id, "album");
        }
        return Album::findOne($body['id']);
    }

    public function actionDelete() {
        $id = Yii::$app->request->get('id');
        $album = Album::findOne($id);
        if ($album === null) {
            throw new NotFoundHttpException("id not found");
        }
        if ($album->user_id != Yii::$app->user->identity->getId()) {
            throw new ForbiddenHttpException();
        }
        $album->delete();
        //删除对应的tag
        $this->hotTagsService->deleteTags($album->tags, $album->id, 'album');
    }

}