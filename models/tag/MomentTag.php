<?php

namespace app\models\tag;

use app\models\moment\Moment;
use Yii;

/**
 * This is the model class for table "moment_tag".
 *
 * @property integer $tag_id
 * @property integer $moment_id
 *
 * @property Moment $moment
 * @property Tag $tag
 */
class MomentTag extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'moment_tag';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tag_id', 'moment_id'], 'integer'],
            [['tag_id', 'moment_id'], 'unique', 'targetAttribute' => ['tag_id', 'moment_id'], 'message' => 'The combination of Tag ID and Moment ID has already been taken.'],
            [['moment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Moment::className(), 'targetAttribute' => ['moment_id' => 'id']],
            [['tag_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tag::className(), 'targetAttribute' => ['tag_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tag_id' => 'Tag ID',
            'moment_id' => 'Moment ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMoment()
    {
        return $this->hasOne(Moment::className(), ['id' => 'moment_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTag()
    {
        return $this->hasOne(Tag::className(), ['id' => 'tag_id']);
    }
}
