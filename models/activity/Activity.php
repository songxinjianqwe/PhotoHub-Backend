<?php

namespace app\models\activity;

use app\models\message\Message;
use app\models\user\User;
use Yii;

/**
 * This is the model class for table "activity".
 *
 * @property integer $id
 * @property string $create_time
 * @property integer $create_user_id
 * @property string $title
 * @property integer $message_id
 * @property integer $replies
 *
 * @property Message $message
 * @property User $createUser
 * @property ActivityReply[] $activityReplies
 */
class Activity extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'activity';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time'], 'safe'],
            [['create_user_id', 'message_id', 'replies'], 'integer'],
            [['title'], 'string'],
            [['message_id'], 'exist', 'skipOnError' => true, 'targetClass' => Message::className(), 'targetAttribute' => ['message_id' => 'id']],
            [['create_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['create_user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'create_time' => 'Create Time',
            'create_user_id' => 'Create User ID',
            'title' => 'Title',
            'message_id' => 'Message ID',
            'replies' => 'Replies',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessage()
    {
        return $this->hasOne(Message::className(), ['id' => 'message_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreateUser()
    {
        return $this->hasOne(User::className(), ['id' => 'create_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActivityReplies()
    {
        return $this->hasMany(ActivityReply::className(), ['activity_id' => 'id']);
    }
}
