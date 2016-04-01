<?php
namespace Home\Model;

use Think\Model;

class ServerModel extends BaseModel
{

    //登录操作
    public function getAll($gid)
    {
        $sql = "select `server`.`channel_id`,`logic`.`sid`,`server`.`name`,`server`.`type`,`logic`.`activation`,`logic`.`db_m_host`,`logic`.`db_m_user`,`logic`.`db_m_pwd`,`logic`.`db_m_port`,`logic`.`db_s_host`,`logic`.`db_s_user`,`logic`.`db_s_pwd`,`logic`.`db_s_port`,`logic`.`dbname`,`logic`.`log_db_host`,`logic`.`log_db_user`,`logic`.`log_db_pwd`,`logic`.`log_db_port`,`logic`.`log_dbname`,`logic`.`redis_host`,`logic`.`redis_port`,`logic`.`redis_game`,`logic`.`redis_social`,`logic`.`redis_fight`,`logic`.`platform_url`,`logic`.`platform_sid`,`logic`.`script_server_id`,`channel`.`code`,`channel`.`callback` from `logic`,`server`,`channel`,`game` where `server`.`logic_id`=`logic`.`id` && `server`.`channel_id`=`channel`.`channel_id` && `logic`.`gid`=`game`.`gid` && `logic`.`gid`=`channel`.`gid` && `logic`.`gid`='{$gid}' && `game`.`status`<>'0' && `logic`.`status`<>'0' && `channel`.`status`<>'0' && `server`.`status`<>'0'";
        $select = $this->query($sql);
        return $select;
    }

}