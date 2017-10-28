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
    private $hotTagsService;
    private $tagTalentService;

    public function init() {
        $this->hotTagsService = Yii::$container->get('app\cache\service\HotTagsService');
        $this->tagTalentService = Yii::$container->get('app\cache\service\TagTalentService');
    }

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors = parent::requireNone($behaviors, ['hot','talent']);
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
        return $this->hotTagsService->getHotTags($page === null ? PageConstant::page : $page, $per_page === null ? PageConstant::per_page : $per_page);
    }

    public function actionTalent() {
        $tagId = Yii::$app->request->get('id');
        $page = Yii::$app->request->get('page');
        $per_page = Yii::$app->request->get('per_page');
        return $this->tagTalentService->show($tagId,$page === null ? PageConstant::page : $page, $per_page === null ? PageConstant::per_page : $per_page);
    }
}