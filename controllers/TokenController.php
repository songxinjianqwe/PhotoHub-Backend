<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/20
 * Time: 15:01
 */

namespace app\controllers;

use app\controllers\base\BaseActiveController;
use app\models\user\LoginDTO;
use app\models\user\LoginResult;
use app\models\user\User;
use app\security\JWTAuth;
use yii\filters\ContentNegotiator;
use yii\filters\Cors;
use yii\rest\ActiveController;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use Yii;
use yii\web\UnauthorizedHttpException;

/**
 * 鉴权时会将请求转发到TokenController
 * TokenController会将鉴权的部分交由JWTAuth来进行
 *
 * 两个方法：1、登录时提交用户名和密码，生成token
 *          2、注销时删除token
 * Class TokenController
 * @package app\controllers
 */
class TokenController extends BaseActiveController {
    public $modelClass = '';
    private $tokenManager;

    public function init() {
        $this->tokenManager = Yii::$container->get('app\security\TokenManager');
    }

    public function actions() {
        $actions = parent::actions();
        unset($actions['index'], $actions['view'], $actions['create'], $actions['update'], $actions['delete']);
        Yii::info($actions);
        return $actions;
    }

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors = parent::requireNone($behaviors, ['create']);
        return $behaviors;
    }

    //没写rules就不能用load()
    //你调用 save()、insert()、update() 这三个方法时，会自动调用yii\base\Model::validate()方法
    public function actionCreate() {
        Yii::info('开始进行登录认证');
        $loginDTO = new LoginDTO();
        Yii::info(['LoginDTO' => Yii::$app->request->post()]);
        if (!$loginDTO->load(['LoginDTO' => Yii::$app->request->post()], 'LoginDTO') || !$loginDTO->validate()) {
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
        return new LoginResult($user->id, $user->username, $this->tokenManager->createToken($user->username));
    }

    public function actionDelete() {
        Yii::info('删除token :' . Yii::$app->user->identity->username);
        $this->tokenManager->deleteToken(Yii::$app->user->identity->username);
    }

    public function actionCos() {
        $appid = "1252651195";
        $bucket = "photohub";
        $secret_id = "AKIDhMNH0bdMTOLBUfhXEoCXh8QbaO1Xm1yo";
        $secret_key = "mRziJo7E9uTq10ngOvHRPCnDRYYMyOqD";
        $expired = time() + 3600;
        $current = time();
        $rdm = rand();
        $multi_effect_signature = 'a=' . $appid . '&b=' . $bucket . '&k=' . $secret_id . '&e=' . $expired . '&t=' . $current . '&r=' . $rdm . '&f=';
        $result = base64_encode(hash_hmac('SHA1', $multi_effect_signature, $secret_key, true) . $multi_effect_signature);
        Yii::$app->response->format = Response::FORMAT_RAW;
        return $result;
    }
}