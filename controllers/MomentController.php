<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/25
 * Time: 9:37
 */

namespace app\controllers;


use app\constant\PageConstant;
use app\controllers\base\BaseActiveController;
use app\models\moment\Moment;
use app\models\tag\Tag;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\IntegrityException;
use yii\web\BadRequestHttpException;
use yii\web\ConflictHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class MomentController extends BaseActiveController {
    public $modelClass = 'app\models\moment\Moment';
    private $feedService;
    private $hotTagsService;
    private $hotMomentsService;
    private $latestMomentsByTagService;
    private $hotMomentsByTagService;
    private $tagTalentService;

    public function init() {
        $this->feedService = Yii::$container->get('app\cache\service\FeedService');
        $this->hotTagsService = Yii::$container->get('app\cache\service\HotTagsService');
        $this->hotMomentsService = Yii::$container->get('app\cache\service\HotMomentsService');
        $this->latestMomentsByTagService = Yii::$container->get('app\cache\service\LatestMomentsByTagService');
        $this->hotMomentsByTagService = Yii::$container->get('app\cache\service\HotMomentsByTagService');
        $this->tagTalentService = Yii::$container->get('app\cache\service\TagTalentService');
    }

    /**
     * @return array
     */
    public function behaviors() {
        $behaviors = parent::behaviors();
        //允许GET /moments 和 GET /moments/{id}
        $behaviors = parent::requireNone($behaviors, ['index', 'view', 'hot', 'latest-by-tag', 'hot-by-tag']);
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
        $userId = Yii::$app->request->get('user_id');
        if ($userId !== null && $userId !== '') {
            Yii::info("MomentController:   actionIndex  user id:" . $userId);
            return Yii::createObject([
                'class' => ActiveDataProvider::className(),
                'query' => Moment::find()->where(['user_id' => $userId])->orderBy('id desc')
            ]);
        }
        $albumId = Yii::$app->request->get('album_id');
        if ($albumId !== null || $albumId !== '') {
            Yii::info("MomentController:   actionIndex  albumn id:" . $albumId);
            return Yii::createObject([
                'class' => ActiveDataProvider::className(),
                'query' => Moment::find()->where(['album_id' => $albumId])->orderBy('id desc')
            ]);
        }
        throw new BadRequestHttpException('user id or album id not given');
    }

    /**
     * @return Moment
     * @throws BadRequestHttpException
     * @throws ConflictHttpException
     */
    public function actionCreate() {
        $body = Yii::$app->request->post();
        $moment = new Moment();
        if ($moment->load(['Moment' => $body], 'Moment')) {
            try {
                $moment->save();
            } catch (IntegrityException $e) {
                throw new ConflictHttpException('moment id 不可重复');
            }
            //先保存moment
            //对tag进行保存
            if ($body['tags'] !== null) {
                $tags = $body['tags'];
                foreach ($tags as $tag) {
                    $this->hotTagsService->saveTag($tag, $moment->id, "moment");
                }
            }
        } else {
            throw new BadRequestHttpException();
        }
        Yii::info('增加moment');
        $this->feedService->addMoment($moment->user_id, $moment->id);
        $this->hotMomentsService->createMoment($moment->id);
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
            try {
                $moment->update();
            } catch (IntegrityException $e) {
                throw new BadRequestHttpException('moment id 不可重复');
            }
        } else {
            throw new BadRequestHttpException();
        }
        if ($body['tags'] === null) {
            $this->hotTagsService->updateTags($moment->tags, [], $moment->id, "moment");
        } else {
            $this->hotTagsService->updateTags($moment->tags, $body['tags'], $moment->id, "moment");
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
        $referTimes = $this->hotMomentsService->getMomentScore($moment->id);
        $this->hotTagsService->deleteTags($moment->tags, $moment->id, 'moment');
        $this->feedService->removeMoment($moment->user_id, $moment->id);
        $this->hotMomentsService->removeMoment($moment->id);
        $this->tagTalentService->removeMoment($moment, $referTimes);
    }

    public function actionHot() {
        $page = Yii::$app->request->get('page');
        $per_page = Yii::$app->request->get('per-page');
        return $this->hotMomentsService->show($page === null ? PageConstant::page : $page, $per_page === null ? PageConstant::per_page : $per_page);
    }

    public function actionLatestByTag() {
        $id = Yii::$app->request->get('id');
        $page = Yii::$app->request->get('page');
        $per_page = Yii::$app->request->get('per-page');
        return $this->latestMomentsByTagService->show($id, $page === null ? PageConstant::page : $page, $per_page === null ? PageConstant::per_page : $per_page);
    }

    public function actionHotByTag() {
        $id = Yii::$app->request->get('id');
        $page = Yii::$app->request->get('page');
        $per_page = Yii::$app->request->get('per-page');
        return $this->hotMomentsByTagService->show($id, $page === null ? PageConstant::page : $page, $per_page === null ? PageConstant::per_page : $per_page);
    }

}