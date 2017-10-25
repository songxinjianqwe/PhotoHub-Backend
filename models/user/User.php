<?php

namespace app\models\user;

use app\models\activity\Activity;
use app\models\album\Album;
use app\models\follow\Follow;
use app\models\follow\FollowGroup;
use app\models\message\action\Comment;
use app\models\message\action\Forward;
use app\models\message\action\Vote;
use app\models\moment\Moment;
use app\models\tag\Tag;
use Yii;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $username
 * @property string $password
 * @property string $reg_time
 * @property string $avatar
 * @property integer $followers
 *
 * @property Activity[] $activities
 * @property Album[] $albums
 * @property Comment[] $comments
 * @property Follow[] $follows
 * @property FollowGroup[] $followGroups
 * @property Forward[] $forwards
 * @property Moment[] $moments
 * @property Role[] $roles
 * @property Vote[] $votes
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface {
    
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'user';
    }

    /**
     * @inheritDoc
     */
    public function fields() {
        $fields = parent::fields();
        unset($fields['password']);
        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['username', 'password'], 'required'],
            [['username', 'password', 'avatar'], 'string'],
            [['reg_time'], 'safe'],
            [['followers'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'password' => 'Password',
            'reg_time' => 'Reg Time',
            'avatar' => 'Avatar',
            'followers' => 'Followers',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActivities() {
        return $this->hasMany(Activity::className(), ['create_user_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAlbums() {
        return $this->hasMany(Album::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments() {
        return $this->hasMany(Comment::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFollows() {
        return $this->hasMany(Follow::className(), ['followed_user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFollowGroups() {
        return $this->hasMany(FollowGroup::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getForwards() {
        return $this->hasMany(Forward::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMoments() {
        return $this->hasMany(Moment::className(), ['user_id' => 'id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserRoles() {
        return $this->hasMany(Role::className(), ['id' => 'role'])->viaTable('user_role', ['user' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVotes() {
        return $this->hasMany(Vote::className(), ['user_id' => 'id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTags() {
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])->viaTable('user_tag', ['user_id' => 'id']);
    }


    /**
     * Finds an identity by the given ID.
     * @param string|int $id the ID to be looked for
     * @return IdentityInterface the identity object that matches the given ID.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentity($id) {
        return static::findOne(['id' => $id]);
    }

    /**
     * Finds an identity by the given token.
     * @param mixed $token the token to be looked for
     * @param mixed $type the type of the token. The value of this parameter depends on the implementation.
     * For example, [[\yii\filters\auth\HttpBearerAuth]] will set this parameter to be `yii\filters\auth\HttpBearerAuth`.
     * @return IdentityInterface the identity object that matches the given token.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentityByAccessToken($token, $type = null) {
    }

    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return string|int an ID that uniquely identifies a user identity.
     */
    public function getId() {
        return $this->getPrimaryKey();
    }

    /**
     * Returns a key that can be used to check the validity of a given identity ID.
     *
     * The key should be unique for each individual user, and should be persistent
     * so that it can be used to check the validity of the user identity.
     *
     * The space of such keys should be big enough to defeat potential identity attacks.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @return string a key that is used to check the validity of a given identity ID.
     * @see validateAuthKey()
     */
    public function getAuthKey() {
    }

    /**
     * Validates the given auth key.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @param string $authKey the given auth key
     * @return bool whether the given auth key is valid.
     * @see getAuthKey()
     */
    public function validateAuthKey($authKey) {
    }

}
