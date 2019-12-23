<?php
namespace app\home\controller;
use think\Db;
use clt\Leftnav;
use think\Controller;
use app\common\model\User as userModel;
use app\common\model\Category as categoryModel;
use app\common\model\Field as fieldModel;
use app\common\model\Link as linkModel;
use app\common\model\Plugin as pluginModel;

class Common extends Controller
{
    protected $pagesize;
    public function initialize()
    {
        $system = cache('System');
        $this->assign('config',$system);
        if($system['mobile']=='open'){
            if(isMobile()){
                $this->redirect('mobile/index/index');
            }
        }
        $userInfo='';
        if(session('user')){
            //用户信息
            $userInfo =userModel::with('userLevel')
                ->where('id',session('user.id'))
                ->find();
        }
        $this->assign('userInfo',$userInfo);

        $action = request()->action();
        $controller = request()->controller();
        $this->assign('action',($action));
        $this->assign('controller',strtolower($controller));
        define('MODULE_NAME',strtolower($controller));
        define('ACTION_NAME',strtolower($action));


        //导航
        $thisCat = categoryModel::where('id',input('catId'))->find();

        $this->assign('title',$thisCat['title']);
        $this->assign('keywords',$thisCat['keywords']);
        $this->assign('description',$thisCat['description']);

        //判断是否为单页面模型
        $hasCat = fieldModel::where(['moduleid'=>$thisCat['moduleid'],'type'=>'catid'])->find();
        define('DBNAME',strtolower($thisCat['module']));
        if($hasCat){
            define('ISPAGE',0);
        }else{
            define('ISPAGE',1);
        }
        $this->pagesize = $thisCat['pagesize']>0 ? $thisCat['pagesize'] : '';
        if($thisCat['pid'] ==0){
            $this->assign('pid',input('catId'));
            $this->assign('ptitle',$thisCat['title']);
        }else{
            $this->assign('ptitle',categoryModel::where('id',$thisCat['pid'])->value('title'));
            $this->assign('pid',$thisCat['pid']);
        }

        // 获取缓存数据
        $cate = cache('cate');
        if(!$cate){
            $column_one = categoryModel::where([['pid','=',0],['ismenu','=',1]])->order('sort')->select();
            $column_two = categoryModel::where('ismenu',1)->order('sort')->select();
            $tree = new Leftnav ();
            $cate = $tree->index_top($column_one,$column_two);
            cache('cate', $cate, 3600);
        }
        $this->assign('category',$cate);
        //友情链接
        $linkList = cache('linkList');
        if(!$linkList){
            $linkList = linkModel::where('open',1)->order('sort asc')->select();
            cache('linkList', $linkList, 3600);
        }

        $this->assign('linkList', $linkList);
        //畅言
        $plugin = pluginModel::where(['code'=>'changyan'])->find();
        $this->changyan = unserialize($plugin['config_value']);
        $this->assign('changyan', $this->changyan);
        $this->assign('time', time());

    }
    //空操作
    public function _empty(){
        return $this->error('空操作，返回上次访问页面中...');
    }
}
