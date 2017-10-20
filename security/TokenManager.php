<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/20
 * Time: 15:04
 */

namespace app\security;


use Firebase\JWT\JWT;
use yii\web\UnauthorizedHttpException;
use Yii;

class TokenManager {
    //过期时间为一天
    const expireTime = 86400;
    const key = '12asdiqwej@3!@#(18u3e21j30 1230!N@#(';
    private $cacheManager;

    public function __construct() {
        $this->cacheManager = Yii::$container->get('app\cache\RedisCacheManager');
    }

    public function createToken($username) {
        $curr = time();
        $token = array(
            "username" => $username,
            "iat" => $curr,
            "exp" => $curr + static::expireTime
        );
        $jwt = JWT::encode($token, static::key, 'HS256');
        Yii::info('JWT生成的token:' . $jwt);
        $this->cacheManager->putWithExpireTime($username, $jwt, static::expireTime);
        return $jwt;
    }

    public function checkToken($token) {
        if ($token === null) {
            throw new UnauthorizedHttpException('token错误');
        }
        try {
            $decoded = JWT::decode($token, static::key, array('HS256'));
        } catch (Exception $e) {
            throw new UnauthorizedHttpException('token错误');
        }
        $decoded_array = (array)$decoded;
        Yii::info('JWT解码后的token:' . implode(',', $decoded_array));
        if (!is_array($decoded_array) || empty($decoded_array)) {
            throw new UnauthorizedHttpException('token错误');
        }
        $cachedToken = $this->cacheManager->get($decoded_array['username']);
        if ($cachedToken === null || $cachedToken !== $token) {
            throw new UnauthorizedHttpException('token错误');
        }
        return $decoded_array['username'];
    }

    public function deleteToken($username) {
        $this->cacheManager->delete($username);
    }
}