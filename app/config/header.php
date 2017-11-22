<?php
/**
 * 引导文件
 *
 * @link http://www.jackydeng.com/
 * @copyright Copyright (c) 2014 jacky deng
 */

// -- 常量定义 ----------------------------------------------------------------------------------------------------

!defined('JDS_APP_NAME') && define('JDS_APP_NAME', 'PYK_SNS'); //应用名称
!defined('JDS_APP_VERSION') && define('JDS_APP_VERSION', '2.0.0'); //应用版本

!defined('JDS_TIME') && define('JDS_TIME', $_SERVER['REQUEST_TIME']); //请求时间戳
!defined('JDS_MICROTIME') && define('JDS_MICROTIME', $_SERVER['REQUEST_TIME_FLOAT']); //请求时间戳(微秒)

!defined('JDS_DIR_ROOT') && define('JDS_DIR_ROOT', dirname(dirname(__DIR__))); //应用程序根路径
!defined('JDS_DIR_APP') && define('JDS_DIR_APP', JDS_DIR_ROOT . '/app'); //app文件夹路径
!defined('JDS_DIR_RUNTIME') && define('JDS_DIR_RUNTIME', JDS_DIR_ROOT . '/runtime'); //运行时临时文件路径
!defined('JDS_DIR_PUBLIC') && define('JDS_DIR_PUBLIC', JDS_DIR_ROOT . '/public'); //web站点根路径
!defined('JDS_DIR_CONFIG') && define('JDS_DIR_CONFIG', JDS_DIR_APP . '/config'); //配置文件路径

!defined('JDS_SITEDIR_PATH') && define('JDS_SITEDIR_PATH', ''); //JDS_SITEDIR_PATH

//参考上方代码,可在此处添加你的常量定义
//!defined('常量名称') && define('常量名称', '值'); //注释

// --路由管理
if (!defined('JDS_HTTP_SCHEME')) {
    if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) {
        define('JDS_HTTP_SCHEME', 'https://');
    } else {
        define('JDS_HTTP_SCHEME', 'http://');
    }
}
// -- 环境检测 ----------------------------------------------------------------------------------------------------

$_JDS_RUNMODE = '';

if (is_file(JDS_DIR_CONFIG . '/config.production.php')) {
    $_JDS_RUNMODE = 'production';
} elseif (is_file(JDS_DIR_CONFIG . '/config.testing.php')) {
    $_JDS_RUNMODE = 'testing';
} elseif (is_file(JDS_DIR_CONFIG . '/config.development.php')) {
    $_JDS_RUNMODE = 'development';
} else {
    die('Runmode Error!');
}

!defined('JDS_RUNMODE') && define('JDS_RUNMODE', $_JDS_RUNMODE); //应用程序运行环境
!defined('JDS_RUNMODE_PRODUCTION') && define('JDS_RUNMODE_PRODUCTION', JDS_RUNMODE == 'production'); //是否产品环境
!defined('JDS_RUNMODE_TESTING') && define('JDS_RUNMODE_TESTING', JDS_RUNMODE == 'testing'); //是否测试环境
!defined('JDS_RUNMODE_DEVELOPMENT') && define('JDS_RUNMODE_DEVELOPMENT', JDS_RUNMODE == 'development'); //是否开发环境
!defined('JDS_DEBUG') && define('JDS_DEBUG', JDS_RUNMODE_DEVELOPMENT || JDS_RUNMODE_TESTING); //测试和开发环境开启Debug

unset($_JDS_RUNMODE);


// -- 自动加载 ----------------------------------------------------------------------------------------------------

if (!isset($_JDS_LOADER)) {
    $_JDS_LOADER = new Phalcon\Loader();
    $_JDS_LOADER->register();
}
