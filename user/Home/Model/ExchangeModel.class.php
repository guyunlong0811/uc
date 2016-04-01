<?php
namespace Home\Model;

use Think\Model;

class ExchangeModel extends BaseModel
{
    protected $_auto = array(
        array('utime', 'time', 3, 'function'),
        array('status', 1, 3),
    );
}