<?php

namespace app\models\follow;

use app\models\user\User;
use Yii;

/**
 * This is the model class for table "follow_group".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $group_name
 *
 * @property Follow[] $follows
 * @property User $user
 */
class FollowGroup extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'follow_group';
    }

    /**
     * @inheritDoc
     */
    public function fields() {
        $fields = parent::fields();
        unset($fields['user_id']);
        $fields['follows'] = 'follows';
        return $fields; 
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['group_name'], 'string'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'group_name' => 'Group Name',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFollows()
    {
        return $this->hasMany(Follow::className(), ['group_id' => 'id']);
    }
}
