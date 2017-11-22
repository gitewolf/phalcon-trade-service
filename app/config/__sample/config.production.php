<?php
return
    [
        'error' => [
            'logger' => [
                'adapter' => 'file',
                'path' => JDS_DIR_ROOT . '/runtime/log/error.log'
            ],
        ],

        'logger' => array(
            'adapter'  => 'file',
            'path'     => JDS_DIR_ROOT . '/runtime/log/',
        ),

        'application' => array(
            'controllersDir' => JDS_DIR_ROOT . "/app/controllers/",
            'modelsDir'      => JDS_DIR_ROOT . "/app/models/",
            'viewsDir'       => JDS_DIR_ROOT . "/app/views/",
            'pluginsDir'     => JDS_DIR_ROOT . "/app/plugin/",
            'libraryDir'     => JDS_DIR_ROOT . "/library/",
            'cacheDir'       => JDS_DIR_ROOT . "/runtime/tmp/",
            'baseUri'        => '/',
        ),

        'elastic' => [//Elastic Search
            'nodes' => [
                [
                    'host' => '127.0.0.1',
                    'port' => 9200
                ]
            ]
        ],


        'db' => [
            'host' => '127.0.0.1',
            'port' => '3306',
            'username' => 'pik',
            'password' => '123456',
            'dbname' => 'jds_pro',
            'charset' => 'utf8mb4',
            'persistent' => false,
        ],

        'redis' => [
            'host' => '127.0.0.1',
            'port' => '6379',
            'timeout' => 600,
            'db' => 6,
            'persistent' => TRUE,
        ],

        //ServiceApi
        'sns_service' => [
            'url' => 'http://sns.service.jackydeng.com/',
            'port' => '80',
        ],

        //StoreService
        'store_service' => [
            'url' => 'http://store.service.jackydeng.com/',
            'port' => '80',
        ],
        //ProService
        'pro_service' => [
            'url' => 'http://pro.service.jackydeng.com/',
            'port' => '80',
        ],

        // 个人沙箱帐号：
        /*
        * 商家账号  ibxkna9622@sandbox.com
        * 商户UID   2088102172161773
        * 登录密码   111111
        */
        /*
         * 买家账号    lxyqfv7169@sandbox.com
         * 登录密码    111111
         * 支付密码    111111
         */

        'aliPay' => [
            'use_sandbox' => true,// 是否使用沙盒模式
//    'partner'                   => '2088811060326162',
            'partner' => '2088102172161773',
            'app_id' => '2016082000291710',
            'sign_type' => 'RSA2',// RSA  RSA2

            // 可以填写文件路径，或者密钥字符串  当前字符串是 rsa2 的支付宝公钥(开放平台获取)
            //'ali_public_key'            => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuu4Z/FSWeGOpwY5yD7EWdjsVm6McpCd96/1qDYhtwiZcJ54xbw4SGhhUWcWOa5NpP3XlC7YxfYPXtxTHiY1xdKovxLlmFySi/yoTXP0MWeyiVpLp/BSeFvqgMVsDE2gFnPU4lxcmaa/gnZ1LXtQcd+0ybdkagvuWE0QQ2EuvhnbmaARnB4FALsl9YW1yHRKWXtFjH9reelL5Nf7rmPd+615i8WuQHslNKm3cHHE96X/kDiQBE7+PqSMocfjtC/PlsYjf0f0K5mb0PBZAcWORVC+tna8m0RP2FresO5bLwOBjQziwei+XLHiuio4lqZzeaE/7cSUWj4qFETr8AfsZ2wIDAQAB',
            //支付宝公钥,支付宝公钥,支付宝公钥
            'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAsti8BjbGDr1MHRL22QGFBux99mqVowoBB+qCCEVg1eDBO7LgV8TMyCzZCd6CyxHVHiv6x8hpm5P4auPq4kbeRUZVQXVEHjOgxs1Ec6KiHAobTkyzM9n3tX8Tiv+f/oQ1e4n6jxb8Qn7t3+vGBhAMWlTmsvtfvj0oRZo8z6DjXfMc/5umxPjWVP5dBMdLHSwqtdYniS0xzfVKqXBfoapxnAOMhGf3b0GSpe1XonQbO0Wjs4TVj1DmJggbYQg0fo+REMC8FfUj8kup8sGwLuAcvb5SJUn218PiJRca3fzm9e4s+0kwE1FT47FgD1HfUjdV3Y+dh0UUl4WvFcwAXIRG7wIDAQAB',
            // 可以填写文件路径，或者密钥字符串  我的沙箱模式，rsa与rsa2的私钥相同，为了方便测试
            'rsa_private_key' => 'MIIEowIBAAKCAQEAuu4Z/FSWeGOpwY5yD7EWdjsVm6McpCd96/1qDYhtwiZcJ54xbw4SGhhUWcWOa5NpP3XlC7YxfYPXtxTHiY1xdKovxLlmFySi/yoTXP0MWeyiVpLp/BSeFvqgMVsDE2gFnPU4lxcmaa/gnZ1LXtQcd+0ybdkagvuWE0QQ2EuvhnbmaARnB4FALsl9YW1yHRKWXtFjH9reelL5Nf7rmPd+615i8WuQHslNKm3cHHE96X/kDiQBE7+PqSMocfjtC/PlsYjf0f0K5mb0PBZAcWORVC+tna8m0RP2FresO5bLwOBjQziwei+XLHiuio4lqZzeaE/7cSUWj4qFETr8AfsZ2wIDAQABAoIBABdJ9NjYHQsQt6SSv6ku0fKW9+E1GCtndCvDncPj3HDU4KRa9CO26BByOYgZsd27NuCeKpQj9dSCaYy4vQdpJNp5HYxv4MU/XkNWFaV/LVvCvJL2qMosM2n/fZfIlACF9DUS5CMG0lVWgTCB2VMOMCHM3nyLEN+CwrbRwxt/inXBAfPitY+Se213kEHzIYrCMoT0mchN92HAuyMNfYsIl7hVfjo8oXIPl7a0QGDER0y89Zj8grcjT1pXORyZnGPXh8byxUX5L2urY4nKwU5uWqS4eu70CeT3tYlFuO5hTxgHHDVZEdbWE7OZlyUp6DPwXi/Q23Q/UlevCoiNPs2N2SkCgYEA49n502LmmpqoitcN9IxFqAO0u2J7S5KMSAqQtgIowephNJCtft/pW6mQM8lzKVvufIAlXx9UWnlHSraVxdfi/IAPl7TwTpz+hzxYnwoz5HQ6VXuECNLfMQDntt2z1LLKyq1cXh+sRa8+xHTjwSArutLQjwTQG5OZgG+hToxyr+cCgYEA0gXyc7dqLK6HSxj8tkmK5Gl/1YTB0E3T5TEN75eOSJdaWH5wQiKXxmoiwfh8dqg1z5Y0sfS7RM/WPV1+Jg96rXtDKKySKbbmVZKHIqK8ozc1+W7L/vP3TTDMqPSYNC73tXqTEw3bT24Syx2AllSwD6s5Zy8TFqtB3x0zvJvrl+0CgYAAh0jlDJ2sTh+F8um9X3Xp5dhNdvUCP9zDbgLX6Tle+cQv8wXz/WD1LXeAJz91IRl0gHeVuOThMNbRfYrrYozMOR/QIkNMa2DLv4AVHljwdrSL7jVoL9UEsBPZgLoiDCgcQDqunTQxFS74Fa1RjVmMnWCOdFxnM/hvK9Mb84dwkQKBgEkPRI44ibjNZcccBB0tbCGVCaEvM3TQ/htGe0CTii16aTVLlqWK/x1IopqzZCiqzz1NVTtqlRKU8kQal92JmPVsYapujdHxDCNMe7HyxohIloAUqOYh3C+AAFHt9FyC0izRXQRN17LD6cm6k5a4Ex8AQ1G/sHY8UQfaUrsTylrpAoGBAMOAzS389n3R0v0AZxfpYGtlKhOdusSBxnx7Wfp+iSG6AhiO5zYrVYPjHxOdv01Wdut8nuY4SAv41r9UcqKOnTLe34MNBVXdmH1bbRp0k85xg42Sl4zXPkD3xAhFI9JLGgsyWcCprcFzSU42SsHSI5WliXxoOE7HjKOW5DHLXEts',
            'limit_pay' => [
                //'balance',// 余额
                //'moneyFund',// 余额宝
                //'debitCardExpress',// 	借记卡快捷
                //'creditCard',//信用卡
                //'creditCardExpress',// 信用卡快捷
                //'creditCardCartoon',//信用卡卡通
                //'credit_group',// 信用支付类型（包含信用卡卡通、信用卡快捷、花呗、花呗分期）
            ],// 用户不可用指定渠道支付当有多个渠道时用“,”分隔
            // 与业务相关参数
            'notify_url' => 'http://test.pro-api.jackydeng.com/pay/aliNotify',
            'return_url' => 'http://pro.jackydeng.com',
            'return_raw' => false,// 在处理回调时，是否直接返回原始数据，默认为 true
        ],

    ];
