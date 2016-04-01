<?php
namespace Home\Api;

use Think\Controller;

class UserApi extends BaseApi
{

    //获取服务器列表
    public function getServerList()
    {
        return D('Server')->getAll($_POST['gid']);
    }

    //用户快速登录
    public function fast()
    {
        //查询用户是否已经注册过弱账号
        $uid = D('Account')->getFastUserInfo($_POST['udid'], $_POST['channel_id'], $_POST['channel_uid']);
        if ($uid === false) {
            return false;
        }
        //尚未注册
        if ($uid === 0) {
            $data['mac'] = $_POST['mac'];
            $data['udid'] = $_POST['udid'];
            $data['channel_id'] = $_POST['channel_id'];
            if ($_POST['channel_id'] < 10000) {
                $data['channel_uid'] = '';
            } else {
                $data['channel_uid'] = $_POST['channel_uid'];
            }
            $data['cip'] = $_POST['ip'];
            if (!$uid = D('Account')->CreateData($data)) {
                return false;
            }//快速注册
        }
        //自动登陆
        return D('Account')->login($uid, $_POST['udid'], $_POST['gid'], $_POST['sid']);//执行登录操作
    }

    //用户正常登陆
    public function login()
    {
        //验证
        if (!$uid = D('Account')->checkLogin($_POST['username'], $_POST['password'], $_POST['pts'])) {
            return false;
        }//验证逻辑
        //执行
        return D('Account')->login($uid, $_POST['udid'], $_POST['gid'], $_POST['sid']);//执行登录操作
    }

    //用户正常注册
    public function register()
    {
        //查询当前udid是否存在弱账号
        $uid = D('Account')->getFastUserInfo($_POST['udid'], $_POST['channel_id'], $_POST['channel_uid']);//验证逻辑
        if (!$uid) {
            //如果不存在则直接注册
            $reg['udid'] = $_POST['udid'];
            $reg['username'] = $_POST['username'];
            $reg['password'] = $_POST['password'];
            $reg['channel_id'] = $_POST['channel_id'];
            $reg['channel_uid'] = $_POST['channel_uid'];
            $reg['cip'] = $_POST['ip'];
            return D('Account')->CreateData($reg);//执行注册操作
        } else {
            $where['uid'] = $uid;
            $save['username'] = $_POST['username'];
            $save['password'] = $_POST['password'];
            if (!D('Account')->UpdateData($save, $where)) {
                return false;
            }//执行注册操作
            return $uid;
        }
    }

    //注册时查询是否有相同用户名
    public function usernameCheck()
    {
        return D('Account')->usernameCheck($_POST['username']);
    }

    //绑定已有账号
    public function binding()
    {
        return D('Account')->binding($_POST['username'], $_POST['password'], $_POST['udid'], $_POST['timestamp']);
    }

    //绑定新建账号(补全弱账号)
    public function complete()
    {
        //验证
        if (!$uid = D('Account')->getFastUserInfo($_POST['udid'], $_POST['channel_id'], $_POST['channel_uid'])) {//验证逻辑
            C('G_ERROR', 'fast_not_exist');
            return false;
        }
        //执行
        $where['uid'] = $uid;
        $save['username'] = $_POST['username'];
        $save['password'] = $_POST['password'];
        if (!D('Account')->UpdateData($save, $where)) {
            return false;
        }//执行注册操作
        return $uid;
    }

    //修改密码
    public function changePwd()
    {
        //验证
        if (!D('Account')->checkPwd($_POST['uid'], $_POST['password'], $_POST['newPassword'], $_POST['pts'])) {
            return false;
        }
        //执行
        return D('Account')->changePwd($_POST['uid'], $_POST['newPassword']);//修改密码
    }

    //绑定手机号
    public function email()
    {
        //查询当前账号是否已经绑定邮箱
        if (!D('Account')->checkEmail($_POST['uid'])) {
            return false;
        }//验证逻辑
        //修改数据
        $where['uid'] = $_POST['uid'];
        $data['email'] = $_POST['email'];
        if (false === $row = D('Account')->UpdateData($data, $where)) {
            return false;
        }
        return $row;
    }

    //绑定手机号
    public function phone()
    {
        //查询当前账号是否已经绑定手机
        if (!D('Account')->checkPhone($_POST['uid'])) {
            return false;
        }//验证逻辑
        //修改数据
        $where['uid'] = $_POST['uid'];
        $data['phone'] = $_POST['phone'];
        if (false === $row = D('Account')->UpdateData($data, $where)) {
            return false;
        }
        return $row;
    }

    //绑定防沉迷
    public function ident()
    {
        //查询当前账号是否已经绑定身份信息
        if (!D('Account')->checkIdent($_POST['uid'])) {
            return false;
        }//验证逻辑
        //修改数据
        $where['uid'] = $_POST['uid'];
        $data['realname'] = $_POST['realname'];
        $data['ident'] = $_POST['ident'];
        if (false === D('Account')->UpdateData($data, $where)) {
            return false;
        }
        return true;
    }

    //获取平台用户ID
    public function getChannelUid()
    {
        $where['uid'] = $_POST['uid'];
        return D('Account')->where($where)->getField('channel_uid');
    }

}