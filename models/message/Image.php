<?php

namespace app\models\message;

use Yii;

/**
 * This is the model class for table "image".
 *
 * @property integer $id
 * @property integer $message_id
 * @property string $url
 *
 * @property Message $message
 */
class Image extends \yii\db\ActiveRecord {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'image';
    }

    /**
     * @inheritDoc
     */
    public function __toString() {
        return '[id:' . $this->id . ', url:' . $this->url . ']';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['message_id'], 'integer'],
            [['url'], 'string'],
            [['message_id'], 'exist', 'skipOnError' => true, 'targetClass' => Message::className(), 'targetAttribute' => ['message_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'message_id' => 'Message ID',
            'url' => 'Url',
        ];
    }
}
