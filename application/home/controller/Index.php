<?php
namespace app\home\controller;
use think\Db;
use clt\Lunar;
use think\facade\Env;
use app\common\model\Article as articleModel;
use app\common\model\Feast as feastModel;
use app\common\model\Ad as adModel;
use app\common\model\Tags as tagsModel;
use app\common\model\FeastElement as feastElementModel;
use app\common\model\Debris as debrisModel;
use app\common\model\Link as linkModel;
class Index extends Common{
    public function initialize(){
        parent::initialize();
    }
    public function index(){
        // 最新动态取4条，相关知识取5条
        $list_a = $this->getArticleList(1,5);
        $list_b = $this->getArticleList(6,4);
        $this->assign('list_a', $list_a);
        $this->assign('list_b', $list_b);
        // 节日插件
        if(!isMobile()){
            $res = $this->getFeastPlugin();
            $this->assign('style', $res['style']);
            $this->assign('js', $res['js']);
        }
        $this->assign('demo_time',$this->request->time());
        // 广告
        $adList = cache('adList');
        if(!$adList){
            $adList = adModel::where([['open','=',1],['as_id','eq',1]])->order('sort asc')->select();
            cache('adList', $adList, 3600);
        }
        $this->assign('adList', $adList);
        // 热门标签
        $tagsList = tagsModel::order('hits','desc')->limit(8)->select();
        $this->assign('tagsList', $tagsList);
        // 中部碎片
        $debrisList = debrisModel::select();
        $this->assign('debrisList', $debrisList);
        // 友情链接
        $linkList  = linkModel::select();
        $this->assign('linkList',$linkList);
        return $this->fetch();
    }
    public function download($id=''){
        $map['id'] = $id;
        $files = Db::name('download')->where($map)->find();
        return download(Env::get('root_path').'public'.$files['files'], $files['title']);
    }

    public function getArticleList($catid=1,$limit=4){
        $order = input('order','createtime');
        $list = articleModel::with('category')
            ->where('catid',$catid)
            ->order($order.' desc')
            ->limit($limit)
            ->select();
        foreach ($list as $k=>$v){
            $list[$k]['time'] = toDate($v['createtime']);
            $list[$k]['date'] = date('Y-m-d',$v['createtime']);
            $list[$k]['url']  = url('home/'.$v['category']['catdir'].'/info',array('id'=>$v['id'],'catId'=>$v['catid']));
        }
        return $list;
    }

    public function getFeastPlugin(){
        $m= $thisDate = date("m");
        $d= $thisDate = date("d");
        $y= $thisDate = date("Y");
        $Lunar=new Lunar();
        //获取农历日期
        $nonliData = $Lunar->convertSolarToLunar($y,$m,$d);
        $nonliData = $nonliData[1].'-'.$nonliData[2];
        $feastId = feastModel::where(array('feast_date'=>$nonliData,'type'=>2))->value('id');
        if($feastId){
            $element = feastElementModel::where('pid',$feastId)->select();
            $style = '<style>';
            $js = '';
            foreach ($element as $k=>$v){
                $style .= $v['css'];
                $js .= $v['js'];
            }
            $style .= '</style>';
        }else{
            $style='';
            $js='';
            $feastId = feastModel::where(array('feast_date'=>$m.'-'.$d,'type'=>1))->value('id');
            if($feastId){
                $element = feastElementModel::where('pid',$feastId)->select();
                $style = '<style>';
                $js = '';
                foreach ($element as $k=>$v){
                    $style .= $v['css'];
                    $js .= $v['js'];
                }
                $style .= '</style>';
            }
        }
        return ['style'=>$style,'js'=>$js];
    }
}