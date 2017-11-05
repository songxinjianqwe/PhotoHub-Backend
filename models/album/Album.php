<?php

namespace app\models\album;

use app\models\moment\Moment;
use app\models\tag\Tag;
use app\models\user\User;
use Yii;

/**
 * This is the model class for table "album".
 *
 * @property integer $id
 * @property string $name
 * @property integer $user_id
 * @property string $description
 * @property string $create_time
 *
 * @property User $user
 * @property AlbumTag[] $albumTags
 * @property Tag[] $tags
 * @property Moment[] $moments
 */
class Album extends \yii\db\ActiveRecord {
    const THUMBNAIL_COUNT = 6;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'album';
    }

    public function fields() {
        $fields = parent::fields();
        unset($fields['user_id']);
        $fields['tags'] = 'tags';
        $fields['user'] = 'user';
        $fields['thumbnails'] = 'thumbnails';
        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['name', 'description'], 'string'],
            [['user_id'], 'integer'],
            [['create_time'], 'safe'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'user_id' => 'User ID',
            'description' => 'Description',
            'create_time' => 'Create Time',
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
    public function getTags() {
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])->viaTable('album_tag', ['album_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMoments() {
        return $this->hasMany(Moment::className(), ['album_id' => 'id']);
    }

    /**
     * 获得相册对应的缩略图，遍历moments，从中取出text里的图片url，最多取6张
     */
    public function getThumbnails() {
        $result = array();
        Yii::info('获取缩略图');
        Yii::info('当前album的id为' . $this->id);
        $moments = Moment::find()->where(['album_id' => $this->id])->all();
        Yii::info('当前moments个数'.count($moments));
        foreach ($moments as $moment) {
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
