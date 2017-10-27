<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/27
 * Time: 8:15
 */

namespace app\cache;


use Yii;

class RedisZSetManager {
    private $prefix;

    /**
     * @inheritDoc
     */
    public function __construct($prefix) {
        $this->prefix = $prefix;
    }

    public function addElement($key, $score, $zsetKey = '') {
        //如果zsetKey不为空，那么zsetName为prefix.zsetkey
        //否则zsetName为$prefix
        $zsetName = $this->prefix . $zsetKey !== '' ? '.' . $zsetKey : '';
        Yii::$app->redis->zAdd($zsetName, $score, $key);
    }

    public function removeElement($key, $zsetKey = '') {
        $zsetName = $this->prefix . $zsetKey !== '' ? '.' . $zsetKey : '';
        Yii::$app->redis->zDelete($zsetName, $key);
    }

    public function changeScore($key, $increment, $zsetKey = '') {
        $zsetName = $this->prefix . $zsetKey !== '' ? '.' . $zsetKey : '';
        Yii::$app->redis->zIncrBy($zsetName, $increment, $key);
    }

    public function indexDesc($page = 1, $per_page = 5, $zsetKey = '') {
        $zsetName = $this->prefix . $zsetKey !== '' ? '.' . $zsetKey : '';
        return Yii::$app->redis->zRevRange($zsetName, ($page - 1) * $per_page, $page * $per_page);
    }

}