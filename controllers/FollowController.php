<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/25
 * Time: 14:36
 */

namespace app\controllers;


use app\controllers\base\BaseActiveController;

class FollowController extends BaseActiveController {
    public $modelClass = 'app\models\follow\follow';

    public function actions() {
        $actions = parent::actions();
        unset($actions['index'], $actions['view'], $actions['update']);
        return $actions;
    }

    /**
     * 只有关注和取关两种操作
     * @return array
     */
    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors = parent::requireAdminOrMySelf($behaviors, ['create', 'delete']);
        return $behaviors;
    }
    //TODO
    public function actionCreate(){
            
    }
    //TODO
    public function actionDelete(){
        
    }
    
}