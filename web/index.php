<?php

//这两行表示现在正处于dev状态，当部署到生产环境时需要将下面两行注释掉
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');
// 注册 Composer 自动加载器
require(__DIR__ . '/../vendor/autoload.php');
// 包含 Yii 类文件
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
// 加载应用配置
$config = require(__DIR__ . '/../config/web.php');

// 创建、配置、运行一个应用
(new yii\web\Application($config))->run();
