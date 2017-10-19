<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/19
 * Time: 15:09
 */
namespace app\controllers;

use yii\rest\ActiveController;

class BookController extends ActiveController
{
    public $modelClass = 'app\models\Book';
}
?>