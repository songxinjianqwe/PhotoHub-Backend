<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/25
 * Time: 9:37
 */

namespace app\controllers;


use app\controllers\base\BaseActiveController;
use app\models\moment\Moment;
use app\models\tag\Tag;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;

class MomentController extends BaseActiveController {
    public $modelClass = 'app\models\moment\Moment';


    /**
     * @return array
     */
    public function behaviors() {
        $behaviors = parent::behaviors();
        //允许GET /moments 和 GET /moments/{id}
        $behaviors = parent::requireNone($behaviors, ['index', 'view']);
        $behaviors = parent::requireAdminOrMySelf($behaviors, ['create', 'update', 'delete']);
        return $behaviors;
    }

    /**
     * @return array
     */
    public function actions() {
        $actions = parent::actions();
        unset($actions['index'], $actions['create']);
        return $actions;
    }

    // /moments?user_id={user_id}
    public function actionIndex() {
        $id = Yii::$app->request->get('user_id');
        if ($id === null || $id === '') {
            throw new BadRequestHttpException("user_id not given");
        }
        Yii::info("MomentController:   actionIndex  id:" . $id);
        return Yii::createObject([
            'class' => ActiveDataProvider::className(),
            'query' => Moment::find()->where(['user_id' => $id])
        ]);
    }

    public function actionCreate() {
        $body = Yii::$app->request->post();
        $moment = new Moment();
        if ($moment->load(['Moment' => $body], 'Moment')) {
            $moment->save();

            //先保存moment
            //对tag进行保存
            if ($body['tags'] !== null) {
                $tags = $body['tags'];
                foreach ($tags as $tag) {
                    Tag::saveTag($tag, $moment->id, "moment");
                }
            }
        } else {
            throw new BadRequestHttpException();
        }
        return $moment;
    }

    //TODO
    public function actionUpdate() {
        
    }
    //TODO
    public function actionDelete() {

    }
}