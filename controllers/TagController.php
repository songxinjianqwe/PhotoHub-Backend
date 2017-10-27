<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/27
 * Time: 8:05
 */

namespace app\controllers;


use app\constant\PageConstant;
use app\controllers\base\BaseActiveController;
use Yii;

class TagController extends BaseActiveController {
    public $modelClass = '';
    private $service;

    public function init() {
        $this->service = Yii::$container->get('app\cache\service\HotTagsService');
    }

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors = parent::requireNone($behaviors,['hot']);
        return $behaviors;
    }


    public function actions() {
        $actions = parent::actions();
        unset($actions['index'], $actions['view'], $actions['create'], $actions['update'], $actions['delete']);
        return $actions;
    }

    public function actionHot() {
        $page = Yii::$app->request->get('page');
        $per_page = Yii::$app->request->get('per_page');
        return $this->service->getHotTags($page === null ? PageConstant::page : $page, $per_page === null ? PageConstant::per_page : $per_page);
    }
}