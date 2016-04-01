<?php
namespace Home\Model;

use Think\Model;

class BannedModel extends BaseModel
{

    //登录操作
    public function getInfo($uid, $gid, $sid)
    {
        $field = array('type',);
        $where1['uid'] = $uid;
        $now = time();
        $where1['starttime'] = array('elt', $now);
        $where1['endtime'] = array('egt', $now);
        $where2 = "(`gid` = '0' || `gid` = '{$gid}') && (`sid` = '0' || `sid` = '{$sid}')";
        $select = $this->field($field)->where($where1)->where($where2)->select();
        if (empty($select)) {
            return array();
        }
        $arr = array();
        foreach ($select as $value) {
            if (!in_array($value['type'], $arr)) {
                $arr[] = $value['type'];
            }
        }
        return $arr;
    }

}