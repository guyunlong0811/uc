<?php
/************************ 服务器 **************************/
//获取服务器列表
function get_server_list()
{

    $serverList = apc_fetch('server');

    if (empty($serverList)) {

        $params['gid'] = C('GAME_ID');
        $params['timestamp'] = time();
        $post = array();
        $post['method'] = 'User.getServerList';
        $post['params'] = $params;
        $post['sign'] = uc_sign_create($params, 'request');
        $post = json_encode($post);
        //发送协议
        $json = curl_link(UC_URL . '?c=Router&a=request', 'post', $post);
        //解码
        $uc = json_decode($json, true);
        //登录用户中心
        if (empty($uc['result']['list'])) {
            C('G_ERROR', 'db_error');
            return false;
        }

        foreach ($uc['result']['list'] as $value) {
            $serverList[$value['sid']]['channel_id'] = $value['channel_id'];
            $serverList[$value['sid']]['name'] = $value['name'];
            $serverList[$value['sid']]['dbname'] = $value['dbname'];
            $serverList[$value['sid']]['master']['DB_DEPLOY_TYPE'] = 0;
            $serverList[$value['sid']]['master']['DB_RW_SEPARATE'] = false;
            $serverList[$value['sid']]['master']['DB_HOST'] = $value['db_m_host'];
            $serverList[$value['sid']]['master']['DB_USER'] = $value['db_m_user'];
            $serverList[$value['sid']]['master']['DB_PWD'] = $value['db_m_pwd'];
            $serverList[$value['sid']]['master']['DB_PORT'] = $value['db_m_port'];
            $serverList[$value['sid']]['all']['DB_DEPLOY_TYPE'] = 1;
            $serverList[$value['sid']]['all']['DB_RW_SEPARATE'] = true;
            $serverList[$value['sid']]['all']['DB_HOST'] = $value['db_m_host'] . ',' . $value['db_s_host'];
            $serverList[$value['sid']]['all']['DB_USER'] = $value['db_m_user'] . ',' . $value['db_s_user'];
            $serverList[$value['sid']]['all']['DB_PWD'] = $value['db_m_pwd'] . ',' . $value['db_s_pwd'];
            $serverList[$value['sid']]['all']['DB_PORT'] = $value['db_m_port'] . ',' . $value['db_s_port'];
            $serverList[$value['sid']]['redis']['host'] = $value['redis_host'];
            $serverList[$value['sid']]['redis']['port'] = $value['redis_port'];
            $serverList[$value['sid']]['redis']['game'] = $value['redis_game'];
            $serverList[$value['sid']]['redis']['social'] = $value['redis_social'];
            $serverList[$value['sid']]['redis']['fight'] = $value['redis_fight'];
            $serverList[$value['sid']]['script'] = $value['script_url'];
            $serverList[$value['sid']]['callback'] = $value['callback'];
        }

        //存储缓存
        apc_store('server', $serverList);

    }

    return $serverList;
}

//改变数据库配置
function change_db_config($type)
{
    switch ($type) {
        case 'master':
            $config = get_config('DB_UC_MASTER');
            break;
        case 'all':
            $config = get_config('DB_UC_ALL');
            break;
    }
    C($config);
    return;
}

//写文件
function write_log($str, $path, $type = 1)
{

    $path = LOG_PATH . $path;
    if (!is_dir($path)) {
        mkdir($path);
    }// 如果不存在则创建
    switch ($type) {
        case 1:
            $filename = date('Ymd');
            break;
        case 2:
            $filename = date('Ym');
            break;
    }
    $file = $path . $filename . ".log";
    $wfp = fopen($file, "a");
    fputs($wfp, $str . "\r\n");
    fclose($wfp);
    return;
}

//保存SQl至配置
function save_sql($sql, $error = false)
{

    if ($error || C('G_ERROR') == 'db_error') {
        $sqlList = C('G_SQL_ERROR');
        $sqlList[] = $sql;
        C('G_SQL_ERROR', $sqlList);
    }

    if (C('G_TRANS')) {
        $sqlList = C('G_SQL');
        $sqlList[] = $sql;
        C('G_SQL', $sqlList);
    }

    return;

}

//获取Predis客户端对象
function get_predis($db)
{
    $list = get_server_list();
    $sid = C('G_SID');
    $redis = $list[$sid]['redis'];
    $server = array('host' => $redis['host'], 'port' => $redis['port'], 'database' => $redis[$db]);
    require_once(APP_PATH . '../Predis/Autoloader.php');
    Predis\Autoloader::register();
    $client = new Predis\Client($server);
    return $client;
}

//返回
function header_info($type = 'html', $charset = 'utf-8')
{
    switch ($type) {
        case 'html':
            $type = 'text/html';
            break;
        case 'json':
            $type = 'application/json';
            break;
    }
    header("Content-type:{$type}; charset={$charset}");
}

//时间格式转化方法
function time2format($time = null, $k = 1)
{

    $format = array(
        1 => "Y-m-d H:i:s",
        2 => "Y-m-d",
        3 => "Y/m/d",
        4 => "H:i:s",
        5 => "H:i",
        6 => "Ymd",
    );

    if ($time === null)
        return date($format[$k]);

    if ($time <= 0)
        return false;

    return date($format[$k], $time);

}

//curl链接
function curl_link($host, $method = 'get', $data = '', $cookie = '', $return = true, $agent = 'WEBSERVER')
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $host);

    if (strtolower($method) == 'post')
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    if (!empty($cookie))
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, $return);
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    $retData = curl_exec($ch);
    curl_close($ch);
    return $retData;
}

//AppStore充值验证
function verify_app_store($receipt, $is_sandbox = false)
{
    //$sandbox should be TRUE if you want to test against itunes sandbox servers
    if ($is_sandbox) {
        $url = "https://sandbox.itunes.apple.com/verifyReceipt";
    } else {
        $url = "https://buy.itunes.apple.com/verifyReceipt";
    }

    $receipt = json_encode(array("receipt-data" => $receipt));
    $response_json = curl_link($url, 'post', $receipt);
    $response = json_decode($response_json, true);

//    $strLog = 'Receipt : '.$receipt."\n";
    $strLog = 'Verify : ' . $response_json . "\n";
    if ($response['status'] == 0) {//eithr OK or expired and needs to synch
//        $strLog .= "Verify OK\n";
        $return = $response;
    } else {
//        $strLog .= "Verify failed\n";
        $return = false;
    }

    $strLog .= "================================================\n";
    write_log($strLog, 'pay/apple/');
    return $return;

}

/************************ 服务器 **************************/

/************************ 游戏逻辑 **************************/
//过去最近的服务器自动更新时间
function get_daily_utime()
{
    $todayUtime = strtotime(time2format(null, 2) . ' ' . C('DAILY_UTIME'));
    $time = time() < $todayUtime ? ($todayUtime - 86400) : $todayUtime;
    return $time;
}

//解析数据
function get_error()
{
    $error = C('G_ERROR') ? C('G_ERROR') : 'unknown';
    $errorInfo = get_config('error', $error);//返回错误信息
    return $errorInfo;
}

//生成sign
function sign_create($id, $sid, $method, $params, $type, $ver)
{

    //获取SALT
    $salt = get_config('verify', array($ver, $type, 'salt'));

    //排序
    ksort($params);

    //创建加密字符串
    $strSign = $id . '&' . $sid . '&' . $method . '&';
    foreach ($params as $value) {
        if (is_array($value))
            $strSign .= json_encode($value) . '&';
        else
            $strSign .= $value . '&';
    }
    $strSign .= $salt;
//    dump($strSign);
    $strSign = strtolower(md5($strSign));
    return $strSign;
}

//生成sign
function uc_sign_create($params, $type)
{
    //获取SALT
    $salt = get_config('uc_verify', $type);
    //排序
    ksort($params);
    //创建加密字符串
    $strSign = '';
    foreach ($params as $value) {
        if (is_array($value)) {
            $value = json_encode($value);
        }
        $strSign .= $value . '&';
    }
    $strSign .= $salt;
    $strSign = strtolower(md5($strSign));
    return $strSign;
}

//获取LUA脚本
function lua($file, $func, $argc = array(), $dir = false)
{

    $inc = get_config('LUA_URL');
    if ($dir)
        $inc .= $dir . '/';
    $inc .= $file . '.lua';
    $lua = new Lua();
    $lua->include($inc);
    $rs = $lua->call($func, $argc);
    return $rs;

}

/************************ 游戏逻辑 **************************/

/************************ 算 法 **************************/
//生成登录TOKEN
function create_login_token($uid)
{
    $str = $uid . time() . get_config('token_key');
    return strtolower(md5($str));
}

//生成订单号
function create_order_id($tid)
{
    return $tid . '_' . time();
}

//数组元素全部转化为string型
function array_value2string(&$arr)
{
    if (is_array($arr) && !empty($arr)) {
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                array_value2string($arr[$key]);
            } else {
                $arr[$key] = (string)$value;
            }
        }
    }
    return;
}

//权重算法
function weight($rate)
{
    $total = array_sum($rate);//所有概率
    $rand = rand(1, $total);
    $sum = 0;
    foreach ($rate as $key => $value) {
        $sum += $value;
        if ($rand <= $sum)
            return $key;
    }
}

//读取多层配置
function get_config($first, $key = false)
{

    //转大写
    $first = strtoupper($first);

    //读一层
    if (!$key)
        return C($first);

    //读两层
    if (!is_array($key))
        return C($first . '.' . $key);

    if (count($key) == 1)
        return C($first . '.' . $key[0]);

    //读多层
    $config = C($first);
    foreach ($key as $value)
        $config = $config[$value];

    return $config;

}

//截取字符串(可以有中文)
function over_cut($string, $start = 0, $length = 10, $charset = 'UTF-8')
{

    if (strlen($string) > $length + 2) {
        $str = mb_substr($string, $start, $length, $charset);
        return $str;
    }
    return $string;

}

//拼接IN句型
function sql_in_condition($arr)
{

    if (empty($arr))
        return false;

    $in = " in (";
    foreach ($arr as $value)
        $in .= "'{$value}',";
    $in = substr($in, 0, -1) . ")";
    return $in;

}

//数组排名
function arr_rank($arr, $k = 'rank')
{

    if (!empty($arr))
        return false;

    $i = 1;
    foreach ($arr as $key => $value) {
        $arr[$key][$k] = $i;
        $i++;
    }
    return $arr;

}

//二维数组按照某一个元素排序
function arr_field_sort($arr, $field, $type = 'asc')
{

    $field_value = $new_array = array();
    foreach ($arr as $k => $v) {
        $field_value[$k] = $v[$field];
    }
    if ($type == 'asc') {
        asort($field_value);
    } else {
        arsort($field_value);
    }
    foreach ($field_value as $k => $v) {
        $new_array[$k] = $arr[$k];
    }
    return $new_array;

}
/************************ 算 法 **************************/