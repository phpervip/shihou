<?php
/**
 * Created by PhpStorm.
 * User: 11093
 * Date: 2019/2/26
 * Time: 10:18
 */

namespace app\home\controller;
use app\admin\model\Tags as tagsModel;
use app\common\model\ArticleTags as articleTags;

use think\Db;
class Tags extends Common{
    public function index(){
        @$keyword=!empty(input('keyword')) ? input('keyword') : "";
        tagsModel::where('name',$keyword)->setInc('hits');
        $list = articleTags::with(['article'=>function($query){
            $query->order('createtime','desc');
        },'article.category'])->paginate(10)->each(function($item, $key){
//  'id' => int 6
//  'tag_id' => int 5
//  'article_id' => int 43
//  'article' =>
//    array (size=5)
//      'id' => int 43
//      'catid' => int 6
//      'title' => string '纯CSS实现页面的尖角、小三角、不同方向尖角的方法小结' (length=75)
//      'createtime' => int 1507425169
//      'category' =>
//        array (size=3)
//          'id' => int 6
//          'catdir' => string 'news' (length=4)
//          'catname' => string '相关知识 ' (length=13)
                $item['time'] = toDate($item['article']['createtime']);
                $item['url'] = url('home/'.$item['article']['category']['catdir'].'/info',array('id'=>$item['article']['id'],'catId'=>$item['article']['catid']));
                if(isset($item['article']['thumb'])){
                    $item['thumb'] = $item['article']['thumb']?$item['article']['thumb']:'/static/home/images/logo.png';
                }else{
                    $item['thumb'] = '/static/home/images/logo.png';
                }
                $item['title_style'] = isset($item['article']['title_style'])?isset($item['article']['title_style']):'';
                $item['title'] = isset($item['article']['title'])?$item['article']['title']:'';
                $item['hits']  = isset($item['article']['hits'])?$item['article']['hits']:0;
                return $item;
            });

        $page = $list->render();
        $list = $list->toArray();
        $this->assign('page',$page);
        $this->assign('lists',$list['data']);
        $this->assign('title','tag-'.$keyword);

        return $this->fetch();
    }
}