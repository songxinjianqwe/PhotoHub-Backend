<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/20
 * Time: 14:04
 */

namespace app\models;


use yii\db\ActiveRecord;

class User extends ActiveRecord {
    /**
     * @inheritDoc
     */
    public static function tableName() {
        return 'user';
    }
    
    
}