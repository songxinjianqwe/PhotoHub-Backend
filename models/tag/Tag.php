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

    public static function updateTags($oldTags, $newTags, $typeId, $tagType) {
        $className = '\app\models\tag\\' . ucwords($tagType) . 'Tag';
        $propertyName = $tagType . '_id';
        $containedTags = [];
        foreach ($newTags as $newTag) {
            //新增的情况
            if ($newTag['id'] === null) {

                $newTagObj = new Tag();
                $newTagObj->name = $newTag['name'];
                $newTagObj->save();

                $typeTag = new $className();
                $typeTag->$propertyName = $typeId;
                $typeTag->tag_id = $newTagObj->id;
                $typeTag->save();
                Yii::info('新增TypeTag' . $typeTag->tag_id);
            } else {
                foreach ($oldTags as $oldTag) {
                    //只要id相等，说明一定不是删除，是修改或不变
                    if ($newTag['id'] == $oldTag->id) {
                        //这个数组里的元素都不会被删除
                        Yii::info('array_push($containedTags, $oldTag->id)' . $oldTag->id);
                        array_push($containedTags, $oldTag->id);
                    }
                }
            }
        }
        //需要去掉的tag
        foreach ($oldTags as $oldTag) {
            if (!in_array($oldTag->id, $containedTags)) {
                Yii::info('待删除的TypeTag' . $oldTag->id);
                $deletedTypeTag = $className::findOne([
                    'tag_id' => $oldTag->id,
                    $propertyName => $typeId
                ]);
                $deletedTypeTag->delete();
                $oldTag->reference_times--;
                $oldTag->update();
            }
        }
    }
    
    public static function deleteTags($tags, $typeId, $tagType) {
        $className = '\app\models\tag\\' . ucwords($tagType) . 'Tag';
        $propertyName = $tagType . '_id';
        foreach ($tags as $tag) {
            Yii::info('待删除的TypeTag' . $tag->id);
            $deletedTypeTag = $className::findOne([
                'tag_id' => $tag->id,
                $propertyName => $typeId
            ]);
            $deletedTypeTag->delete();
            $tag->reference_times--;
            $tag->update();
        }
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
    public static function saveTag($tagName, $typeId, $tagType) {
        $tagDO = Tag::findOne(['name' => $tagName]);
        $className = '\app\models\tag\\' . ucwords($tagType) . 'Tag';
        $propertyName = $tagType . '_id';
        //如果不存在该tag，那么保存
        if ($tagDO === null) {
            $newTag = new Tag();
            $newTag->name = $tagName;
            $newTag->save();

            $typeTag = new $className();
            $typeTag->$propertyName = $typeId;
            $typeTag->tag_id = $newTag->id;
            $typeTag->save();
        } else {
            //如果存在则引用
            $tagDO->reference_times++;
            $tagDO->update();

            $typeTag = new $className();
            $typeTag->$propertyName = $typeId;
            $typeTag->tag_id = $tagDO->id;
            $typeTag->save();
        }
    }
}
