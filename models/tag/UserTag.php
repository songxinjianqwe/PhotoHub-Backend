<?php

namespace app\models\tag;

use app\models\user\User;
use Yii;

/**
 * This is the model class for table "user_tag".
 *
 * @property integer $tag_id
 * @property integer $user_id
 *
 * @property User $user
 * @property Tag $tag
 */
class UserTag extends \yii\db\ActiveRecord {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'user_tag';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['tag_id', 'user_id'], 'integer'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['tag_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tag::className(), 'targetAttribute' => ['tag_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'tag_id' => 'Tag ID',
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
    public function getTag() {
        return $this->hasOne(Tag::className(), ['id' => 'tag_id']);
    }
}
