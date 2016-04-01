<?php
return array(

    'PROTOCOL' => array(

        'User' => array(

            'getServerList' => array(
                'key' => 'list',
                'params' => array(
                    'gid' => array('type' => 'number',),
                ),
            ),

            'fast' => array(
                'params' => array(
                    'mac' => array('type' => 'string', 'regex' => '/^\w|-{15,64}$/',),
                    'udid' => array('type' => 'string', 'regex' => '/^\w|-{15,64}$/',),
                    'channel_id' => array('type' => 'number',),
                    'channel_uid' => array('type' => 'string',),
                    'gid' => array('type' => 'number',),
                    'sid' => array('type' => 'number',),
                    'ip' => array('type' => 'string',),
                ),
            ),

            'login' => array(
                'params' => array(
                    'username' => array('type' => 'string', 'regex' => '/^\w{6,16}$/',),
                    'password' => array('type' => 'string', 'regex' => '/^\w{32}$/',),
                    'udid' => array('type' => 'string', 'regex' => '/^\w{15,64}$/',),
                    'gid' => array('type' => 'number',),
                    'sid' => array('type' => 'number',),
                    'pts' => array('type' => 'number',),
                ),
            ),

            'usernameCheck' => array(
                'key' => 'count',
                'params' => array(
                    'username' => array('type' => 'string', 'regex' => '/^\w{6,16}$/',),
                ),
            ),

            'complete' => array(
                'key' => 'uid',
                'params' => array(
                    'udid' => array('type' => 'string', 'regex' => '/^\w{15,64}$/',),
                    'channel_id' => array('type' => 'number',),
                    'channel_uid' => array('type' => 'string',),
                    'username' => array('type' => 'string', 'regex' => '/^\w{6,16}$/',),
                    'password' => array('type' => 'string', 'regex' => '/^\w{32}$/',),
                ),
            ),

            'register' => array(
                'key' => 'uid',
                'params' => array(
                    'udid' => array('type' => 'string', 'regex' => '/^\w{15,64}$/',),
                    'username' => array('type' => 'string', 'regex' => '/^\w{6,16}$/',),
                    'password' => array('type' => 'string', 'regex' => '/^\w{32}$/',),
                    'channel_id' => array('type' => 'number',),
                    'channel_uid' => array('type' => 'string',),
                    'gid' => array('type' => 'number',),
                    'sid' => array('type' => 'number',),
                    'ip' => array('type' => 'string',),
                ),
            ),

            'changePwd' => array(
                'params' => array(
                    'uid' => array('type' => 'number',),
                    'password' => array('type' => 'string', 'regex' => '/^\w{32}$/',),
                    'newPassword' => array('type' => 'string', 'regex' => '/^\w{32}$/',),
                    'pts' => array('type' => 'number',),
                ),
            ),

            'binding' => array(
                'params' => array(
                    'udid' => array('type' => 'string', 'regex' => '/^\w{15,64}$/',),
                    'username' => array('type' => 'string', 'regex' => '/^\w{6,16}$/',),
                    'password' => array('type' => 'string', 'regex' => '/^\w{32}$/',),
                ),
            ),

            'email' => array(
                'key' => 'row',
                'params' => array(
                    'uid' => array('type' => 'number',),
                    'email' => array('type' => 'string', 'regex' => '/^(\w)+(\.\w+)*@(\w)+((\.\w+)+)$/',),
                ),
            ),

            'phone' => array(
                'key' => 'row',
                'params' => array(
                    'uid' => array('type' => 'number',),
                    'phone' => array('type' => 'number', 'regex' => '/^1\d{10}$/',),
                ),
            ),

            'ident' => array(
                'params' => array(
                    'uid' => array('type' => 'number',),
                    'realname' => array('type' => 'string', 'regex' => '/^[\u4e00-\u9fa5]{2,4}$/',),
                    'ident' => array('type' => 'number', 'regex' => '/^[1-9]\d{16}(\d|x)$/',),
                ),
            ),

            'getChannelUid' => array(
                'key' => 'channel_uid',
                'params' => array(
                    'uid' => array('type' => 'number',),
                ),
            ),

        ),

        'Exchange' => array(

            'index' => array(
                'key' => 'goods',
                'params' => array(
                    'gid' => array('type' => 'number',),
                    'sid' => array('type' => 'number',),
                    'channel_id' => array('type' => 'number',),
                    'uid' => array('type' => 'number',),
                    'code' => array('type' => 'string',),
                    'level' => array('type' => 'number',),
                ),
            ),

            'activation' => array(
                'params' => array(
                    'gid' => array('type' => 'number',),
                    'sid' => array('type' => 'number',),
                    'channel_id' => array('type' => 'number',),
                    'uid' => array('type' => 'number',),
                    'code' => array('type' => 'string',),
                ),
            ),

        ),

    ),

);