## 纯PHP代码文件不要以?>结束
## 类与对象
$bar = new foo;
$bar->do_foo();
## 引用
在下列情况下一个变量被认为是 NULL：
被赋值为 NULL。
尚未被赋值。
被 unset()。
## foreach
可以很容易地通过在 $value 之前加上 & 来修改数组的元素。此方法将以引用赋值而不是拷贝一个值。
<?php
$arr = array(1, 2, 3, 4);
foreach ($arr as &$value) {
    $value = $value * 2;
}
// $arr is now array(2, 4, 6, 8)
unset($value); // 最后取消掉引用
?>
## require|include
require 和 include 几乎完全一样，除了处理失败的方式不同之外。require 在出错时产生 E_COMPILE_ERROR 级别的错误。换句话说将导致脚本中止而 include 只产生警告（E_WARNING），脚本会继续运行。
被包含文件先按参数给出的路径寻找，如果没有给出目录（只有文件名）时则按照 include_path 指定的目录寻找。如果在 include_path 下没找到该文件则 include 最后才在调用脚本文件所在的目录和当前工作目录下寻找。如果最后仍未找到文件则 include 结构会发出一条警告；这一点和 require 不同，后者会发出一个致命错误。
require_once 语句和 require 语句完全相同，唯一区别是 PHP 会检查该文件是否已经被包含过，如果是则不会再次包含。
## 可变函数
$func = 'foo';
$func();        // This calls foo()
## 访问静态变量/方法
::
Test::getNew();

## 继承extends
被继承的方法和属性可以通过用同样的名字重新声明被覆盖。但是如果父类定义方法时使用了 final，
则该方法不可被覆盖。可以通过 parent:: 来访问被覆盖的方法或属性。

$this->property（其中 property 是该属性名）这种方式来访问非静态属性。
静态属性则是用 ::（双冒号）：self::$property 来访问。
const constant = 'constant value';
self::constant
MyClass::constant 

## 自动加载
spl_autoload_register() 函数可以注册任意数量的自动加载器，当使用尚未被定义的类（class）和接口（interface）时自动去加载。
通过注册自动加载器，脚本引擎在 PHP 出错失败前有了最后一个机会加载所需的类。
 
## 构造函数与析构函数
class BaseClass {
   function __construct() {
       print "In BaseClass constructor\n";
   }
}

class SubClass extends BaseClass {
   function __construct() {
       parent::__construct();
       print "In SubClass constructor\n";
   }
}
析构函数会在到某个对象的所有引用都被删除或者当对象被显式销毁时执行。
class MyDestructableClass {
   function __construct() {
       print "In constructor\n";
       $this->name = "MyDestructableClass";
   }

   function __destruct() {
       print "Destroying " . $this->name . "\n";
   }
} 

析构函数即使在使用 exit() 终止脚本运行时也会被调用。在析构函数中调用 exit() 将会中止其余关闭操作的运行。
## 访问控制
对属性或方法的访问控制，是通过在前面添加关键字 public（公有），protected（受保护）或 private（私有）来实现的。
被定义为公有的类成员可以在任何地方被访问。
被定义为受保护的类成员则可以被其自身以及其子类和父类访问。
被定义为私有的类成员则只能被其定义所在的类访问。
如果用 var 定义，则被视为公有。

类中的方法可以被定义为公有，私有或受保护。如果没有设置这些关键字，则该方法默认为公有。

范围解析操作符（也可称作 Paamayim Nekudotayim）或者更简单地说是一对冒号，可以用于访问静态成员，
类常量，还可以用于覆盖类中的属性和方法。
当在类定义之外引用到这些项目时，要使用类名。
自 PHP 5.3.0 起，可以通过变量来引用类，该变量的值不能是关键字（如 self，parent 和 static）。


## static
声明类属性或方法为静态，就可以不实例化类而直接访问。
静态属性不能通过一个类已实例化的对象来访问（但静态方法可以）。

静态属性不可以由对象通过 -> 操作符来访问。

还可以作为引用关键字，类似于self和parent
public static function who() {
    echo __CLASS__;
}
public static function test() {
    static::who(); // 后期静态绑定从这里开始
}

## 抽象类
PHP 5 支持抽象类和抽象方法。定义为抽象的类不能被实例化。
任何一个类，如果它里面至少有一个方法是被声明为抽象的，那么这个类就必须被声明为抽象的。
被定义为抽象的方法只是声明了其调用方式（参数），不能定义其具体的功能实现。

abstract class AbstractClass
{
 // 强制要求子类定义这些方法
    abstract protected function getValue();
    abstract protected function prefixValue($prefix);

    // 普通方法（非抽象方法）
    public function printOut() {
        print $this->getValue() . "\n";
    }
}

abstract class AbstractClass
{
    // 我们的抽象方法仅需要定义需要的参数
    abstract protected function prefixName($name);

}

class ConcreteClass extends AbstractClass
{

    // 我们的子类可以定义父类签名中不存在的可选参数
    public function prefixName($name, $separator = ".") {
        if ($name == "Pacman") {
            $prefix = "Mr";
        } elseif ($name == "Pacwoman") {
            $prefix = "Mrs";
        } else {
            $prefix = "";
        }
        return "{$prefix}{$separator} {$name}";
    }
}

## interface
使用接口（interface），可以指定某个类必须实现哪些方法，但不需要定义这些方法的具体内容。
接口是通过 interface 关键字来定义的，就像定义一个标准的类一样，但其中定义所有的方法都是空的。
接口中定义的所有方法都必须是公有，这是接口的特性。

接口中也可以定义常量。接口常量和类常量的使用完全相同，但是不能被子类或子接口所覆盖。
## trait
trait ezcReflectionReturnInfo {
    function getReturnType() { /*1*/ }
    function getReturnDescription() { /*2*/ }
}

class ezcReflectionMethod extends ReflectionMethod {
    use ezcReflectionReturnInfo;
    /* ... */
}

class ezcReflectionFunction extends ReflectionFunction {
    use ezcReflectionReturnInfo;
    /* ... */
}从基类继承的成员会被 trait 插入的成员所覆盖。
优先顺序是来自当前类的成员覆盖了 trait 的方法，
而 trait 则覆盖了被继承的方法。

## 匿名类
$util->setLogger(new class {
    public function log($msg)
    {
        echo $msg;
    }
});
## 对象遍历
foreach($this as $key => $value) {
   print "$key => $value\n";
}

## final
PHP 5 新增了一个 final 关键字。如果父类中的方法被声明为 final，则子类无法覆盖该方法。
如果一个类被声明为 final，则不能被继承。


## 对象拷贝
对象复制可以通过 clone 关键字来完成（如果可能，这将调用对象的 __clone() 方法）。对象中的 __clone() 方法不能被直接调用。

$copy_of_object = clone $object;
当对象被复制后，PHP 5 会对对象的所有属性执行一个浅复制（shallow copy）。所有的引用属性 仍然会是一个指向原来的变量的引用。

## 对象比较
当使用比较运算符（==）比较两个对象变量时，比较的原则是：如果两个对象的属性和属性值 都相等，而且两个对象是同一个类的实例，那么这两个对象变量相等。
而如果使用全等运算符（===），这两个对象变量一定要指向某个类的同一个实例（即同一个对象）。
## 序列化
所有php里面的值都可以使用函数serialize()来返回一个包含**字节流的字符串**来表示。
unserialize()函数能够重新把字符串变回php原来的值。 
序列化一个对象将会保存对象的所有变量，但是不会保存对象的方法，只会保存类的名字。
为了能够unserialize()一个对象，这个对象的类必须已经定义过。
如果序列化类A的一个对象，将会返回一个跟类A相关，而且包含了对象所有变量值的字符串。 
如果要想在另外一个文件中解序列化一个对象，这个对象的类必须在解序列化之前定义，
可以通过包含一个定义该类的文件或使用函数spl_autoload_register()来实现。

## namespace
名为PHP或php的命名空间，以及以这些名字开头的命名空间（例如PHP\Classes）被保留用作语言内核使用，而不应该在用户空间的代码中使用。
虽然任意合法的PHP代码都可以包含在命名空间中，但只有以下类型的代码受命名空间的影响，
它们是：类（包括抽象类和traits）、接口、函数和常量。
命名空间通过关键字namespace 来声明。
如果一个文件中包含命名空间，它必须在其它所有代码之前声明命名空间，除了一个以外：declare关键字。
命名空间必须是程序脚本的第一条语句

也可以在同一个文件中定义多个命名空间。在同一个文件中定义多个命名空间有两种语法形式。
namespace MyProject {

const CONNECT_OK = 1;
class Connection { /* ... */ }
function connect() { /* ... */  }
}

namespace AnotherProject {

const CONNECT_OK = 1;
class Connection { /* ... */ }
function connect() { /* ... */  }
}
在实际的编程实践中，非常不提倡在同一个文件中定义多个命名空间。这种方式的主要用于将多个 PHP 脚本合并在同一个文件中。


PHP 命名空间中的元素使用同样的原理。例如，类名可以通过三种方式引用：
1. 非限定名称，或不包含前缀的类名称，例如 $a=new foo(); 或 foo::staticmethod();。
如果当前命名空间是 currentnamespace，foo 将被解析为 currentnamespace\foo。
如果使用 foo 的代码是全局的，不包含在任何命名空间中的代码，则 foo 会被解析为foo。 
警告：如果命名空间中的函数或常量未定义，
则该非限定的函数名称或常量名称会被解析为全局函数名称或常量名称。

2. 限定名称,或包含前缀的名称，
例如 $a = new subnamespace\foo(); 或 subnamespace\foo::staticmethod();。
如果当前的命名空间是 currentnamespace，则 foo 会被解析为 currentnamespace\subnamespace\foo。
如果使用 foo 的代码是全局的，不包含在任何命名空间中的代码，foo 会被解析为subnamespace\foo。

3. 完全限定名称，或包含了全局前缀操作符的名称，
例如， $a = new \currentnamespace\foo(); 或 \currentnamespace\foo::staticmethod();。在这种情况下，foo 总是被解析为代码中的文字名(literal name)currentnamespace\foo。



注意访问任意全局类、函数或常量，都可以使用完全限定名称，
例如 \strlen() 或 \Exception 或 \INI_ALL。
$a = \strlen('hi'); // 调用全局函数strlen
$b = \INI_ALL; // 访问全局常量 INI_ALL
$c = new \Exception('error'); // 实例化全局类 Exception


别名：
在PHP中，别名是通过操作符 use 来实现的. 下面是一个使用所有可能的五种导入方式的例子：
Example #1 使用use操作符导入/使用别名
use My\Full\Classname as Another;


## Exception/Error
Error 类并非继承自 Exception 类，所以不能用 catch (Exception $e) { ... } 来捕获 Error。你可以用 catch (Error $e) { ... }，或者通过注册异常处理函数（ set_exception_handler()）来捕获 Error。

try {
    echo inverse(5) . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
} finally {
    echo "First finally.\n";
}

Exception {
/* 属性 */
protected string $message ;
protected int $code ;
protected string $file ;
protected int $line ;

## 引用
引用传递
可以将一个变量通过引用传递给函数，这样该函数就可以修改其参数的值。语法如下：
function foo(&$var)
{
    $var++;
}

$a=5;
foo($a);
// $a is 6 here

返回引用
引用返回用在当想用函数找到引用应该被绑定在哪一个变量上面时。
不要用返回引用来增加性能，引擎足够聪明来自己进行优化。
仅在有合理的技术原因时才返回引用！要返回引用，使用此语法：
class foo {
    public $value = 42;

    public function &getValue() {
        return $this->value;
    }
}

当 unset 一个引用，只是断开了变量名和变量内容之间的绑定。这并不意味着变量内容被销毁了。例如：
$a = 1;
$b =& $a;//PHP 的引用允许用两个变量来指向同一个内容
unset($a);


## 预定义变量

1. 超全局变量
$GLOBALS 一个包含了全部变量的全局组合数组。变量的名字就是数组的键。
$_SERVER $_SERVER 是一个包含了诸如头信息(header)、路径(path)、以及脚本位置(script locations)等等信息的数组。这个数组中的项目由 Web 服务器创建
$_GET 通过 URL 参数传递给当前脚本的变量的数组。
$_POST 当 HTTP POST 请求的 Content-Type 是 application/x-www-form-urlencoded 或 multipart/form-data 时，会将变量以关联数组形式传入当前脚本。
$_FILES 通过 HTTP POST 方式上传到当前脚本的项目的数组
$_COOKIE 
$_SESSION
$_REQUEST 默认情况下包含了 $_GET，$_POST 和 $_COOKIE 的数组。
$_ENV 通过环境方式传递给当前脚本的变量的数组。




"" 双引号里面的字段会经过编译器解释，然后再当作HTML代码输出。
'' 单引号里面的不进行解释，直接输出。
从字面意思上就可以看出，单引号比双引号要快了。