<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/26
 * Time: 11:41
 */

namespace app\controllers;


use app\constant\PageConstant;
use app\controllers\base\BaseActiveController;
use app\models\activity\Activity;
use Yii;
use yii\rest\CreateAction;
use yii\web\NotFoundHttpException;

class ActivityController extends BaseActiveController {
    public $modelClass = 'app\models\activity\Activity';
    private $latestActivitiesService;
    private $hotActivitiesService;

    /**
     * @inheritDoc
     */
    public function init() {
        $this->latestActivitiesService = Yii::$container->get('app\cache\service\LatestActivitiesService');
        $this->hotActivitiesService = Yii::$container->get('app\cache\service\HotActivitiesService');
    }

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors = parent::requireNone($behaviors, ['index', 'view', 'hot', 'latest']);
        $behaviors = parent::requireAdminOrMySelf($behaviors, ['create', 'update']);
        $behaviors = parent::requireCustomOrAdmin($behaviors, ['delete'], function () {
            $id = Yii::$app->request->get('id');
            $activity = Activity::findOne($id);
            return $activity->user_id == Yii::$app->user->identity->getId();
        });
        return $behaviors;
    }

    public function actions() {
        $actions = parent::actions();
        unset($actions['create'], $actions['delete']);
        return $actions;
    }

    public function actionCreate() {
        $action = new CreateAction('create', $this, ['modelClass' => $this->modelClass]);
        $model = $action->run();
        $this->latestActivitiesService->createActivity($model->id);
        $this->hotActivitiesService->createActivity($model->id);
        return $model;
    }

    public function actionDelete() {
        $id = Yii::$app->request->get('id');
        $activity = Activity::findOne($id);
        if ($activity === null) {
            throw new NotFoundHttpException('id not found');
        }
        $activity->delete();
        $this->latestActivitiesService->removeActivity($activity->id);
        $this->hotActivitiesService->removeActivity($activity->id);
    }

    public function actionHot() {
        $page = Yii::$app->request->get('page');
        $per_page = Yii::$app->request->get('per_page');
        return $this->hotActivitiesService->show($page === null ? PageConstant::page : $page, $per_page === null ? PageConstant::per_page : $per_page);

    }

    public function actionLatest() {
        $page = Yii::$app->request->get('page');
        $per_page = Yii::$app->request->get('per_page');
        return $this->latestActivitiesService->show($page === null ? PageConstant::page : $page, $per_page === null ? PageConstant::per_page : $per_page);
    }


}