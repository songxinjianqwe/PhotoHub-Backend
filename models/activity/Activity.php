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
    const THUMBNAIL_COUNT = 6;
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
        $fields['thumbnails'] = 'thumbnails';
        $fields['replies'] = 'replies';
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
    public function getReplies() {
        return $this->hasMany(ActivityReply::className(), ['activity_id' => 'id']);
    }


    /**
     * 获得相册对应的缩略图，遍历moments，从中取出text里的图片url，最多取6张
     */
    public function getThumbnails() {
        $result = array();
        Yii::info('获取缩略图');
        Yii::info('当前activity的id为' . $this->id);
        $replies = ActivityReply::find()->where(['activity_id' => $this->id])->all();
        Yii::info('当前replies个数' . count($replies));
        foreach ($replies as $moment) {
            Yii::info('该moment id为' . $moment->id);
            $midResult = array();
            preg_match_all('/\]\((.*?(?<=png|gif|jpg|jpeg))\)/', $moment->message->text, $midResult);
            Yii::info('得到结果');
            Yii::info($midResult[1]);
            $result = array_merge($result, array_slice($midResult[1], 0, static::THUMBNAIL_COUNT - count($result)));
            Yii::info('当前结果个数' . count($result));
            if (count($result) === static::THUMBNAIL_COUNT) {
                return $result;
            }
        }
        return $result;
    }
}
