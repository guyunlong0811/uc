<?php
namespace Home\Controller;

use Think\Controller;

class TestController extends Controller
{

    private $controller = 'User';//控制器名称
    private $action = 'getServerList';//方法名称
    private $host = 'http://localhost/FGUserCenter/user.php?c=Router&a=request';//请求地址
    private $json = array();//请求地址

    //测试数据
    private $TestData = array(

        'User' => array(

            'getServerList' => array(
                'gid' => 1,
            ),

            'fast' => array(
                'udid' => '1q2w3e4r5t6y7u8i9o01q2w3e4r5t6y7u8i9o10001',
                'channel_id' => 10001,
                'channel_uid' => 'apple_81623510237',
                'gid' => 1,
                'sid' => 102,
            ),

            'login' => array(
                'udid' => 'kkdi383hf7w9fhw89hf89w3jfw3hrwi3hrlw',
                'username' => 'guyunlong',
                'password' => '123456',
                'gid' => 1,
                'sid' => 102,
                'pts' => 0,
            ),

            'usernameCheck' => array(
                'username' => 'guyunlong',
            ),

            'register' => array(
                'udid' => 'kkdi383hf7w9fhw89hf89w3jfw3hrwi3hrlw',
                'channel_id' => 1001,
                'channel_uid' => '12342314323432',
                'username' => 'guyunlong',
                'password' => '123456',
                'gid' => 1,
                'sid' => 102,
            ),

            'changePwd' => array(
                'uid' => 3,
                'password' => '654321',
                'newPassword' => '123456',
                'pts' => 0,
            ),

            'complete' => array(
                'udid' => 'kkdi383hf7w9fhw89hf89w3jfw3hrwi3hrlw',
                'channel_id' => 1001,
                'channel_uid' => '12342314323432',
                'username' => 'guyunlong1',
                'password' => '123456',
            ),

            'email' => array(
                'uid' => 10000,
                'email' => 'gu.yunlong@forevergame.com',
            ),

            'phone' => array(
                'uid' => 10000,
                'phone' => '13764426340',
            ),

            'getChannelUid' => array(
                'uid' => 10000,
            ),

        ),

        'Exchange' => array(

            'index' => array(
                'uid' => 1,
                'gid' => 1,
                'sid' => 102,
                'level' => 1,
                'code' => 'yye8mg2',
            ),

            'add' => array(
                'uid' => 1,
                'gid' => 1,
                'type' => 2,
                'server' => '[1,2]',
                'starttime' => 1400000000,
                'endtime' => 1500000000,
                'reason' => 'test',
            ),

            'open' => array(
                'id' => 1,
            ),

        ),

    );

    public function _initialize()
    {

        header_info();

        //配置协议
        if (isset($_GET['controller']))
            $this->controller = $_GET['controller'];

        if (isset($_GET['action']))
            $this->action = $_GET['action'];

        $this->json['method'] = $this->controller . '.' . $this->action;

    }

    public function index()
    {

        if (isset($this->TestData[$this->controller][$this->action])) {
            $params = $this->TestData[$this->controller][$this->action];
        } else {
            $params = array();
        }
        $params['timestamp'] = time();

        //获取协议配置
        $protocol = get_config('protocol', array($this->controller, $this->action,));

        //制造params

        foreach ($protocol['params'] as $key => $value) {
            if ($key == 'password') {
                if ($this->action == 'register') {
                    $this->json['params'][$key] = md5($params[$key]);
                } else {
                    $this->json['params'][$key] = md5(md5($params[$key]) . $params['timestamp'] . get_config('uc_verify', 'password'));
                }
            } else if ($key == 'newPassword') {
                $this->json['params'][$key] = md5($params[$key]);
            } else if ($key == 'pts') {
                $this->json['params'][$key] = $params['timestamp'];
            } else {
                $this->json['params'][$key] = $params[$key];
            }
        }
        $this->json['params']['timestamp'] = $params['timestamp'];//生成时间

        //生成sign
        $this->json['sign'] = uc_sign_create($this->json['params'], 'request');

        //生成json
        $post = json_encode($this->json);
//        dump($post);

        //发送协议
        $ret = curl_link($this->host, 'post', $post);
        echo '<div style="max-width:960px; word-break:break-all;">';
        echo $this->host;
        echo '<hr />';
        echo $post;
        echo '<hr />';
        //打印结果
        echo($ret);
        echo "</div>";

        //解析结果
        $ret = json_decode($ret, true);
        dump($ret);

    }

    public function export()
    {

        $now = time();

        $sql = "truncate `account`;";
        M()->execute($sql);
        $sql = "insert into `account` (`udid`,`username`,`password`,`channel_id`,`channel_uid`,`ctime`,`last_login_time`,`status`) values ";
        for ($i = 1; $i <= 100; ++$i) {
            $udid = $this->getRandChar(40);
            $password = md5(123456);
            $sql .= "('{$udid}','guyunlong{$i}','{$password}','1001','0','{$now}','{$now}','1'),";
        }
        $sql = substr($sql, 0, -1) . ';';
        if (!M()->execute($sql)) $rs = false;
        unset($sql);
    }

    public function getRandChar($length)
    {
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;

        for ($i = 0; $i < $length; $i++) {
            $str .= $strPol[rand(0, $max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }

        return $str;
    }

}