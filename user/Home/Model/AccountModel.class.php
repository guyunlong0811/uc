<?php
namespace Home\Model;

use Think\Model;

class AccountModel extends BaseModel
{

    protected $_validate = array(//自动验证
        array('username', '', 'username_existed', 0, 'unique', 3), //验证name字段是否唯一
        array('username', '/^[a-zA-Z]{1}\w{5,15}$/', 'username_format', 0, 'regex', 3), //验证name字段是否合法
        array('phone', '', 'phone_existed', 0, 'unique', 3), //验证手机号码是否唯一
        array('phone', '', '/^1\d{10}$/', 'phone_format', 0, 'regex', 3), //手机号是否合法
//        array('password','/^[\\~!@#$%^&*()-_=+|{}\[\],.?\/:;\'\"\d\w]{6,16}$/','password_format_error',0,'regex',3),
    );

    protected $_auto = array(
        array('ctime', 'time', 1, 'function'), //新增的时候把ctime字段设置为当前时间
        array('last_login_time', 'time', 1, 'function'), //最近登录时间设为空
//        array('password','md5',3,'function') , //对password字段在新增和编辑的时候使md5函数处理
        array('password', null, 3, 'ignore'), //对password字段在编辑的时候为空的话自动过滤
    );

    //获取快速登录用户
    public function getFastUserInfo($udid, $channel_id, $channel_uid)
    {
        $where['username'] = '';
//        $where['channel_id'] = $channel_id;
        if ($channel_id < 10000) {
            $where['udid'] = $udid;
        } else {
            $where['channel_uid'] = $channel_uid;
        }
        $userInfo = $this->where($where)->order('`uid` asc')->find();
        //分析结果
        if (empty($userInfo)) {//找不到用户名
            return 0;//提示未注册
        } else {
            return $userInfo['uid'];//修改后UID
        }
    }

    //登录操作
    public function login($uid, $udid, $gid, $sid)
    {
        $now = time();
        $list = D('Banned')->getInfo($uid, $gid, $sid);
        if (in_array(1, $list)) {
            C('G_ERROR', 'user_banned');//提示未注册
            return false;
        }
        //计算登陆token
        $rs['uid'] = $uid;
        $rs['token'] = create_login_token($uid);//生成登陆密钥
        $rs['silence'] = in_array(2, $list) ? 1 : 0;//是否禁言

        //查看服务器是否需要激活码
        $where['gid'] = $gid;
        $where['sid'] = $sid;
        $activation = M('Logic')->where($where)->getField('activation');
        if ($activation == '1') {

            //查看游戏对应激活码ID
            $where = "`gid`='{$gid}' && `type`='2' && `status`='1' && `starttime` < '{$now}' && (`endtime`='0' || `endtime` >= '{$now}') && (`server`='0' || `server` like '%{$sid}%' || `server` like '{$sid}%' || `server` like '%{$sid}')";
            $idList = M('ExchangeType')->where($where)->getField('id', true);
            //查询帐号是否激活过
            $where = array();
            $where['uid'] = $uid;
            $where['type'] = array('in', $idList);
            $where['status'] = 1;
            $count = M('Exchange')->where($where)->count();
            $count = $count ? $count : 0;

        } else {
            $count = 1;
        }

        $rs['activation'] = $count;

        //修改登陆次数和登录时间&添加登录记录
        $this->logLogin($uid, $udid, $gid, $sid);
        return $rs;
    }

    private function logLogin($uid, $udid, $gid, $sid)
    {
        //最后登录时间
        $where['uid'] = $uid;
        $data['last_login_time'] = time();//修改最近登录时间
        $this->UpdateData($data, $where);
        //插入登录记录
        $add['uid'] = $uid;
        $add['udid'] = $udid;
        $add['gid'] = $gid;
        $add['sid'] = $sid;
        D('Login')->CreateData($add);
        return true;
    }

    //登录验证逻辑
    public function checkLogin($username, $password, $timestamp)
    {

        //获取用户信息
        $where['username'] = $username;
        $userInfo = $this->getRowCondition($where);

        //分析结果
        if (!$userInfo) {//找不到用户名
            C('G_ERROR', 'username_not_exist');//提示未注册
            return false;
        }
        if (strtolower(md5($userInfo['password'] . $timestamp . get_config('uc_verify', 'password'))) != strtolower($password)) {//密码不正确
            C('G_ERROR', 'password_wrong');//提示密码错误
            return false;
        }

        return $userInfo['uid'];//返回用户uid

    }

    //查询用户名是否存在
    public function usernameCheck($username)
    {
        $where['username'] = $username;
        return $this->where($where)->count();
    }

    //修改密码
    public function changePwd($uid, $password)
    {
        $where['uid'] = $uid;
        $data['password'] = $password;
        if (false === $this->UpdateData($data, $where)) {
            return false;
        }
        return true;
    }

    //修改密码检查
    public function checkPwd($uid, $password, $newPassword, $timestamp)
    {
        //获取用户信息
        $where['uid'] = $uid;
        $userInfo = $this->getRowCondition($where);

        //分析结果
        if (!$userInfo) {//找不到用户名
            C('G_ERROR', 'uid_not_exist');//提示用户不存在
            return false;
        }
        if (strtolower(md5($userInfo['password'] . $timestamp . get_config('uc_verify', 'password'))) != strtolower($password)) {//密码不正确
            C('G_ERROR', 'password_wrong');//提示密码错误
            return false;
        }
        if ($password == strtolower($newPassword . $timestamp . get_config('uc_verify', 'password'))) {
            C('G_ERROR', 'password_not_changed');//提示新密码与旧密码一致
            return false;
        }
        return true;

    }

    //验证绑定信息
    public function binding($username, $password, $udid, $timestamp)
    {

        //获取用户信息
        $where1['username'] = $username;
        $userInfo1 = $this->getRowCondition($where1);

        //分析结果
        if (!$userInfo1) {//找不到用户名
            C('G_ERROR', 'username_not_exist');//提示未注册
            return false;
        }

        if (strtolower(md5($userInfo1['password'] . $timestamp . get_config('uc_verify', 'password'))) != strtolower($password)) {//密码不正确
            C('G_ERROR', 'password_wrong');//提示密码错误
            return false;
        }

        $where2['udid'] = $udid;
        $where2['channel_id'] = 0;
        $userInfo2 = $this->getRowCondition($where2);
        if (!$userInfo2) {//找不到用户名
            C('G_ERROR', 'device_unknown');//错误设备
            return false;
        }

        $rs['after'] = $userInfo1['uid'];//修改后UID
        $rs['before'] = $userInfo2['uid'];//修改前UID
        return $rs;

    }

    //检查邮箱是否已经绑定
    public function checkEmail($uid)
    {
        $where['uid'] = $uid;
        $email = $this->where($where)->getField('email');
        if ($email != '') {//找不到用户名
            C('G_ERROR', 'email_binding_already');//提示已绑定手机
            return false;
        }
        return true;
    }

    //检查手机号是否已经绑定
    public function checkPhone($uid)
    {
        $where['uid'] = $uid;
        $phone = $this->where($where)->getField('phone');
        if ($phone != '') {//找不到用户名
            C('G_ERROR', 'phone_binding_already');//提示已绑定手机
            return false;
        }
        return true;
    }

    //检查手机号是否已经绑定
    public function checkIdent($uid)
    {
        $where['uid'] = $uid;
        $ident = $this->where($where)->getField('ident');
        if ($ident) {//找不到用户名
            C('G_ERROR', 'ident_binding_already');//提示已绑定手机
            return false;
        }
        return true;
    }

}