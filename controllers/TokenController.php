<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/20
 * Time: 15:01
 */

namespace app\controllers;

use app\models\LoginDTO;
use app\models\LoginResult;
use app\models\User;
use app\security\JWTAuth;
use yii\filters\ContentNegotiator;
use yii\filters\Cors;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use Yii;
use yii\web\UnauthorizedHttpException;
use yii\helpers\Json;

/**
 * 鉴权时会将请求转发到TokenController
 * TokenController会将鉴权的部分交由JWTAuth来进行
 *
 * 两个方法：1、登录时提交用户名和密码，生成token
 *          2、注销时删除token
 * Class TokenController
 * @package app\controllers
 */
class TokenController extends Controller {
    private $tokenManager;
    
    public function init() {
        $this->tokenManager = Yii::$container->get('app\security\TokenManager');
    }

    public function behaviors() {
        $behaviors = parent::behaviors();
        unset($behaviors ['authenticator']);
        //添加CORS支持
        $behaviors ['corsFilter'] = [
            'class' => Cors:: className(),
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => true,
            ],
        ];
        $behaviors['authenticator'] = [
            'class' => JWTAuth::className(),
        ];
        //除了这些action其他都会经过Filter
        $behaviors['authenticator']['except'] = ['login'];
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::className(),
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];
        return $behaviors;
    }
    
    //没写rules就不能用load()
    //你调用 save()、insert()、update() 这三个方法时，会自动调用yii\base\Model::validate()方法
    public function actionLogin() {
        Yii::info('开始进行登录认证');
        $loginDTO = new LoginDTO();
        Yii::info(['LoginDTO'=> Yii::$app->request->post()]);
        if (!$loginDTO->load(['LoginDTO'=> Yii::$app->request->post()],'LoginDTO') || !$loginDTO->validate()) {
            throw new BadRequestHttpException('登录信息不完整');
        }
        Yii::info('转换的LoginDTO为：' . $loginDTO);
        //按用户名查询用户
        $user = User::findOne([
            'username' => $loginDTO->username
        ]);
        if ($user === null) {
            throw new NotFoundHttpException($loginDTO->username . ' 不存在');
        }
        if (!Yii::$app->getSecurity()->validatePassword($loginDTO->password, $user->password)) {
            throw new UnauthorizedHttpException('密码错误');
        }
        Yii::info($this->tokenManager);
        $this->tokenManager->deleteToken($user->username);
        //验证成功
        return new LoginResult($user->id,$user->username,$this->tokenManager->createToken($user->username));
    }
}