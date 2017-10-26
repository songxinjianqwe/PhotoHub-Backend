<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/20
 * Time: 14:09
 */

namespace app\controllers;


use app\controllers\base\BaseActiveController;
use app\models\album\Album;
use app\models\follow\Follow;
use app\models\follow\FollowGroup;
use app\models\tag\Tag;
use app\models\tag\UserTag;
use app\models\user\User;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

class UserController extends BaseActiveController {
    public $modelClass = 'app\models\user\User';

    /**
     * 在这里对认证和授权进行限制
     * 如果需要重写，那么需要设置 $behaviors['authenticator']['except']和unset($actions['create'])
     * 并实现函数actionCreate
     * 如果需要对某个action设置管理员权限，需要设置$behaviors = parent::requireAdminRule($behaviors,'index')
     * 如果需要对某个action设置管理员或本人权限，需要设置$behaviors = parent::requireAdminOrMySelfRule($behaviors, 'view', explode('/',Yii::$app->request->pathInfo)[1])
     * @return array
     */
    public function behaviors() {
        $behaviors = parent::behaviors();
        //访问 POST /users不需要任何权限
        $behaviors = parent::requireNone($behaviors, ['create']);
        //访问/users 需要管理员权限
        $behaviors = parent::requireAdmin($behaviors, ['index']);
        //修改用户信息 需要管理员或本人权限
        $behaviors = parent::requireAdminOrMySelf($behaviors, ['update']);
        Yii::info('最终的behaviors');
        Yii::info($behaviors);
        return $behaviors;
    }

    /**
     * 如果想自定义某些action，那么需要重写actions方法对希望自定义的action先禁用
     * @return array
     */
    public function actions() {
        $actions = parent::actions();
        unset($actions['create']);
        return $actions;
    }

    /**
     * 注册
     */
    public function actionCreate() {
        $body = Yii::$app->request->post();
        $user = new User();
        if (!$user->load(['User' => $body], 'User') || !$user->validate()) {
            throw new BadRequestHttpException('注册信息不完整');
        }
        if (User::findOne([
                'username' => $user->username
            ]) !== null) {
            //Conflict
            throw new HttpException(409);
        }
        Yii::info('对密码进行加密');
        $user->password = Yii::$app->getSecurity()->generatePasswordHash($user->password);
        $user->save();

        //同时创建一个默认相册和默认的关注分组
        $album = new Album();
        $album->name = '默认相册';
        $album->description = $user->username . '的默认相册';
        $album->user_id = $user->id;
        $album->save();

        $followGroup = new FollowGroup();
        $followGroup->user_id = $user->id;
        $followGroup->group_name = '默认分组';
        $followGroup->save();

        //再次更新user
        $user->default_album_id = $album->id;
        $user->default_follow_group_id = $followGroup->id;
        $user->update();
        $user->password = '';

        //保存用户的tags
        $tags = $body['tags'];
        if ($tags !== null) {
            foreach ($tags as $tag) {
                Tag::saveTag($tag,$user->id,"user");
            }
        }
        
        $follows = $body['follows'];
        if ($follows !== null) {
            foreach ($follows as $followedUserId) {
                $fol = new Follow();
                $fol->followed_user_id = $followedUserId;
                $fol->group_id = $user->default_follow_group_id;
                $fol->save();
                
                $followedUser = User::findOne($followedUserId);
                $followedUser->followers++;
                $followedUser->update();
            }
        }
        return $user;
    }
}