<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/20
 * Time: 15:03
 */

namespace app\cache;
use Yii;

class RedisCacheManager {
    /**
     * 将键值对放到redis中
     * @param $key
     * @param $value
     * @param $expireTime
     */
    public function putWithExpireTime($key,$value,$expireTime){
        Yii::info('放入cache : key:'.$key.'  value:'.$value);
        Yii::$app->redis->setex($key, $expireTime, $value);
    }

    /**
     * @param $key
     * @return null|object
     */
    public function get($key){
        Yii::info('从cache中取出 : key:'.$key.'  value:'.Yii::$app->redis->get($key));
        return Yii::$app->redis->get($key);
    }
    
    public function delete($key){
        Yii::$app->redis->del($key);
    }
}