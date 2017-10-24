<?php

namespace app\models\message\action;

use app\models\message\Message;
use app\models\user\User;
use Yii;

/**
 * This is the model class for table "forward".
 *
 * @property integer $id
 * @property integer $message_id
 * @property integer $user_id
 * @property string $create_time
 * @property string $platform
 *
 * @property User $user
 * @property Message $message
 */
class Forward extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'forward';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['message_id', 'user_id'], 'integer'],
            [['create_time'], 'safe'],
            [['platform'], 'string'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['message_id'], 'exist', 'skipOnError' => true, 'targetClass' => Message::className(), 'targetAttribute' => ['message_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'message_id' => 'Message ID',
            'user_id' => 'User ID',
            'create_time' => 'Create Time',
            'platform' => 'Platform',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
