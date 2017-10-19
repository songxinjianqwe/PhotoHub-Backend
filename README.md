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

## Yii开发

### 应用结构
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
### 开发注意
修改web.php中的两处配置
删除mail和test目录
    



