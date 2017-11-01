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
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'album';
    }

    public function fields() {
        $fields = parent::fields();
        $fields['tags'] = 'tags';
        $fields['moments'] = 'moments';
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
}
