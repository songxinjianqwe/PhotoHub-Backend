<?php

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');

$config = [
    'id' => 'PhotoHub',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'components' => [
        'request' => [
            'enableCookieValidation' => false,
            //Rest专用
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
                'text/json' => 'yii\web\JsonParser',
            ]
        ],
        //全局的日期格式化
        'formatter' => [
            'dateFormat' => 'yyyy-MM-dd',
            'datetimeFormat' => 'yyyy-MM-dd HH:mm:ss',
        ],
        'cache' => [
            'class' => 'yii\redis\Cache',
        ],
        //Redis配置
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'localhost',
            'port' => 6379,
            'database' => 0,
        ],
        //权限相关        
        'user' => [
            'identityClass' => 'app\models\User',
            'enableSession' => false,
            'enableAutoLogin' => false,
            'loginUrl' => ''
        ],
        //Rest专用        
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                //登录注销
                'POST tokens' => 'token/login',
                'DELETE tokens' => 'token/logout',
                //关注分组
                'GET users/<user_id:\d?>/follow_groups' => 'follow-group/index',
                'GET users/<user_id:\d?>/follow_groups/<id:\d?>' => 'follow-group/view',
                'POST users/<user_id:\d?>/follow_groups' => 'follow-group/create',
                'PUT users/<user_id:\d?>/follow_groups' => 'follow-group/update',
                'DELETE users/<user_id:\d?>/follow_groups/<id:\d?>' => 'follow-group/delete',
                //关注
                'POST users/<user_id:\d?>/follows' => 'follow/create',
                'DELETE users/<user_id:\d?>/follows/<id:\d?>' => 'follow/delete',
                //相册
                'GET users/<user_id:\d?>/albums' => 'album/index',
                'GET users/<user_id:\d?>/albums/<id:\d?>' => 'album/view',
                'POST users/<user_id:\d?>/albums' => 'album/create',
                'PUT users/<user_id:\d?>/albums' => 'album/update',
                'DELETE users/<user_id:\d?>/albums/<id:\d?>' => 'album/delete',
                
                ['class' => 'yii\rest\UrlRule', 'controller' => ['user', 'book', 'moment','message']],
            ],
        ],
        //日志
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'exportInterval' => 1,
                    'levels' => YII_DEBUG ? ['error', 'warning', 'trace', 'info'] : ['error'],
                    'logVars' => ['$_GET', '$_POST', '$_SERVER'],
                    'except' => ['yii\web\UrlManager::parseRequest']
                ],
            ],
        ],
        //数据库
        'db' => $db,
    ],
    'params' => $params,
];

//如果是DEBUG，那么会加入debug模块和gii模块
if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
