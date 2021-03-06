<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/18
 * Time: 20:17
 */

namespace app\admin\controller;
use think\Controller;
use think\Session;

/**
 * Class 后台基础类
 * @package app\admin\controller
 */
class BaseAdmin extends Controller
{

    public $begin;
    public $end;
    public $page_size = 0;

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub

        Session::start();
        // history.back返回后输入框值丢失问题 参考文章  http://blog.csdn.net/qinchaoguang123456/article/details/29852881
        header("Cache-control: private");

        $action = request()->action();

        if (!in_array($action, array('login', 'vertify'))) {
            if (session('ADMIN.admin_id') > 0) {
                $this->check_privilege();//检查管理员菜单操作权限
                $this->public_assign();
            }else {
                //($action == 'index') && $this->redirect( url('admin/admin/login'));
                //$this->error('请先登录', url('admin/admin/login'), null, 1);
                $this->redirect( url('admin/admin/login'));
            }
        }
    }

    public function check_privilege(){
        $ctl = request()->controller();
        $act = request()->action();
        $act_list = session('ADMIN.act_list');
        //无需验证的操作
        $unneeded_check = array('login','logout','vertifyHandle','vertify','imageUp','upload','videoUp','delupload','login_task');
        if($ctl == 'Index' || $act_list == 'all'){
            //后台首页控制器无需验证,超级管理员无需验证
            return true;
        }elseif(request()->isAjax() || strpos($act,'ajax')!== false || in_array($act,$unneeded_check)){
            //所有ajax请求不需要验证权限
            return true;
        }else{
            $right = db('system_menu')
                ->field('privilege')
                ->where("menu_id", "in", $act_list)
                ->cache(true);
            $role_right = '';
            foreach ($right as $val){
                $role_right .= $val.',';
            }
            $role_right = explode(',', $role_right);
            //检查是否拥有此操作权限
            if(!in_array($ctl.'@'.$act, $role_right)){
                $this->error('您没有操作权限['.($ctl.'@'.$act).'],请联系超级管理员分配权限',url('admin/index/welcome'));
            }
        }
        return false;
    }

    /**
     * 保存公告变量到 smarty中 比如 导航
     */
    public function public_assign()
    {
        $shop_config = array();
        $nz_config = db('config')
            ->cache(true)
            ->select();
        foreach($nz_config as $k => $v)
        {
            $shop_config[$v['inc_type'].'_'.$v['config_key']] = $v['config_value'];
        }
        if(input('start_time')){
            $begin = $begin = input('start_time');
            $end = input('end_time');
        }else{
            $begin = date('Y-m-d', strtotime("-3 month"));//30天前
            $end = date('Y-m-d', strtotime('+1 days'));
        }
        $this->assign('start_time',$begin);
        $this->assign('end_time',$end);
        $this->begin = strtotime($begin);
        $this->end = strtotime($end)+86399;
        $this->page_size = config('paginate.list_rows');
        $this->assign('shop_config', $shop_config);
        $this->assign('admin_info',session('ADMIN'));
        $this->assign('menu', getMenuArr());
    }

    public function ajaxReturn($data,$type = 'json'){
        exit(json_encode($data));
    }


    public function getVerify(){
        get_verify_code();
    }

}