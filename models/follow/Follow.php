<?php

namespace app\models\follow;

use app\models\user\User;
use Yii;

/**
 * This is the model class for table "follow".
 *
 * @property integer $id
 * @property integer $followed_user_id
 * @property string $create_time
 * @property integer $group_id
 *
 * @property FollowGroup $group
 * @property User $followedUser
 */
class Follow extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'follow';
    }

    /**
     * @inheritDoc
     */
    public function fields() {
        $fields = parent::fields();
        unset($fields['followed_user_id']);
        $fields['followedUser'] = 'followedUser';
        return $fields;
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['followed_user_id', 'group_id'], 'integer'],
            [['create_time'], 'safe'],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => FollowGroup::className(), 'targetAttribute' => ['group_id' => 'id']],
            [['followed_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['followed_user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'followed_user_id' => 'Followed User ID',
            'create_time' => 'Create Time',
            'group_id' => 'Group ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFollowedUser()
    {
        return $this->hasOne(User::className(), ['id' => 'followed_user_id']);
    }
}
