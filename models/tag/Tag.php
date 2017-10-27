<?php

namespace app\models\tag;

use app\cache\service\HotTagsService;
use Yii;

/**
 * This is the model class for table "tag".
 *
 * @property integer $id
 * @property string $name
 * @property integer $reference_times
 *
 * @property AlbumTag[] $albumTags
 * @property Album[] $albums
 * @property MomentTag[] $momentTags
 * @property Moment[] $moments
 * @property UserTag[] $userTags
 */
class Tag extends \yii\db\ActiveRecord {
       
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'tag';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['name'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'name' => 'Name'
        ];
    }

}
