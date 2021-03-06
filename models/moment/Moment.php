<?php

namespace app\models\moment;

use app\cache\service\HotMomentsService;
use app\models\album\Album;
use app\models\message\Message;
use app\models\tag\Tag;
use app\models\user\User;
use Yii;

/**
 * This is the model class for table "moment".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $message_id
 * @property integer $votes
 * @property integer $comments
 * @property integer $forwards
 * @property integer $album_id
 *
 * @property Album $album
 * @property Message $message
 * @property User $user
 * @property MomentTag[] $momentTags
 * @property Tag[] $tags
 * 所有的查询函数都源自 find() 或 findBySql()这两个函数
 * 还有findOne和findAll
 * Moment::findOne()
 * 
 */
class Moment extends \yii\db\ActiveRecord {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'moment';
    }

    /**
     * @inheritDoc
     */
    public function __toString() {
        return 'id:'.$this->id.',message.text'.$this->message->text;
    }
    
    


    /**
     * 重新设置返回的属性，在此进行关联查询
     * $this->getUser相当于$this->user
     * @return array
     */
    public function fields() {
        $fields = parent::fields();
        Yii::info($fields);
        unset($fields['user_id'], $fields['message_id'],$fields['album_id']);
        $fields['user'] = 'user';
        $fields['message'] = 'message';
        $fields['tags'] = 'tags';
        $fields['album'] = 'album';
        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['id'],'safe'],
            [['user_id','message_id'], 'required'],
            [['user_id', 'message_id', 'album_id'], 'integer'],
            [['album_id'], 'exist', 'skipOnError' => true, 'targetClass' => Album::className(), 'targetAttribute' => ['album_id' => 'id']],
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
            'user_id' => 'User ID',
            'message_id' => 'Message ID',
            'album_id' => 'Album ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAlbum() {
        return $this->hasOne(Album::className(), ['id' => 'album_id']);
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
    public function getTags() {
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])->viaTable('moment_tag', ['moment_id' => 'id']);
    }
}
