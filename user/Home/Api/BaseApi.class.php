<?php
namespace Home\Api;

use Think\Controller;

class BaseApi extends Controller
{

    //开始事务
    protected function transBegin()
    {
        C('G_TRANS', true);//事务标示
        C('G_TRANS_FLAG', false);//事务标示
        change_db_config('master');
        M()->startTrans();
    }

    //结束事务
    protected function transEnd()
    {

        if (!C('G_TRANS_FLAG')) {
            M()->rollback();
            if (C('G_ERROR') != 'db_error')//如果不是数据库有问题
                C('G_TRANS', false);//结束事务
        } else {
            M()->commit();
            C('G_TRANS', false);//结束事务
        }
        change_db_config('all');
        return C('G_TRANS_FLAG');

    }

    //分页
    protected function page($model = false, $style = 'sql', $where = '1=1', $num = 10)
    {//分页

        if ($style == 'sql') {
            $count = $model->where($where)->count();
            $count = !empty($count) ? $count : '0';
        } else if ($style == 'array') {
            $count = count($model);
        } else {
            return false;
        }
//		if($count == 0)return false;
        //dump($count);
        $page = new \Think\Page($count, $num);
        $page->setConfig('first', '<<');
        $page->setConfig('prev', '<');
        $page->setConfig('next', '>');
        $page->setConfig('last', '>>');
        $page->setConfig('theme', "%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% total:%TOTAL_ROW%");//rows %NOW_PAGE%/%TOTAL_PAGE%pages
        $this->show = $page->show('bootstrap');
//        dump($this->show);
//        exit;
        // dump($page);
        return $page;

    }

}