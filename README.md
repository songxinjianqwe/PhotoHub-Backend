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
web目录不可删除，起码要保留index.php和assets文件（否则无法访问gii）
修改db.php中的数据源

### Gii
通过
http://localhost:8080/gii访问

### Rest    

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


模糊搜索标签
查询一条message的点赞评论转发情况
