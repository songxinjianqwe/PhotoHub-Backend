<?php

namespace app\models\tag;

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
            [['name'], 'string'],
            [['reference_times'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'reference_times' => 'Reference Times',
        ];
    }

    /**
     * @param $tagName
     * @param $userId
     * @param $tagType moment,album,user
     */
    public static function saveTag($tagName,$userId,$tagType) {
        $tagDO = Tag::findOne(['name' => $tagName]);
        //如果不存在该tag，那么保存
        if ($tagDO === null) {
            $newTag = new Tag();
            $newTag->name = $tagName;
            $newTag->save();
            
            $className = '\app\models\tag\\'.ucwords($tagType).'Tag';
            $propertyName = $tagType.'_id';
            
            $userTag = new $className();
            $userTag->$propertyName = $userId;
            $userTag->tag_id = $newTag->id;
            $userTag->save();
        } else {
            $tagDO->reference_times++;
            $tagDO->update();

            $userTag = new UserTag();
            $userTag->user_id = $userId;
            $userTag->tag_id = $tagDO->id;
            $userTag->save();
        }
    }
}
