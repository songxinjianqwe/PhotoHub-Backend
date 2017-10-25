<?php

namespace app\models\message;

use app\models\activity\Activity;
use app\models\message\action\Comment;
use app\models\message\action\Forward;
use app\models\message\action\Vote;
use app\models\moment\Moment;
use Yii;

/**
 * This is the model class for table "message".
 *
 * @property integer $id
 * @property string $create_time
 * @property string $text
 *
 * @property Activity[] $activities
 * @property ActivityReply[] $activityReplies
 * @property Comment[] $comments
 * @property Forward[] $forwards
 * @property Image[] $images
 * @property Moment[] $moments
 * @property Video[] $videos
 * @property Vote[] $votes
 */
class Message extends \yii\db\ActiveRecord {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'message';
    }

    /**
     * @inheritDoc
     */
    public function fields() {
        $fields = parent::fields();
        $fields['images'] = 'images';
        $fields['videos'] = 'videos';
        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['create_time'], 'safe'],
            [['text'], 'string'],
        ];
    }

    /**
     * 1、后面的是前面的注释，在rules验证的时候，如果报错，会把此处的后面的内容显示出来
     * 2、hint作用，即表单中用户插入数据时，提示用户该字段该填什么内容
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'create_time' => 'Create Time',
            'text' => 'Text',
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments() {
        return $this->hasMany(Comment::className(), ['message_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getForwards() {
        return $this->hasMany(Forward::className(), ['message_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImages() {
        return $this->hasMany(Image::className(), ['message_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVideos() {
        return $this->hasMany(Video::className(), ['message_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVotes() {
        return $this->hasMany(Vote::className(), ['message_id' => 'id']);
    }
}
