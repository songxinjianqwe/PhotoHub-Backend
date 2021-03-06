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
            'loginUrl' => '',
        ],
        //Rest专用        
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                //登录注销
                'POST tokens' => 'token/create',
                'DELETE tokens' => 'token/delete',
                'OPTIONS tokens' => 'token/options',
                'GET tokens/cos' => 'token/cos',
                'OPTIONS tokens/cos' => 'token/options',
                //关注分组
                'GET users/<user_id:\d+>/follow_groups' => 'follow-group/index',
                'GET users/<user_id:\d+>/follow_groups/<id:\d+>' => 'follow-group/view',
                'POST users/<user_id:\d+>/follow_groups' => 'follow-group/create',
                'PUT users/<user_id:\d+>/follow_groups/<id:\d+>' => 'follow-group/update',
                'DELETE users/<user_id:\d+>/follow_groups/<id:\d+>' => 'follow-group/delete',
                'OPTIONS users/<user_id:\d+>/follow_groups' => 'follow-group/options',
                'OPTIONS users/<user_id:\d+>/follow_groups/<id:\d+>' => 'follow-group/options',
                //关注
                'POST users/<user_id:\d+>/follows' => 'follow/create',
                'DELETE users/<user_id:\d+>/follows/<target_id:\d+>' => 'follow/delete',
                'GET users/<user_id:\d+>/follows/<target_id:\d+>' => 'follow/is-follow',
                'OPTIONS users/<user_id:\d+>/follows' => 'follow/options',
                'OPTIONS users/<user_id:\d+>/follows/<target_id:\d+>' => 'follow/options',
                //点赞
                'POST messages/<message_id:\d+>/vote' => 'action/vote',
                'DELETE messages/<message_id:\d+>/vote/<id:\d+>' => 'action/un-vote',
                'OPTIONS messages/<message_id:\d+>/vote' => 'action/options',
                'OPTIONS messages/<message_id:\d+>/vote/<id:\d+>' => 'action/options',
                //评论
                'POST messages/<message_id:\d+>/comment' => 'action/comment',
                'DELETE messages/<message_id:\d+>/comment/<id:\d+>' => 'action/un-comment',
                'OPTIONS messages/<message_id:\d+>/comment' => 'action/options',
                'OPTIONS messages/<message_id:\d+>/comment/<id:\d+>' => 'action/options',
                //转发
                'POST messages/<message_id:\d+>/forward' => 'action/forward',
                'OPTIONS messages/<message_id:\d+>/forward' => 'action/options',
                //活动回复
                'GET activities/<activity_id:\d+>/replies' => 'activity-reply/index',
                'GET activities/<activity_id:\d+>/replies/<id:\d+>' => 'activity-reply/view',
                'POST activities/<activity_id:\d+>/replies' => 'activity-reply/create',
                'DELETE activities/<activity_id:\d+>/replies/<id:\d+>' => 'activity-reply/delete',
                'OPTIONS activities/<activity_id:\d+>/replies' => 'activity-reply/options',
                'OPTIONS activities/<activity_id:\d+>/replies/<id:\d+>' => 'activity-reply/options',
                //热门动态，分页显示
                'GET moments/hot' => 'moment/hot',
                'OPTIONS moments/hot' => 'moment/options',
                //按Tag查询的热门动态
                'GET moments/hot/by_tag/<id:\d+>' => 'moment/hot-by-tag',
                'OPTIONS moments/hot/by_tag/<id:\d+>' => 'moment/options',
                //按Tag查询的最新动态
                'GET moments/latest/by_tag/<id:\d+>' => 'moment/latest-by-tag',
                'OPTIONS moments/latest/by_tag/<id:\d+>' => 'moment/options',
                //热门活动
                'GET activities/hot' => 'activity/hot',
                'OPTIONS activities/hot' => 'activity/options',
                //最新活动
                'GET activities/latest' => 'activity/latest',
                'OPTIONS activities/latest' => 'activity/options',
                //Feed
                'GET users/<id:\d+>/feed' => 'feed/index',
                'OPTIONS users/<id:\d+>/feed' => 'feed/options',
                //热门标签
                'GET tags/hot' => 'tag/hot',
                'GET tags/<id:\d+>' => 'tag/view',
                'OPTIONS tags/hot' => 'tag/options',
                'OPTIONS tags/<id:\d+>' => 'tag/options',
                //用户与标签
                'GET tags/<tag_id:\d+>/users/<user_id:\d+>' => 'tag/is-like',
                'POST tags/<tag_id:\d+>/users/<user_id:\d+>' => 'tag/user-like',
                'DELETE tags/<tag_id:\d+>/users/<user_id:\d+>' => 'tag/user-un-like',
                'OPTIONS tags/<tag_id:\d+>/users/<user_id:\d+>'=> 'tag/options',
                //标签达人
                'GET tags/talents/<id:\d+>' => 'tag/talent',
                'POST tags/talents/batch' => 'tag/talent-batch',
                'OPTIONS tags/talents/batch' => 'tag/options',
                'OPTIONS tags/talents/<id:\d+>' => 'tag/options',
                //模糊搜索
                'GET tags/search' => 'tag/search',
                'OPTIONS tags/search' => 'tag/options',
                'GET users/<username:\w+>/duplication' => 'user/username-duplicated',
                'GET users/search' => 'user/search',
                'OPTIONS users/search' => 'user/options',
                //恢复用户
                'PUT users/<id:\d+>/recover' => 'user/recover',
                'OPTIONS users/<id:\d+>/recover' => 'user/options',
                // /<controller> 四种方法
                ['class' => 'yii\rest\UrlRule', 'controller' => ['user', 'book', 'moment', 'message', 'activity','album']],
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
                    'logVars' => ['$_GET', '$_POST', '$_SERVER']
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
