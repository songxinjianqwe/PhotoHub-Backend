<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/19
 * Time: 15:07
 */

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class Book
 * @package app\models
 */
class Book extends ActiveRecord
{

    public static function tableName()
    {
        return 'book';
    }
}

?>