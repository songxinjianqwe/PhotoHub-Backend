<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/20
 * Time: 14:44
 */

namespace app\security;

use app\models\user\User;
use Yii;
use yii\filters\auth\AuthMethod;

class JWTAuth extends AuthMethod {
    private $tokenManager;
    public $realm = 'api';

    public function init() {
        $this->tokenManager = Yii::$container->get('app\security\TokenManager');
    }
    /**
     * Authenticates the current user.
     * 返回的$identity可以通过Yii::$app->user->identity获取
     * @inheritdoc
     */
    public function authenticate($user, $request, $response) {
        Yii::info('开始进行认证');
        $authHeader = $request->getHeaders()->get('Authentication');
        if ($authHeader !== null) {
            Yii::info('含有Authentication请求头');
            $username = $this->tokenManager->checkToken($authHeader);
            Yii::info('Token认证成功');
            $identity = User::findOne([
                'username' => $username
            ]);
            if ($identity === null) {
                Yii::info('获取访问用户信息成功');
                $this->handleFailure($response);
            }
            Yii::info('通过Filter!!!');
            $user->identity = $identity;
            return $identity;
        }
        Yii::info('不含有Authentication请求头，认证失败');
        return null;
    }
}