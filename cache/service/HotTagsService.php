<?php
/**
 * Created by PhpStorm.
 * User: songx
 * Date: 2017/10/27
 * Time: 11:41
 */

namespace app\cache\service;


use app\cache\RedisZSetManager;
use app\models\page\PageVO;
use app\models\tag\Tag;
use app\models\tag\UserTag;
use app\util\DBUtil;
use Yii;

class HotTagsService {
    private $manager;
    private $latestMomentsByTagService;
    private $hotMomentsByTagService;

    public function __construct() {
        $this->manager = new RedisZSetManager('tag.hot');
        $this->latestMomentsByTagService = Yii::$container->get('app\cache\service\LatestMomentsByTagService');
        $this->hotMomentsByTagService = Yii::$container->get('app\cache\service\HotMomentsByTagService');
    }
    
    public function saveTags($tagIds,$userId){
        foreach($tagIds as $tagId){
            $userTag = new UserTag();
            $userTag->user_id = $userId;
            $userTag->tag_id = $tagId;
            $userTag->save();
            //用户关注标签会影响标签的热度
            $this->manager->changeScore($tagId, 1);
        }    
    }
    
    public function saveTag($tagName, $typeId, $tagType) {
        $tagDO = Tag::findOne(['name' => $tagName]);
        $className = '\app\models\tag\\' . ucwords($tagType) . 'Tag';
        $propertyName = $tagType . '_id';
        //如果不存在该tag，那么保存
        if ($tagDO === null) {
            $newTag = new Tag();
            $newTag->name = $tagName;
            $newTag->save();
            //新增的引用数为1
            $this->createTag($newTag->id);

            $typeTag = new $className();
            $typeTag->$propertyName = $typeId;
            $typeTag->tag_id = $newTag->id;
            $typeTag->save();
            //当新增的Tag属于Moment，那么会将新增的Moment加入
            if ($tagType === 'moment') {
                $this->latestMomentsByTagService->createMoment($typeId, $newTag->id);
                $this->hotMomentsByTagService->createMoment($typeId, $newTag->id);
            }
        } else {
            //如果存在则引用
            //引用数+1
            $this->referTag($tagDO->id);
            $typeTag = new $className();
            $typeTag->$propertyName = $typeId;
            $typeTag->tag_id = $tagDO->id;
            $typeTag->save();
            if ($tagType === 'moment') {
                $this->latestMomentsByTagService->createMoment($typeId, $tagDO->id);
                $this->hotMomentsByTagService->createMoment($typeId, $tagDO->id);
            }
        }
    }

    public function updateTags($oldTags, $newTags, $typeId, $tagType) {
        $className = '\app\models\tag\\' . ucwords($tagType) . 'Tag';
        $propertyName = $tagType . '_id';
        $containedTags = [];
        foreach ($newTags as $newTag) {
            //新增的情况
            if ($newTag['id'] === null) {

                $newTagObj = new Tag();
                $newTagObj->name = $newTag['name'];
                $newTagObj->save();
                $this->createTag($newTagObj->id);

                $typeTag = new $className();
                $typeTag->$propertyName = $typeId;
                $typeTag->tag_id = $newTagObj->id;
                $typeTag->save();
                Yii::info('新增TypeTag' . $typeTag->tag_id);
                if ($tagType === 'moment') {
                    $this->latestMomentsByTagService->createMoment($typeId, $newTagObj->id);
                    $this->hotMomentsByTagService->createMoment($typeId, $newTagObj->id);
                }
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
                $this->unReferTag($oldTag->id);

                if ($tagType === 'moment') {
                    $this->latestMomentsByTagService->removeMoment($typeId, $oldTag->id);
                    $this->hotMomentsByTagService->removeMoment($typeId, $oldTag->id);
                }
            }
        }
    }

    public function deleteTags($tags, $typeId, $tagType) {
        $className = '\app\models\tag\\' . ucwords($tagType) . 'Tag';
        $propertyName = $tagType . '_id';
        foreach ($tags as $tag) {
            Yii::info('待删除的TypeTag' . $tag->id);
            $deletedTypeTag = $className::findOne([
                'tag_id' => $tag->id,
                $propertyName => $typeId
            ]);
            $deletedTypeTag->delete();
            $this->unReferTag($tag->id);

            if ($tagType === 'moment') {
                $this->latestMomentsByTagService->removeMoment($typeId, $tag->id);
                $this->hotMomentsByTagService->removeMoment($typeId, $tag->id);
            }
        }
    }

    private function createTag($tagId) {
        $this->manager->addElement($tagId, 1);
    }

    private function referTag($tagId) {
        $this->manager->changeScore($tagId, 1);
    }

    private function unReferTag($tagId) {
        $this->manager->changeScore($tagId, -1);
    }

    public function getHotTags($page, $per_page) {
        $pageDTO = $this->manager->indexDesc($page, $per_page);
        return new PageVO(DBUtil::orderByField($pageDTO->ids,Tag::find()->where(['id' => $pageDTO->ids])->all(),'id'), $pageDTO->_meta);
    }
}