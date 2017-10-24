### ActiveController
#### Rest整合成功后默认可以访问这些API
GET /users: 逐页列出所有用户
GET /users/123: 返回用户 123 的详细信息
POST /users: 创建一个新用户
PUT /users/123: 更新用户123
DELETE /users/123: 删除用户123

[
    'PUT,PATCH users/<id>' => 'user/update',
    'DELETE users/<id>' => 'user/delete',
    'GET,HEAD users/<id>' => 'user/view',
    'POST users' => 'user/create',
    'GET,HEAD users' => 'user/index',
    'users/<id>' => 'user/options',
    'users' => 'user/options',
]

导航到其他页面的数据。例如： http://localhost/users?page=2 会给你的用户数据的下一个页面。
使用 fields 和 expand 参数，你也可以指定哪些字段应该包含在结果内。 例如：URL http://localhost/users?fields=id,email 将只返回 id 和 email 字段。

### 资源
#### 通过覆盖 fields() 和/或 yii\base\Model::extraFields() 方法, 可指定资源中称为 字段 的数据放入展现数组中， 两个方法的差别为前者指定默认包含到展现数组的字段集合， 后者指定由于终端用户的请求包含 expand 参数哪些额外的字段应被包含到展现数组，例如，

// 返回fields()方法中申明的所有字段
http://localhost/users

// 只返回fields()方法中申明的id和email字段
http://localhost/users?fields=id,email

// 返回fields()方法申明的所有字段，以及extraFields()方法中的profile字段
http://localhost/users?expand=profile

// 返回回fields()和extraFields()方法中提供的id, email 和 profile字段
http://localhost/users?fields=id,email&expand=profile


可覆盖 fields() 方法来增加、删除、重命名、重定义字段， fields() 的返回值应为数组，数组的键为字段名 数组的值为对应的字段定义，可为属性名或返回对应的字段值的匿名函数， 特殊情况下，如果字段名和属性名相同， 可省略数组的键，例如

// 明确列出每个字段，适用于你希望数据表或
// 模型属性修改时不导致你的字段修改（保持后端API兼容性）
public function fields()
{
    return [
        // 字段名和属性名相同
        'id',
        // 字段名为"email", 对应的属性名为"email_address"
        'email' => 'email_address',
        // 字段名为"name", 值由一个PHP回调函数定义
        'name' => function ($model) {
            return $model->first_name . ' ' . $model->last_name;
        },
    ];
}

// 过滤掉一些字段，适用于你希望继承
// 父类实现同时你想屏蔽掉一些敏感字段
public function fields()
{
    $fields = parent::fields();

    // 删除一些包含敏感信息的字段
    unset($fields['auth_key'], $fields['password_hash'], $fields['password_reset_token']);

    return $fields;
}

#### 覆盖 extraFields() 方法
yii\base\Model::extraFields() 默认返回空值， yii\db\ActiveRecord::extraFields() 返回和数据表关联的属性。

extraFields() 返回的数据格式和 fields() 相同， 一般extraFields() 主要用于指定哪些值为对象的字段， 例如，给定以下字段申明

public function fields()
{
    return ['id', 'email'];
}

public function extraFields()
{
    return ['profile'];
}

集合
资源对象可以组成 集合， 每个集合包含一组相同类型的资源对象。

集合可被展现成数组，更多情况下展现成 data providers. 因为data providers支持资源的排序和分页，这个特性在 RESTful API 返回集合时也用到， 例如This is because data providers support sorting and pagination 如下操作返回post资源的data provider:

namespace app\controllers;

use yii\rest\Controller;
use yii\data\ActiveDataProvider;
use app\models\Post;

class PostController extends Controller
{
    public function actionIndex()
    {
        return new ActiveDataProvider([
            'query' => Post::find(),
        ]);
    }
}
#### DataProvider
当在RESTful API响应中发送data provider 时， yii\rest\Serializer 会取出资源的当前页并组装成资源对象数组， yii\rest\Serializer 也通过如下HTTP头包含页码信息：

X-Pagination-Total-Count: 资源所有数量;
X-Pagination-Page-Count: 页数;
X-Pagination-Current-Page: 当前页(从1开始);
X-Pagination-Per-Page: 每页资源数量;
Link: 允许客户端一页一页遍历资源的导航链接集合.
可在快速入门 一节中找到样例.


###Controller
Yii 提供两个控制器基类来简化创建RESTful 操作的工作:yii\rest\Controller 和 yii\rest\ActiveController， 两个类的差别是后者提供一系列将资源处理成Active Record的操作。 因此如果使用Active Record内置的操作会比较方便，可考虑将控制器类 继承yii\rest\ActiveController， 它会让你用最少的代码完成强大的RESTful APIs.

yii\rest\Controller 和 yii\rest\ActiveController 提供以下功能， 一些功能在后续章节详细描述：

HTTP 方法验证;
内容协商和数据格式化;
认证;
频率限制.

yii\rest\ActiveController 额外提供一下功能:
一系列常用动作: index, view, create, update, delete, options;
对动作和资源进行用户认证.

#### 过滤器 
yii\rest\Controller提供的大多数RESTful API功能通过过滤器实现. 特别是以下过滤器会按顺序执行：

在 响应格式化 一节描述;

verbFilter: 支持HTTP 方法验证;
在认证一节描述;

在频率限制 一节描述.

这些过滤器都在behaviors()方法中声明， 可覆盖该方法来配置单独的过滤器，禁用某个或增加你自定义的过滤器。 例如，如果你只想用HTTP 基础认证，可编写如下代码：

use yii\filters\auth\HttpBasicAuth;

public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['authenticator'] = [
        'class' => HttpBasicAuth::className(),
    ];
    return $behaviors;
}
#### CORS
public function behaviors()
{
    $behaviors = parent::behaviors();

    // remove authentication filter
    $auth = $behaviors['authenticator'];
    unset($behaviors['authenticator']);
    
    // add CORS filter
    $behaviors['corsFilter'] = [
        'class' => \yii\filters\Cors::className(),
    ];
    
    // re-add authentication filter
    $behaviors['authenticator'] = $auth;
    // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
    $behaviors['authenticator']['except'] = ['options'];

    return $behaviors;
}
####ActiveController
继承 ActiveController
如果你的控制器继承yii\rest\ActiveController， 应设置modelClass 属性 为通过该控制器返回给用户的资源类名，该类必须继承yii\db\ActiveRecord.

自定义动作
一系列常用动作: index, view, create, update, delete, options;
所有这些动作通过actions() 方法申明，可覆盖actions()方法配置或禁用这些动作， 如下所示：

public function actions()
{
    $actions = parent::actions();

    // 禁用"delete" 和 "create" 动作
    unset($actions['delete'], $actions['create']);

    // 使用"prepareDataProvider()"方法自定义数据provider 
    $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

    return $actions;
}

public function prepareDataProvider()
{
    // 为"index"动作准备和返回数据provider
}

#### 鉴权
执行访问检查
通过RESTful APIs显示数据时，经常需要检查当前用户是否有权限访问和操作所请求的资源， 在yii\rest\ActiveController中， 可覆盖checkAccess()方法来完成权限检查。

public function checkAccess($action, $model = null, $params = [])
{
    // check if the user can access $action and $model
    // throw ForbiddenHttpException if access should be denied
    if ($action === 'update' || $action === 'delete') {
        if ($model->author_id !== \Yii::$app->user->id)
            throw new \yii\web\ForbiddenHttpException(sprintf('You can only %s articles that you\'ve created.', $action));
    }
}
checkAccess() 方法默认会被yii\rest\ActiveController默认动作所调用，如果创建新的操作并想执行权限检查， 应在新的动作中明确调用该方法。

#### 路由
在实践中，你通常要用美观的 URL 并采取有优势的 HTTP 动词。 例如，请求 POST /users 意味着访问 user/create 动作。 这可以很容易地通过配置 urlManager 应用程序组件来完成 如下所示：

'urlManager' => [
    'enablePrettyUrl' => true,
    'enableStrictParsing' => true,
    'showScriptName' => false,
    'rules' => [
        ['class' => 'yii\rest\UrlRule', 'controller' => 'user'],
    ],
]

映射
[
    'PUT,PATCH users/<id>' => 'user/update',
    'DELETE users/<id>' => 'user/delete',
    'GET,HEAD users/<id>' => 'user/view',
    'POST users' => 'user/create',
    'GET,HEAD users' => 'user/index',
    'users/<id>' => 'user/options',
    'users' => 'user/options',
]
该规则支持下面的 API 末端:
GET /users: 逐页列出所有用户；
HEAD /users: 显示用户列表的概要信息；
POST /users: 创建一个新用户；
GET /users/123: 返回用户为 123 的详细信息;
HEAD /users/123: 显示用户 123 的概述信息;
PATCH /users/123 and PUT /users/123: 更新用户 123;
DELETE /users/123: 删除用户 123;
OPTIONS /users: 显示关于末端 /users 支持的动词;
OPTIONS /users/123: 显示有关末端 /users/123 支持的动词。

您可以通过配置 only 和 except 选项来明确列出哪些行为支持， 哪些行为禁用。例如，
[
    'class' => 'yii\rest\UrlRule',
    'controller' => 'user',
    'except' => ['delete', 'create', 'update'],
],

您也可以通过配置 patterns 或 extraPatterns 重新定义现有的模式或添加此规则支持的新模式。 例如，通过末端 GET /users/search 可以支持新行为 search， 按照如下配置 extraPatterns 选项，
[
    'class' => 'yii\rest\UrlRule',
    'controller' => 'user',
    'extraPatterns' => [
        'GET search' => 'search',
    ],
]

{{ip}}/users?page=1&per-page=3
注意参数是page和per-page



#### 格式化响应
当处理一个 RESTful API 请求时， 一个应用程序通常需要如下步骤 来处理响应格式：

确定可能影响响应格式的各种因素， 例如媒介类型， 语言， 版本， 等等。 这个过程也被称为 content negotiation。
资源对象转换为数组， 如在 Resources 部分中所描述的。 通过 yii\rest\Serializer 来完成。
通过内容协商步骤将数组转换成字符串。 response formatters 通过 response 应用程序 组件来注册完成。

内容协商
幕后，执行一个 RESTful API 控制器动作之前，yii\filters\ContentNegotiator filter 将检查 Accept HTTP header 在请求时和配置 response format 为 'json'。 之后的动作被执行并返回得到的资源对象或集合， yii\rest\Serializer 将结果转换成一个数组。最后，yii\web\JsonResponseFormatter 该数组将序列化为JSON字符串，并将其包括在响应主体。

默认, RESTful APIs 同时支持JSON和XML格式。为了支持新的格式，你应该 在 contentNegotiator 过滤器中配置 formats 属性， 类似如下 API 控制器类:

use yii\web\Response;

public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['contentNegotiator']['formats']['text/html'] = Response::FORMAT_HTML;
    return $behaviors;
}
formats 属性的keys支持 MIME 类型，而 values 必须在 yii\web\Response::$formatters 中支持被响应格式名称。

数据序列化
正如我们上面所描述的，yii\rest\Serializer 负责转换资源的中间件 对象或集合到数组。它将对象 yii\base\ArrayableInterface 作为 yii\data\DataProviderInterface。 前者主要由资源对象实现， 而 后者是资源集合。

你可以通过设置 yii\rest\Controller::$serializer 属性和一个配置数组。 例如，有时你可能想通过直接在响应主体内包含分页信息来 简化客户端的开发工作。这样做，按照如下规则配置 yii\rest\Serializer::$collectionEnvelope 属性：

use yii\rest\ActiveController;

class UserController extends ActiveController
{
    public $modelClass = 'app\models\User';
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];
}
响应数据
{
    "items": [
        {
            "id": 1,
            ...
        },
        {
            "id": 2,
            ...
        },
        ...
    ],
    "_links": {
        "self": {
            "href": "http://localhost/users?page=1"
        },
        "next": {
            "href": "http://localhost/users?page=2"
        },
        "last": {
            "href": "http://localhost/users?page=50"
        }
    },
    "_meta": {
        "totalCount": 1000,
        "pageCount": 50,
        "currentPage": 1,
        "perPage": 20
    }
}    
    