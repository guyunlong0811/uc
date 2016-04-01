<?php
namespace Home\Api;

use Think\Controller;

class ExchangeApi extends BaseApi
{

    //兑换
    public function index()
    {

        //获取兑换码数据
        $where['code'] = $_POST['code'];
        $count = M('Exchange')->where($where)->count();

        //兑换码不存在
        if ($count == '0') {
            C('G_ERROR', 'exchange_not_exist');
            return false;
        }

        //获取兑换码数据
        $where['status'] = 0;
        $exchangeInfo = M('Exchange')->where($where)->find();

        //兑换码已经被使用
        if (empty($exchangeInfo)) {
            C('G_ERROR', 'exchange_used');
            return false;
        }

        //获取兑换码类型数据
        $where = array();
        $where['id'] = $exchangeInfo['type'];
        $exchangeTypeInfo = M('ExchangeType')->where($where)->find();

        //激活码类型是否正确
        if ($exchangeTypeInfo['type'] != '1') {
            C('G_ERROR', 'exchange_not_exist');
            return false;
        }

        //兑换未开始
        if ($_POST['timestamp'] < $exchangeTypeInfo['starttime']) {
            C('G_ERROR', 'exchange_not_start');
            return false;
        }

        //兑换结束
        if ($exchangeTypeInfo['endtime'] != 0 && $_POST['timestamp'] > $exchangeTypeInfo['endtime']) {
            C('G_ERROR', 'exchange_over');
            return false;
        }

        //兑换码是否过期
        if ($exchangeTypeInfo['lifetime'] != '0') {
            $expire = $exchangeInfo['ctime'] + ($exchangeTypeInfo['lifetime'] * 86400);
            if ($_POST['timestamp'] > $expire) {
                C('G_ERROR', 'exchange_expire');
                return false;
            }
        }

        //游戏是否正确
        if ($_POST['gid'] != $exchangeTypeInfo['gid']) {
            C('G_ERROR', 'exchange_wrong_game');
            return false;
        }

        //服务器是否正确
        if ($exchangeTypeInfo['server'] != '0') {
            $serverList = explode('#', $exchangeTypeInfo['server']);
            if (!in_array($_POST['sid'], $serverList)) {
                C('G_ERROR', 'exchange_wrong_server');
                return false;
            }
        }

        //渠道是否正确
        if ($exchangeTypeInfo['channel'] != '0') {
            $channelList = explode('#', $exchangeTypeInfo['channel']);
            if (!in_array($_POST['channel_id'], $channelList)) {
                C('G_ERROR', 'exchange_wrong_channel');
                return false;
            }
        }

        //等级是否满足
        if ($exchangeTypeInfo['level'] > $_POST['level']) {
            C('G_ERROR', 'exchange_level_low');
            return false;
        }

        //查询玩家同服使用情况
        $where = array();
        $where['type'] = $exchangeInfo['type'];
        $where['sid'] = $_POST['sid'];
        $where['uid'] = $_POST['uid'];
        $where['status'] = 1;
        $useCount = M('Exchange')->where($where)->count();
        if ($useCount >= $exchangeTypeInfo['use_count']) {
            C('G_ERROR', 'exchange_type_use_max');
            return false;
        }

        //查询玩家不同服使用情况
        $where = array();
        $where['type'] = $exchangeInfo['type'];
        $where['uid'] = $_POST['uid'];
        $where['status'] = 1;
        $useCount = M('Exchange')->where($where)->count();
        if ($useCount >= $exchangeTypeInfo['use_count_diff']) {
            C('G_ERROR', 'exchange_type_use_max');
            return false;
        }

        //使用
        $where = array();
        $where['id'] = $exchangeInfo['id'];
        $update['uid'] = $_POST['uid'];
        $update['sid'] = $_POST['sid'];

        //如果修改失败或者修改0条都报错
        if (!D('Exchange')->UpdateData($update, $where)) {
            C('G_ERROR', 'exchange_use_fail');
            return false;
        }

        return $exchangeTypeInfo['goods'];
    }

    //激活
    public function activation()
    {

        //获取兑换码数据
        $where['code'] = $_POST['code'];
        $count = M('Exchange')->where($where)->count();

        //兑换码不存在
        if ($count == '0') {
            C('G_ERROR', 'exchange_not_exist');
            return false;
        }

        //获取兑换码数据
        $where['status'] = 0;
        $exchangeInfo = M('Exchange')->where($where)->find();

        //兑换码已经被使用
        if (empty($exchangeInfo)) {
            C('G_ERROR', 'exchange_used');
            return false;
        }

        //获取兑换码类型数据
        $where = array();
        $where['id'] = $exchangeInfo['type'];
        $exchangeTypeInfo = M('ExchangeType')->where($where)->find();

        //激活码类型是否正确
        if ($exchangeTypeInfo['type'] != '2') {
            C('G_ERROR', 'exchange_not_exist');
            return false;
        }

        //兑换未开始
        if ($_POST['timestamp'] < $exchangeTypeInfo['starttime']) {
            C('G_ERROR', 'activation_not_start');
            return false;
        }

        //兑换结束
        if ($exchangeTypeInfo['endtime'] != 0 && $_POST['timestamp'] > $exchangeTypeInfo['endtime']) {
            C('G_ERROR', 'activation_over');
            return false;
        }

        //兑换码是否过期
        if ($exchangeTypeInfo['lifetime'] != '0') {
            $expire = $exchangeInfo['ctime'] + ($exchangeTypeInfo['lifetime'] * 86400);
            if ($_POST['timestamp'] > $expire) {
                C('G_ERROR', 'activation_expire');
                return false;
            }
        }

        //游戏是否正确
        if ($_POST['gid'] != $exchangeTypeInfo['gid']) {
            C('G_ERROR', 'activation_wrong_game');
            return false;
        }

        //服务器是否正确
        if ($exchangeTypeInfo['server'] != '0') {
            $serverList = explode('#', $exchangeTypeInfo['server']);
            if (!in_array($_POST['sid'], $serverList)) {
                C('G_ERROR', 'activation_wrong_server');
                return false;
            }
        }

        //渠道是否正确
        if ($exchangeTypeInfo['channel'] != '0') {
            $channelList = explode('#', $exchangeTypeInfo['channel']);
            if (!in_array($_POST['channel_id'], $channelList)) {
                C('G_ERROR', 'activation_wrong_channel');
                return false;
            }
        }

        //查询玩家不同服使用情况
        $where = array();
        $where['type'] = $exchangeInfo['type'];
        $where['uid'] = $_POST['uid'];
        $where['status'] = 1;
        $useCount = M('Exchange')->where($where)->count();
        if ($useCount >= $exchangeTypeInfo['use_count_diff']) {
            C('G_ERROR', 'activation_type_use_max');
            return false;
        }

        //使用
        $where = array();
        $where['id'] = $exchangeInfo['id'];
        $update['uid'] = $_POST['uid'];
        $update['sid'] = $_POST['sid'];

        //如果修改失败或者修改0条都报错
        if (!D('Exchange')->UpdateData($update, $where)) {
            return false;
        }

        return true;

    }

}