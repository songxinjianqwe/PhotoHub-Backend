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
 * @property integer $user_id
 * @property string $title
 * @property integer $message_id
 * @property integer $replies
 *
 * @property Message $message
 * @property User $user
 * @property ActivityReply[] $activityReplies
 */
class Activity extends \yii\db\ActiveRecord {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'activity';
    }

    public function fields() {
        $fields = parent::fields();
        unset($fields['message_id']);
        $fields['message'] = 'message';
        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['create_time'], 'safe'],
            [['user_id', 'message_id'], 'integer'],
            [['title'], 'string'],
            [['message_id'], 'exist', 'skipOnError' => true, 'targetClass' => Message::className(), 'targetAttribute' => ['message_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'create_time' => 'Create Time',
            'user_id' => 'User ID',
            'title' => 'Title',
            'message_id' => 'Message ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessage() {
        return $this->hasOne(Message::className(), ['id' => 'message_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser() {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActivityReplies() {
        return $this->hasMany(ActivityReply::className(), ['activity_id' => 'id']);
    }
}
