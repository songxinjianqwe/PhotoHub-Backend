<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/20
 * Time: 19:42
 */

namespace app\controllers\base;


use app\security\JWTAuth;
use yii\filters\ContentNegotiator;
use yii\filters\Cors;
use yii\rest\ActiveController;
use yii\web\Response;

class BaseActiveController extends ActiveController {
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
            'class' => JWTAuth::className()
        ];
        //除了这些action其他都会经过Filter
//        $behaviors['authenticator']['except'] = [];
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::className(),
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];
        return $behaviors;
    }
}