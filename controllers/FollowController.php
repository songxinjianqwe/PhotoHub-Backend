<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/25
 * Time: 14:36
 */

namespace app\controllers;


use app\controllers\base\BaseActiveController;

class FollowController extends BaseActiveController {
    public $modelClass = 'app\models\follow\follow';
}