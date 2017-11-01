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
    public static function orderByField($raw, $dbResult, $fieldName) {
        $result = array();
        foreach ($raw as $rawId) {
            foreach ($dbResult as $item) {
                if ($item[$fieldName] == $rawId) {
                    array_push($result, $item);
                }
            }
        }
        return $result;
    }
}   