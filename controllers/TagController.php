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
use app\models\tag\Tag;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class TagController extends BaseActiveController {
    public $modelClass = '';
    private $hotTagsService;
    private $tagTalentService;
    private $hotMomentsByTagService;

    public function init() {
        $this->hotTagsService = Yii::$container->get('app\cache\service\HotTagsService');
        $this->tagTalentService = Yii::$container->get('app\cache\service\TagTalentService');
        $this->hotMomentsByTagService = Yii::$container->get('app\cache\service\HotMomentsByTagService');
    }

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors = parent::requireNone($behaviors, ['hot', 'talent', 'search','talent-batch']);
        return $behaviors;
    }


    public function actions() {
        $actions = parent::actions();
        unset($actions['index'], $actions['view'], $actions['create'], $actions['update'], $actions['delete']);
        return $actions;
    }

    public function actionSearch() {
        $keyword = Yii::$app->request->get('keyword');
        if ($keyword === null) {
            throw new BadRequestHttpException();
        }
        $tag = Tag::find()->where(['like', 'name', $keyword])->one();
        if ($tag === null) {
            throw new NotFoundHttpException('keyword can not match any tags');
        }
        return $this->hotMomentsByTagService->show($tag->id, PageConstant::page, PageConstant::per_page);
    }

    public function actionHot() {
        $page = Yii::$app->request->get('page');
        $per_page = Yii::$app->request->get('per-page');
        return $this->hotTagsService->getHotTags($page === null ? PageConstant::page : $page, $per_page === null ? PageConstant::per_page : $per_page);
    }

    public function actionTalent() {
        $tagId = Yii::$app->request->get('id');
        $page = Yii::$app->request->get('page');
        $per_page = Yii::$app->request->get('per-page');
        return $this->tagTalentService->show($tagId, $page === null ? PageConstant::page : $page, $per_page === null ? PageConstant::per_page : $per_page);
    }
    
    public function actionTalentBatch(){
        $tagIds = Yii::$app->request->bodyParams['tagIds'];
        $result = array();
        foreach($tagIds as $tagId){
            $tag = Tag::findOne($tagId);
            $result[$tag->name] = $this->tagTalentService->show($tagId,PageConstant::page,PageConstant::per_page)->items;
        }
        return $result;
    }
}