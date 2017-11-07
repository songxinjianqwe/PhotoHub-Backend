<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/25
 * Time: 14:04
 */

namespace app\controllers;


use app\controllers\base\BaseActiveController;
use app\models\follow\FollowGroup;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;

class FollowGroupController extends BaseActiveController {
    public $modelClass = 'app\models\follow\FollowGroup';

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors = parent::requireAdminOrMySelf($behaviors, ['view', 'create', 'update', 'delete']);
        return $behaviors;
    }

    /**
     * @return array
     */
    public function actions() {
        $actions = parent::actions();
        unset($actions['index'],$actions['delete']);
        return $actions;
    }


    public function actionIndex() {
        $id = Yii::$app->request->get('user_id');
        if ($id === null || $id === '') {
            throw new BadRequestHttpException("user_id not given");
        }
        return Yii::createObject([
            'class' => ActiveDataProvider::className(),
            'query' => FollowGroup::find()->where(['user_id' => $id])
        ]);
    }
    
    public function actionDelete(){
        $id = Yii::$app->request->get('id');
        $group = FollowGroup::findOne($id);
        foreach($group->follows as $follow){
            $follow->delete();
        }
        $group->delete();
    }
}