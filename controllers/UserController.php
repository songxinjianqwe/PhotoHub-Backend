<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/20
 * Time: 14:09
 */

namespace app\controllers;


use app\controllers\base\BaseActiveController;
use app\models\user\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

class UserController extends BaseActiveController {
    public $modelClass = 'app\models\user\User';

    /**
     * 自行添加不需要登录的action
     * @return array
     */
    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['except'] = ['create'];
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['index'],
            'rules' => [
                [
                    'actions' => ['index'],
                    'allow' => true,
                    'matchCallback' => function ($rule, $action) {
//                        return 
                    }
                ],
            ],
        ];
        return $behaviors;
    }

    /**
     * 如果想自定义某些action，那么需要重写actions方法对希望自定义的action先禁用
     * @return array
     */
    public function actions() {
        $actions = parent::actions();
        unset($actions['create']);
        return $actions;
    }

    /**
     * 注册
     */
    public function actionCreate() {
        $user = new User();
        if (!$user->load(['User' => Yii::$app->request->post()], 'User') || !$user->validate()) {
            throw new BadRequestHttpException('注册信息不完整');
        }
        if (User::findOne([
                'username' => $user->username
            ]) !== null) {
            //Conflict
            throw new HttpException(409);
        }
        Yii::info('对密码进行加密');
        $user->password = Yii::$app->getSecurity()->generatePasswordHash($user->password);
        $user->save();
        $user->password = '';
        return $user;
    }
}