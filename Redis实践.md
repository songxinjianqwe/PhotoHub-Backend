# Practice
hash sets: 关注列表, 粉丝列表, 双向关注列表(key-value(field), 排序)
string(counter): 微博数, 粉丝数, ...(避免了select count(*) from ...)
sort sets(自动排序): TopN, 热门微博等, 自动排序
lists(queue): push/sub提醒,...

# Requirements
1. moment的votes、comments和forwards计数
2. tag的reference_times计数
3. activity的replies计数
4. 热门动态
5. 热门标签
6. feed流
7. 标签达人
8. 每个标签对应的最新/热门动态
9. 最新/热门活动

# 数据结构
1. hash(键值对)
2. list(按照插入顺序排序)
3. zset(有序集)
4. string

# 需求对应的数据结构与业务逻辑
1. feed流：基于zset（一系列的zset）
每个用户有一个收feed的zset  redis的key为**feed.user_id**
(创建用户时，添加一个zset，key为feed.username)

某用户发布动态：遍历粉丝列表，将moment_id插入到每个粉丝的收feed，值为moment_id，score为时间戳(秒/毫秒)
某用户删除动态：遍历粉丝列表，从每个粉丝的收feed中删除对应的moment_id

2. 热门标签：基于zset（一个zset）
redis的key为**tag.hot**
值为tag_id，score为tag的引用次数

创建标签时，会在zset中添加一个元素
每次引用tag（新增album,moment），会增加对应tag_id的score
取消引用时会减少对应tag_id的score

3. 热门动态：基于zset（一个zset）
redis的key为**moment.hot**

某用户发布动态：将moment_id插入到zset中，score为0
某用户删除动态：从zset中删除对应的moment_id

某用户点赞转发评论某动态：在zset中将对应的动态的score+1
某用户取消点赞，取消转发某动态：在zset中将对应的动态的score-1

4. 最新/热门活动：基于zset（两个zset）
redis的key为**activity.latest**   **activity.hot**
值为activity_id，score为时间戳或回复数

创建活动时，会在两个zset中添加一个元素
删除活动时，会删除相应元素

每次回复某个活动时，会增加activity.hot中对应活动的score
每次删除对某个活动的回复时，会减少activity.hot中对应活动的score

5. 每个标签对应的最新/热门动态（一系列的zset）
**tag.moment.hot.tag_id**
**tag.moment.latest.tag_id**
创建标签时，会添加两个zset
某用户发布动态时，会将moment_id插入到zset中
某用户删除动态：从zset中删除对应的moment_id

某用户点赞转发评论某动态：在zset中将对应的动态的score+1
某用户取消点赞，取消转发某动态：在zset中将对应的动态的score-1
6. 标签达人（一系列zset）
**tag.talent.tag_id**

创建标签时，会添加一个zset

某用户点赞转发评论某动态：搜索该动态对应的标签，遍历标签，将标签对应的zset中的用户的score+1，如果没有该用户，则插入该用户。
某用户取消点赞，搜索该动态对应的标签，遍历标签，将标签对应的zset中的用户的score-1   

# 示例
//实例化redis
$redis = new Redis();
//连接
$redis->connect('127.0.0.1', 6379);
//有序集合
//添加元素
echo $redis->zadd('set', 1, 'cat');echo '<br>';
echo $redis->zadd('set', 2, 'dog');echo '<br>';
echo $redis->zadd('set', 3, 'fish');echo '<br>';
echo $redis->zadd('set', 4, 'dog');echo '<br>';
echo $redis->zadd('set', 4, 'bird');echo '<br>';

//返回集合中的所有元素
print_r($redis->zrange('set', 0, -1));echo '<br>';
print_r($redis->zrange('set', 0, -1, true));echo '<br>';

//返回元素的score值
echo $redis->zscore('set', 'dog');echo '<br>';

//返回存储的个数
echo $redis->zcard('set');echo '<br>';

//删除指定成员
$redis->zrem('set', 'cat');
print_r($redis->zrange('set', 0, -1));echo '<br>';

//返回集合中介于min和max之间的值的个数
print_r($redis->zcount('set', 3, 5));echo '<br>';

//返回有序集合中score介于min和max之间的值
print_r($redis->zrangebyscore('set', 3, 5));echo '<br>';
print_r($redis->zrangebyscore('set', 3, 5, ['withscores'=>true]));echo '<br>';

//返回集合中指定区间内所有的值
print_r($redis->zrevrange('set', 1, 2));echo '<br>';
print_r($redis->zrevrange('set', 1, 2, true));echo '<br>';


//有序集合中指定值的socre增加
echo $redis->zscore('set', 'dog');echo '<br>';
$redis->zincrby('set', 2, 'dog');
echo $redis->zscore('set', 'dog');echo '<br>';

//移除score值介于min和max之间的元素
print_r($redis->zrange('set', 0, -1, true));echo '<br>';
print_r($redis->zremrangebyscore('set', 3, 4));echo '<br>';
print_r($redis->zrange('set', 0, -1, true));echo '<br>';

//结果
// 1
// 0
// 0
// 0
// 0
// Array ( [0] => cat [1] => fish [2] => bird [3] => dog )
// Array ( [cat] => 1 [fish] => 3 [bird] => 4 [dog] => 4 )
// 4
// 4
// Array ( [0] => fish [1] => bird [2] => dog )
// 3
// Array ( [0] => fish [1] => bird [2] => dog )
// Array ( [fish] => 3 [bird] => 4 [dog] => 4 )
// Array ( [0] => bird [1] => fish )
// Array ( [bird] => 4 [fish] => 3 )
// 4
// 6
// Array ( [fish] => 3 [bird] => 4 [dog] => 6 )
// 2
// Array ( [dog] => 6 )             