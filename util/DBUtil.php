<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/28
 * Time: 22:10
 */

namespace app\util;


use Yii;

class DBUtil {
    public static function orderByField($raw, $dbResult,$fieldName) {
        Yii::info('数组长度为'.count($dbResult));
        $result = array();
        foreach ($dbResult as $item) {
            $result[array_search($item[$fieldName], $raw)] = $item;
        }
        Yii::info('DBUtil');
        foreach($result as $r){
            Yii::info($r->id);
        }
        return $result;
    }
}   