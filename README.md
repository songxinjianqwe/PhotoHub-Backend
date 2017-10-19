## PHP环境安装
1. 下载PHP7和Apache
2. Apache做一定配置，并加入到系统服务，最后启动
访问http://localhost:port即可访问
比如设置端口号为8088
http://localhost:8088/
这个会映射到Apache目录下的htdocs
我们自己的项目也会放到该目录下
3. 在PHPStorm中配置php和apache
Language:php 设置php7和php.exe
4. 新建项目
Build:Deployment 设置apache的环境
![image](http://markdown-1252651195.cossh.myqcloud.com/%E5%9B%BE%E5%83%8F%201.png)
![image](http://markdown-1252651195.cossh.myqcloud.com/%E5%9B%BE%E5%83%8F%202.png)
5. 在项目上右键上传至localhost
可以在Tools->Deployment->Automatic Upload
6. 在浏览器中打开http://localhost:port/项目名/index.html
访问时不需要在PhpStorm中点击任何东西，PhpStorm相当于一个编辑器而非运行环境，
php代码实际运行在Apache中，因此要保证Apache开机启动且始终运行（Windows 服务保证）。

比如当前项目名为YiiRestSkeleton，在浏览器中访问http://localhost:8088/YiiRestSkeleton
该文件会映射到Apache目录/htdocs/YiiRestSkeleton/index.html
7. 如果希望在phpStorm中打开网页，而非跑到浏览器里打开的话，需要配置右上角的运行环境。
![image](http://markdown-1252651195.cossh.myqcloud.com/%E5%9B%BE%E5%83%8F%203.png)
中途需要添加server，server的端口需要和apache端口一致
此时点击绿色的Run按钮直接会打开http://localhost:8088/YiiRestSkeleton/
8. 参考
> http://www.cnblogs.com/wangqishu/p/5028031.html
> http://blog.csdn.net/u012861467/article/details/54692236

- 注意php和apache的版本要对应
- 修改apache的ServerRoot、DocumentRoot、PHPIniDir、 LoadModule php7_modul、php的minetype、ServerName和端口号
- 修改php的php.ini和extension_dir 
- 需要将apache加入windows服务
- 通过访问http://localhost:8088/测试是否正常启动

## Yii环境配置
1. 下载安装Composer
> https://getcomposer.org/Composer-Setup.exe
2. 在命令行中输入
composer self-update 
composer global require "fxp/composer-asset-plugin:^1.3.1"
composer create-project --prefer-dist yiisoft/yii2-app-basic project_dir
3.启动内置服务器：php yii serve --port=8080
访问：http://localhost:8080/
4.使用Apache服务器
编辑httpd.conf
取消该行注释LoadModule rewrite_module modules/mod_rewrite.so
设置文档根目录为 "basic/web"
```
DocumentRoot "D:\php\PhotoHub\web"
<Directory "D:\php\PhotoHub\web">
    # 开启 mod_rewrite 用于美化 URL 功能的支持（译注：对应 pretty URL 选项）
    RewriteEngine on
    # 如果请求的是真实存在的文件或目录，直接访问
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    # 如果请求的不是真实文件或目录，分发请求至 index.php
    RewriteRule . index.php
    DirectoryIndex index.php 
    # ...其它设置...
</Directory>
```
在Windows系统服务中重启Apache

## Yii开发

### 项目结构
basic/                  应用根目录
    composer.json       Composer 配置文件, 描述包信息
    config/             包含应用配置及其它配置
        console.php     控制台应用配置信息
        web.php         Web 应用配置信息
    commands/           包含控制台命令类
    controllers/        包含控制器类
    models/             包含模型类
    runtime/            包含 Yii 在运行时生成的文件，例如日志和缓存文件
    vendor/             包含已经安装的 Composer 包，包括 Yii 框架自身
    views/              包含视图文件
    web/                Web 应用根目录，包含 Web 入口文件
        assets/         包含 Yii 发布的资源文件（javascript 和 css）
        index.php       应用入口文件
    yii                 Yii 控制台命令执行脚本

Rest开发注意
修改web.php中的两处配置
删除mail,test,views,commands,assets目录
web目录不可删除，起码要保留index.php文件
修改db.php中的数据源

### Rest    
#### Rest整合成功后默认可以访问这些API
GET /users: 逐页列出所有用户
GET /users/123: 返回用户 123 的详细信息
POST /users: 创建一个新用户
PUT /users/123: 更新用户123
DELETE /users/123: 删除用户123

导航到其他页面的数据。例如： http://localhost/users?page=2 会给你的用户数据的下一个页面。
使用 fields 和 expand 参数，你也可以指定哪些字段应该包含在结果内。 例如：URL http://localhost/users?fields=id,email 将只返回 id 和 email 字段。

#### 开发
从 yii\base\Model 类扩展的资源被表示为数据模型。 如果你在使用（关系或非关系）数据库，推荐你使用 ActiveRecord 来表示资源。
你可以使用 yii\rest\UrlRule 简化路由到你的 API 末端。

1. 在/models中创建一个实体类：
```php
class Book extends ActiveRecord
{

    public static function tableName()
    {
        return 'book';
    }
}
```
实体类要继承自ActiveRecord，并且添加一个静态方法tableName()，返回的是对应的表名

2. 在/controllers中创建一个Controller类：
```php
class BookController extends ActiveController
{
    public $modelClass = 'app\models\Book';
}
```
Controller要继承自ActiveController，并且添加一个public属性$modelClass，指向实体类的类路径

3. 在/config/web.php中的urlManager的rules加一行，只需要改controller，这个字符串是url路径
```php
'urlManager' => [
    'enablePrettyUrl' => true,
    'enableStrictParsing' => true,
    'showScriptName' => false,
    'rules' => [
        ['class' => 'yii\rest\UrlRule', 'controller' => 'user'],
        ['class' => 'yii\rest\UrlRule', 'controller' => 'book'],
    ],
],
```
4. 形成映射
http://localhost:8080/users -> app\controllers\UserController -> app\modesl\User -> Table user

### 应用结构
#### 入口脚本
入口脚本主要完成以下工作：

定义全局常量；
注册 Composer 自动加载器；
包含 Yii 类文件；
加载应用配置；
创建一个应用实例并配置;
调用 yii\base\Application::run() 来处理请求。


当定义一个常量时，通常使用类似如下代码来定义：

defined('YII_DEBUG') or define('YII_DEBUG', true);
上面的代码等同于:

if (!defined('YII_DEBUG')) {
    define('YII_DEBUG', true);
}

#### 应用主体
应用主体是管理 Yii 应用系统整体结构和生命周期的对象。 每个Yii应用系统只能包含一个应用主体，
应用主体在 入口脚本 中创建并能通过表达式 \Yii::$app 全局范围内访问。

在一个应用中，至少要配置2个属性: yii\base\Application::id 和 yii\base\Application::basePath。
1. yii\base\Application::id
yii\base\Application::id 属性用来区分其他应用的唯一标识ID。主要给程序使用。 为了方便协作，最好使用数字作为应用主体ID， 但不强制要求为数字。

2. yii\base\Application::basePath
yii\base\Application::basePath 指定该应用的根目录。 根目录包含应用系统所有受保护的源代码。 在根目录下可以看到对应MVC设计模式的models, views, controllers等子目录。
可以使用路径或 路径别名 来在配置 yii\base\Application::basePath 属性。 两种格式所对应的目录都必须存在，否则系统会抛出一个异常。 系统会使用 realpath() 函数规范化配置的路径.
yii\base\Application::basePath 属性经常用于派生一些其他重要路径（如runtime路径）， 因此，系统预定义 @app 代表这个路径。 派生路径可以通过这个别名组成（如@app/runtime代表runtime的路径）。

3. yii\base\Application::aliases
该属性允许你用一个数组定义多个 别名。 数组的key为别名名称，值为对应的路径。 例如：

[
    'aliases' => [
        '@name1' => 'path/to/path1',
        '@name2' => 'path/to/path2',
    ],
]
4. bootstrap
这个属性很实用，它允许你用数组指定启动阶段 bootstrapping process 需要运行的组件。 比如，如果你希望一个 模块 自定义 URL 规则， 你可以将模块ID加入到bootstrap数组中。

属性中的每个组件需要指定以下一项:

应用 组件 ID.
模块 ID.
类名.
配置数组.
创建并返回一个组件的无名称函数.
5.yii\base\Application::components
这是最重要的属性，它允许你注册多个在其他地方使用的 应用组件.
每一个应用组件指定一个key-value对的数组，key代表组件ID， value代表组件类名或 配置。
在应用中可以任意注册组件，并可以通过表达式 \Yii::$app->ComponentID 全局访问。
6. yii\base\Application::controllerMap 
该属性允许你指定一个控制器ID到任意控制器类。 
Yii遵循一个默认的 规则 指定控制器ID到任意控制器类
（如post对应app\controllers\PostController）。 
通过配置这个属性，可以打破这个默认规则，
在下面的例子中， account对应到app\controllers\UserController， 
article 对应到 app\controllers\PostController。
[
    'controllerMap' => [
        'account' => 'app\controllers\UserController',
        'article' => [
            'class' => 'app\controllers\PostController',
            'enableCsrfValidation' => false,
        ],
    ],
]

7.controllerNamespace
该属性指定控制器类默认的命名空间，默认为app\controllers。 
比如控制器ID为 post 默认对应 PostController （不带命名空间）， 
类全名为 app\controllers\PostController。
控制器类文件可能放在这个命名空间对应目录的子目录下， 
例如，控制器ID admin/post 对应的控制器类全名为 app\controllers\admin\PostController。
如果你想打破上述的规则， 可以配置 controllerMap 属性。

8.yii\base\Application::modules
该属性指定应用所包含的 模块。
该属性使用数组包含多个模块类 配置，数组的键为模块ID， 例：
[
    'modules' => [
        // "booking" 模块以及对应的类
        'booking' => 'app\modules\booking\BookingModule',

        // "comment" 模块以及对应的配置数组
        'comment' => [
            'class' => 'app\modules\comment\CommentModule',
            'db' => 'db',
        ],
    ],
]

#### 应用组件
在同一个应用中，每个应用组件都有一个独一无二的 ID 用来区分其他应用组件， 你可以通过如下表达式访问应用组件。
\Yii::$app->componentID
可以使用 \Yii::$app->db 来获取到已注册到应用的 DB connection， 使用 \Yii::$app->cache 来获取到已注册到应用的 primary cache。
第一次使用以上表达式时候会创建应用组件实例， 后续再访问会返回此实例，无需再次创建。
有时你想在每个请求处理过程都实例化某个组件即便它不会被访问， 可以将该组件ID加入到应用主体的 bootstrap 属性中。
