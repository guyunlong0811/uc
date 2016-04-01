<?php
namespace Home\Controller;

use Think\Controller;

class RouterController extends Controller
{

    private $mResult = false;//处理结果
    private $mProtocol = false;//处理结果
    private $mRequest = array();//请求数据
    private $mRespond = array();//回应数据

    /******************************************REQUEST开始******************************************/

    public function request()
    {

        //取得post字符串
        $post = file_get_contents("php://input");
        //$post = $_GLOBALS['HTTP_RAW_POST_DATA'];

        //解析字符串
        $this->mRequest = json_decode(trim($post), true);//解析结果为array
        $_POST = $this->mRequest['params'];

        //解析方法
        $method = explode('.', $this->mRequest['method']);

        //获取验证字段名
        $this->mProtocol = get_config('protocol', array($method[0], $method[1],));
        if (!isset($this->mProtocol)) {
            C('G_ERROR', 'protocol_error');
            return false;
        }

        //参数&sign验证
        $verify = $this->verify();
//        dump($verify);
        if ($verify !== true) {
            C('G_ERROR', 'params_error');
            C('G_DEBUG_PARAMS', $verify);
            return false;
        }

        //分发功能
        $c = $method[0];
        $a = $method[1];
        $this->mResult = A($c, 'Api')->$a();
        return true;

    }

    //验证sign
    private function verify()
    {

        //数据处理
        if (isset($this->mProtocol['params'])) {
            foreach ($this->mProtocol['params'] as $key => $value) {

                //不存在报错
                if (!isset($_POST[$key])) {
                    return $key;
                }

                //url解码
                if ($key != 'channel_token') {
                    $_POST[$key] = urldecode($_POST[$key]);
                }

                //去除前后空格
                $_POST[$key] = trim($_POST[$key]);

                switch ($value['type']) {
                    case 'number':
                        if (!is_numeric($_POST[$key])) {
                            return $key;
                        }
                        if (isset($value['regex']) && !preg_match($value['regex'], $_POST[$key])) {
                            return $key;
                        }
                        break;

                    case 'string':
                        if (!is_string($_POST[$key])) {
                            return $key;
                        }
                        if (isset($value['regex']) && !preg_match($value['regex'], $_POST[$key])) {
                            return $key;
                        }
                        break;
                    case 'json':
                        $_POST[$key] = json_decode($_POST[$key], true);
                        if (json_last_error() != JSON_ERROR_NONE) {
                            return $key;
                        }
                        break;
                }

            }
        }

        //检查时间戳
        if (isset($_POST['pts']) && abs(time() - $_POST['pts']) > get_config('uc_verify', 'time_limit')) {
            return 'pts';
        }

        //检查时间戳
        if (abs(time() - $_POST['timestamp']) > get_config('uc_verify', 'time_limit')) {
            return 'timestamp';
        }

        //生成sign
        $mySign = uc_sign_create($_POST, 'request');

        //比较sign
        if ($this->mRequest['sign'] != $mySign)
            return 'sign';

        return true;

    }

    /******************************************REQUEST结束******************************************/

    /******************************************返回方法开始******************************************/

    private function respond()
    {
        //返回参数
        if ($this->mResult !== false) {
            if ($this->mResult !== true) {
                if (isset($this->mProtocol['key'])) {
                    $rs = $this->mResult;
                    $this->mResult = array();
                    $this->mResult[$this->mProtocol['key']] = $rs;
                } else if ($this->mResult === true) {
                    $this->mResult = array();
                }
                $this->mRespond['result'] = $this->mResult;
            }
            $this->mRespond['result']['timestamp'] = time();
            $this->mRespond['sign'] = uc_sign_create($this->mRespond['result'], 'respond');
        } else {
            $this->mRespond['error']['code'] = C('G_ERROR');
            //参数错误
            $params = C('G_DEBUG_PARAMS');
            if (!empty($params)) {
                $this->mRespond['error']['debug'] = $params;
            }
            $this->mRespond['error']['timestamp'] = time();
            $this->mRespond['sign'] = uc_sign_create($this->mRespond['error'], 'respond');

        }
        //输出结果
        header_info('json');
        echo json_encode($this->mRespond);
        return;
    }

    /******************************************返回方法结束******************************************/

    public function _empty()
    {//空操作
        return false;
    }

    public function __destruct()
    {
        $this->respond();//返回方法
    }

}