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

    public function getSetName($zsetKey) {
        return $this->prefix . ($zsetKey != '' ? ('.' . $zsetKey) : (''));
    }

    public function addElement($key, $score, $zsetKey = '') {
        //如果zsetKey不为空，那么zsetName为prefix.zsetkey
        //否则zsetName为$prefix
        Yii::info('$this->prefix ' . $this->prefix);
        Yii::info('$zsetkey ' . $zsetKey);
        Yii::info('$key ' . $key);

        Yii::info('$zsetName:' . $this->getSetName($zsetKey));
        Yii::info('Yii::$app->redis->zAdd($zsetName, $score, $key)');
        Yii::$app->redis->zadd($this->getSetName($zsetKey), $score, $key);
    }

    public function removeElement($key, $zsetKey = '') {
        Yii::info('Yii::$app->redis->zrem($zsetName, $key)');
        Yii::info('$zsetName:' . $this->getSetName($zsetKey));
        Yii::info('$key ' . $key);
        Yii::$app->redis->zrem($this->getSetName($zsetKey), $key);
    }

    public function changeScore($key, $increment, $zsetKey = '') {
        Yii::info('Yii::$app->redis->zIncrBy($zsetName, $increment, $key)');
        Yii::$app->redis->zincrby($this->getSetName($zsetKey), $increment, $key);
    }

    public function indexDesc($page, $per_page, $zsetKey = '') {
        Yii::info('Yii::$app->redis->zRevRange($zsetName, ($page - 1) * $per_page, $page * $per_page)   ');
        Yii::info('$page:' . $page);
        Yii::info('$per_page:' . $per_page);
        $data = Yii::$app->redis->zrevrange($this->getSetName($zsetKey), ($page - 1) * $per_page, $page * $per_page);
        Yii::info('data:' . implode(';', $data));
        return $data;
    }
}