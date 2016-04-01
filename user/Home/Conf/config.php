<?php
$db = require_once('db.php');
$protocol = require_once('protocol.php');
$verify = require_once('verify.php');
$config = array(
    'TOKEN_KEY' => '123456',//登陆密钥盐

    //全局变量
    'G_BEHAVE' => '',//当前协议的行为代号
    'G_REDIS' => false,//REDIS是否有问题(false为没有问题，否则则为出问题的库号)
    'G_STATIC' => false,//静态表是否问题(false为没有问题，否则则为出问题的表名)
    'G_TRANS' => false,//是否启用了事务
    'G_TRANS_FLAG' => false,//事务是否有错
    'G_ERROR' => null,//错误提示
    'G_SQL' => array(),//trans过程中的所有SQL
    'G_SQL_ERROR' => array(),//所有报错的SQL

    'DB_FIELDS_CACHE' => true,          // 禁用字段缓存(不同库中有相同名字的表)
);
return array_merge($config, $db, $protocol, $verify);
