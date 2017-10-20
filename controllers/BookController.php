<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/19
 * Time: 15:09
 */

namespace app\controllers;

use app\controllers\base\BaseActiveController;

class BookController extends BaseActiveController {
    public $modelClass = 'app\models\Book';
    //index -> /books
}