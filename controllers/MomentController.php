<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/25
 * Time: 9:37
 */

namespace app\controllers;


use app\controllers\base\BaseActiveController;
use app\models\moment\Moment;
use app\models\tag\Tag;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class MomentController extends BaseActiveController {
    public $modelClass = 'app\models\moment\Moment';
    private $feedService;

    public function init() {
        $this->feedService = Yii::$container->get('app\cache\service\FeedService');
    }

    /**
     * @return array
     */
    public function behaviors() {
        $behaviors = parent::behaviors();
        //允许GET /moments 和 GET /moments/{id}
        $behaviors = parent::requireNone($behaviors, ['index', 'view']);
        $behaviors = parent::requireAdminOrMySelf($behaviors, ['create', 'update']);
        return $behaviors;
    }

    /**
     * @return array
     */
    public function actions() {
        $actions = parent::actions();
        unset($actions['index'], $actions['create'], $actions['update'], $actions['delete']);
        return $actions;
    }

    // /moments?user_id={user_id}
    public function actionIndex() {
        $id = Yii::$app->request->get('user_id');
        if ($id === null || $id === '') {
            throw new BadRequestHttpException("user_id not given");
        }
        Yii::info("MomentController:   actionIndex  id:" . $id);
        return Yii::createObject([
            'class' => ActiveDataProvider::className(),
            'query' => Moment::find()->where(['user_id' => $id])
        ]);
    }

    /**
     * 同步更新tag-talent,feed
     * @return Moment
     * @throws BadRequestHttpException
     */
    public function actionCreate() {
        $body = Yii::$app->request->post();
        $moment = new Moment();
        if ($moment->load(['Moment' => $body], 'Moment')) {
            $moment->save();
            //先保存moment
            //对tag进行保存
            if ($body['tags'] !== null) {
                $tags = $body['tags'];
                foreach ($tags as $tag) {
                    Tag::saveTag($tag, $moment->id, "moment");
                }
            }
        } else {
            throw new BadRequestHttpException();
        }
        Yii::info('增加moment');
        $this->feedService->addMoment($moment->user_id, $moment->id);
        return $moment;
    }

    /**
     * 同步更新tag-talent,feed
     * 注意tags是一个对象数组而非字符串数组
     * @return Moment
     * @throws BadRequestHttpException
     */
    public function actionUpdate() {
        $body = Yii::$app->request->post();
        $moment = Moment::findOne($body['id']);
        $oldUserId = $moment->user_id;
        if ($moment->load(['Moment' => $body], 'Moment')) {
            //userid不可修改
            if ($oldUserId != $moment->user_id) {
                throw new BadRequestHttpException('user id can not be changed');
            }
            $moment->update();
        } else {
            throw new BadRequestHttpException();
        }
        if ($body['tags'] === null) {
            Tag::updateTags($moment->tags, [], $moment->id, "moment");
        } else {
            Tag::updateTags($moment->tags, $body['tags'], $moment->id, "moment");
        }
        return Moment::findOne($body['id']);
    }

    /**
     * 同步更新tag-talent,feed
     * 注意删除message之后才能删除对应的moment
     * 前端要分别删除，先删除message，再删除moment
     */
    public function actionDelete() {
        $id = Yii::$app->request->get('id');
        $moment = Moment::findOne($id);
        if ($moment === null) {
            throw new NotFoundHttpException("id not found");
        }
        if ($moment->user_id != Yii::$app->user->identity->getId()) {
            throw new ForbiddenHttpException();
        }
        $moment->delete();
        //删除对应的tag
        Tag::deleteTags($moment->tags, $moment->id, 'moment');
        $this->feedService->removeMoment($moment->user_id, $moment->id);
    }

    public function actionHot() {
        
    }
}