<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/20
 * Time: 22:04
 */

namespace app\models\user;


class LoginResult {
    public $id;
    public $username;
    public $token;
    public $isAdmin;

    /**
     * @inheritDoc
     */
    public function __construct($id,$username,$token,$isAdmin) {
        $this->id = $id;
        $this->username = $username;
        $this->token = $token;
        $this->isAdmin = $isAdmin;
    }

}