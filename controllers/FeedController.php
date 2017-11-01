<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/27
 * Time: 14:23
 */

namespace app\controllers;


use app\cache\service\FeedService;
use app\constant\PageConstant;
use app\controllers\base\BaseActiveController;
use Yii;

class FeedController extends BaseActiveController {
    public $modelClass = '';
    private $feedService;

    public function init() {
        $this->feedService = Yii::$container->get('app\cache\service\FeedService');
    }

    public function actions() {
        $actions = parent::actions();
        unset($actions['index'], $actions['view'], $actions['create'], $actions['update'], $actions['delete']);
        return $actions;
    }

    public function actionIndex() {
        $id = Yii::$app->request->get('id');
        $page = Yii::$app->request->get('page');
        $per_page = Yii::$app->request->get('per-page');
        return $this->feedService->show($id,$page === null ? PageConstant::page:$page,$per_page === null ? PageConstant::per_page:$per_page);
    }

}