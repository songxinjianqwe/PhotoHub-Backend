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
            ],
        ];
        $behaviors['authenticator'] = [
            'class' => JWTAuth::className(),
            'except' => ['options']
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


    protected function requireAdminOrMySelf($behaviors, $actions, $extraCheck = null) {
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
                'matchCallback' => function ($rule, $action) use ($extraCheck) {
                    Yii::info('开始校验权限...');
                    Yii::info('当前访问的url为：' . Yii::$app->request->url);
                    //拿到user_id
                    if (Yii::$app->request->isPost || Yii::$app->request->isPut) {
                        //POST请求中的user_id一定是放在请求体里的
                        //因为新增用户的请求不需要校验，所以请求体里的用户id的名字一定是user_id
                        //如果没带user_id，那么是不被允许的

                        //PUT请求中的user_id会出现在url和请求体两个地方
                        //我们要求这两个地方都要有user_id，并且值相同
                        //只有PUT /users这个请求中的请求体的用户id的名字是id，所以特殊判断即可

                        //使用正则：/前面要加\转义，前后加/
                        if (preg_match('/^\/users\/\d+$/', Yii::$app->request->url)) {
                            Yii::info('是更新用户信息的请求，特殊处理');
                            $userIdInBody = Yii::$app->request->bodyParams['id'];
                            $userIdInUrl = Yii::$app->request->get('id');
                            if ($userIdInBody != $userIdInUrl) {
                                return false;
                            }
                            $userId = $userIdInBody;
                        } else {
                            //其他更新请求，用户id以user_id的名字一定出现在请求体中，有可能以user_id的名字出现在url中
                            $userIdInBody = Yii::$app->request->bodyParams['user_id'];
                            $userIdInUrl = Yii::$app->request->get('user_id');
                            //不可全为空
                            if ($userIdInBody === null && $userIdInUrl === null) {
                                return false;
                            }
                            //不为空且不相等
                            if ($userIdInBody != null && $userIdInUrl != null && $userIdInUrl != $userIdInBody) {
                                return false;
                            }
                            $userId = $userIdInBody === null ? $userIdInUrl : $userIdInBody;
                        }
                    } else if (Yii::$app->request->isGet || Yii::$app->request->isDelete) {
                        $userId = Yii::$app->request->get('user_id');
                        if ($userId === null && preg_match('/\/users\/\d+/', Yii::$app->request->url)) {
                            $userId = Yii::$app->request->get('id');
                        } else {
                            Yii::info('未取得id');
                        }
                        Yii::info('get/put/delete请求，从url中取出id:' . $userId);
                    }
                    Yii::info('已经计算出userId:' . $userId);
                    if ($extraCheck !== null && !call_user_func($extraCheck)) {
                        Yii::info('extraCheck未通过');
                        return false;
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

    protected function requireCustomOrAdmin($behaviors, $actions, $checkCallback) {
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
                'matchCallback' => function ($rule, $action) use ($checkCallback) {
                    //注意，这里的判断逻辑是如果checkCallback通过，即通过
                    //如果不通过，那么如果是admin的话，也通过
                    //只有都不通过，才不通过
                    if ($checkCallback !== null && call_user_func($checkCallback)) {
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