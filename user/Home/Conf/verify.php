<?php
//验证配置
return array(
    'UC_VERIFY' => array(//通讯密钥
        'time_limit' => 600,//时间容错率
        'request' => 'forever!23',//salt:通讯密钥;
        'respond' => 'forever!23',
        'password' => 'fgpwdsalt',
    ),
);
