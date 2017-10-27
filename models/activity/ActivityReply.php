<?php

namespace app\models\activity;

use app\models\message\Message;
use app\models\user\User;
use Yii;

/**
 * This is the model class for table "activity_reply".
 *
 * @property integer $id
 * @property integer $activity_id
 * @property integer $message_id
 * @property integer $user_id
 *
 * @property User $user
 * @property Message $message
 * @property Activity $activity
 */
class ActivityReply extends \yii\db\ActiveRecord {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'activity_reply';
    }

    public function fields() {
        $fields = parent::fields();
        unset($fields['message_id'],$fields['user_id']);
        $fields['message'] = 'message';
        $fields['user'] = 'user';
        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['activity_id', 'message_id', 'user_id'], 'integer'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['message_id'], 'exist', 'skipOnError' => true, 'targetClass' => Message::className(), 'targetAttribute' => ['message_id' => 'id']],
            [['activity_id'], 'exist', 'skipOnError' => true, 'targetClass' => Activity::className(), 'targetAttribute' => ['activity_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'activity_id' => 'Activity ID',
            'message_id' => 'Message ID',
            'user_id' => 'User ID',
        ];
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
    public function getMessage() {
        return $this->hasOne(Message::className(), ['id' => 'message_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActivity() {
        return $this->hasOne(Activity::className(), ['id' => 'activity_id']);
    }
}
