<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/20
 * Time: 20:08
 */

namespace app\models;




use yii\base\Model;

/**
 * Model属性必须是public！！！
 * Class LoginDTO
 * @package app\model
 */
class LoginDTO extends Model {
    public $username;
    public $password;
    /**
     * @inheritDoc
     */
    public function __toString() {
        return '[username:'.$this->username.',password:'.$this->password.']';
    }
    
    public function rules() {
        return [
            // username,password属性必须有值
            [['username', 'password'], 'required'],
        ];
    }
}