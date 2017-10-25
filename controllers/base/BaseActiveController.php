<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/20
 * Time: 19:42
 */

namespace app\controllers\base;


use app\security\JWTAuth;
use Yii;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\filters\Cors;
use yii\rest\ActiveController;
use yii\web\Response;

class BaseActiveController extends ActiveController {
    //加这个可以返回分页信息
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

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
            'except' => []
        ];
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::className(),
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];
        return $behaviors;
    }

    protected function requireNone($behaviors, $actions) {
        $behaviors['authenticator']['except'] = array_merge($behaviors['authenticator']['except'], $actions);
        return $behaviors;
    }

    protected function requireAdmin($behaviors, $actions) {
        if ($behaviors['access'] == null) {
            $behaviors['access'] = [
                'class' => AccessControl::className(),
                'only' => [],
                'rules' => [],
            ];
        }
        $behaviors['access']['only'] = array_merge($behaviors['access']['only'], $actions);
        array_push($behaviors['access']['rules'],
            [
                'actions' => $actions,
                'allow' => true,
                'matchCallback' => function ($rule, $action) {
                    foreach (Yii::$app->user->identity->userRoles as $role) {
                        if ($role->role_name === 'ROLE_ADMIN') {
                            return true;
                        }
                    }
                    return false;
                }
            ]);
        return $behaviors;
    }


    protected function requireAdminOrMySelf($behaviors, $actions) {
        if ($behaviors['access'] == null) {
            $behaviors['access'] = [
                'class' => AccessControl::className(),
                'only' => [],
                'rules' => [],
            ];
        }
        $behaviors['access']['only'] = array_merge($behaviors['access']['only'], $actions);
        array_push($behaviors['access']['rules'],
            [
                'actions' => $actions,
                'allow' => true,
                'matchCallback' => function ($rule, $action) {
                    //拿到user_id
                    if (Yii::$app->request->isPost) {
                        $userId = Yii::$app->request->bodyParams['user_id'];
                        if ($userId === null) {
                            $userId = Yii::$app->request->bodyParams['id'];
                        }
                        Yii::info('post请求，请求体中取出id:' . $userId);
                    } elseif (Yii::$app->request->isGet || Yii::$app->request->isDelete || Yii::$app->request->isPut) {
                        $userId = Yii::$app->request->get('user_id');
                        if ($userId === null) {
                            $userId = Yii::$app->request->get('id');
                        }
                        Yii::info('get/put/delete请求，从url中取出id:' . $userId);
                    }
                    //进行判断
                    if (Yii::$app->user->identity->getId() == $userId) {
                        return true;
                    }
                    foreach (Yii::$app->user->identity->userRoles as &$role) {
                        if ($role->role_name === 'ROLE_ADMIN') {
                            return true;
                        }
                    }
                    return false;
                }
            ]);
        return $behaviors;
    }
}