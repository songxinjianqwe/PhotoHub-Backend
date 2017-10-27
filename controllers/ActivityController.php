<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/26
 * Time: 11:41
 */

namespace app\controllers;


use app\controllers\base\BaseActiveController;
use app\models\activity\Activity;
use Yii;

class ActivityController extends BaseActiveController {
    public $modelClass = 'app\models\activity\Activity';

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors = parent::requireNone($behaviors, ['index', 'view']);
        $behaviors = parent::requireAdminOrMySelf($behaviors, ['create', 'update']);
        $behaviors = parent::requireCustomOrAdmin($behaviors, ['delete'], function () {
            $id = Yii::$app->request->get('id');
            $activity = Activity::findOne($id);
            return $activity->user_id == Yii::$app->user->identity->getId();
        });
        return $behaviors;
    }
    
    public function actionHot(){
        
    }

}