<?php
namespace app\home\controller;
use app\common\model\Category as categoryModel;
use app\common\model\Field as fieldModel;
use app\common\model\Donation as donationModel;
use app\common\model\Message as messageModel;

class EmptyController extends Common{
    protected  $dao,$fields;
    public function initialize()
    {
        parent::initialize();
    }
    public function index(){
        $dbModel  = model(DBNAME);
        if(ISPAGE==1){
            $info = $dbModel::where('id',input('catId'))->find();
            $this->assign('info',$info);
            if($info['template']){
                $template = $info['template'];
            }else{

                $info['template'] = categoryModel::where('id',$info['id'])->value('template_show');
                $info['title_style'] = isset($info['title_style'])?$info['title_style']:'';
                if($info['template']){
                    $template = $info['template'];
                }else{
                    $template = DBNAME.'_show';
                }
            }
            return $this->fetch($template);
        }else{
            if(DBNAME=='picture'){
                $setup = fieldModel::where(['moduleid'=>3,'field'=>'group'])->value('setup');
                $setup=is_array($setup) ? $setup: string2array($setup);
                $options = explode("\n",$setup['options']);
                foreach($options as $r) {
                    $v = explode("|",$r);
                    $k = trim($v[1]);
                    $optionsarr[$k]['val'] = $v[0];
                    $optionsarr[$k]['key'] = $k;
                }
                $this->assign('options',$optionsarr);
            }
            $arrchildid = categoryModel::where(['id'=>input('catId')])->value('arrchildid');
            $map = ' ';
            if($arrchildid!=input('catId')){
                $map .= 'catid in ('.$arrchildid.')';
            }else{
                $map .= 'catid = '.input("catId");
            }
            $map .= ' and (status = 1 or (status = 0 and createtime <'.time().'))';
            if(DBNAME=='team'){
                $list = $dbModel::where($map)->order('sort asc,createtime desc')->select();
                foreach ($list as $k=>$v){
                    $item['title_style'] = isset($item['title_style'])?isset($item['title_style']):'';
                    if(isset($v['thumb'])){
                        $list[$k]['title_thumb'] = $v['thumb']?$v['thumb']:'/static/home/images/portfolio-thumb/p'.($k+1).'.jpg';
                    }else{
                        $list[$k]['title_thumb'] = '/static/home/images/portfolio-thumb/p'.($k+1).'.jpg';
                    }
                }
                $this->assign('list',$list);
            }else{
                // ' catid = 34 and (status = 1 or (status = 0 and createtime <1577064674))'
                $list=$dbModel::with('category')
                    ->where($map)
                    ->order('createtime desc')
                    ->paginate($this->pagesize)
                    ->each(function($item, $key){
                        $item['time'] = toDate($item['createtime']);
                        $item['url'] = url('home/'.$item['category']['catdir'].'/info',array('id'=>$item['id'],'catId'=>$item['catid']));
                        if(isset($item['thumb'])){
                            $item['thumb'] = $item['thumb']?$item['thumb']:'/static/home/images/logo.png';
                        }else{
                            $item['thumb'] = '/static/home/images/logo.png';
                        }
                        $item['title_style'] = isset($item['title_style'])?isset($item['title_style']):'';
                        return $item;
                    });
                $page = $list->render();
                $list = $list->toArray();
                $this->assign('lists',$list['data']);
                $this->assign('page',$page);
            }
            $cattemplate = categoryModel::where('id',input('catId'))->value('template_list');
            $template =$cattemplate ? $cattemplate : DBNAME.'_list';

            return $this->fetch($template);
        }
    }

    public function info(){
        $dbModel  = model(DBNAME);
        $dbModel::where('id',input('id'))->setInc('hits');
        $info = $dbModel::where('id',input('id'))->find();
        $info['pic'] = isset($info['pic'])?$info['pic']:config('view_replace_str.__HOME__')."/images/sample-images/blog-post".mt_rand(1,3).".jpg";
        $info['title_thumb'] = isset($info['thumb']) && $info['thumb'] ?$info['thumb']:config('view_replace_str.__HOME__').'/images/sample-images/blog-post'.mt_rand(1,3).'.jpg';
        $info['title_style'] = isset($info['title_style'])? $info['title_style']:'';
        if(DBNAME=='picture'){
            $pics = explode(':::',$info['pics']);
            foreach ($pics as $k=>$v){
                $info['pics'][$k] = explode('|',$v);
            }
        }
        if(DBNAME=='article'){
            $tags = explode(',',$info['tags']);
            $this->assign('tags',$tags);
        }
        $info['time'] = $info['updatetime']?toDate($info['updatetime']):toDate($info['createtime']);
        $this->assign('info',$info);

        if($info['template']){
			$template = $info['template'];
		}else{
			$cattemplate = categoryModel::where('id',$info['catid'])->value('template_show');
			if($cattemplate){
				$template = $cattemplate;
			}else{
				$template = DBNAME.'_show';
			}
		}
        return $this->fetch($template);
    }

    public function donationList(){
        $pageSize = 15;
        $page = input('post.curr');
        $list=donationModel::order('addtime desc')->paginate(array('list_rows'=>$pageSize,'page'=>$page))->toArray();
        return ['code'=>1,'msg'=>'获取成功!','data'=>$list['data'],'count'=>$list['total'],'rel'=>1];
    }
    public function message(){
        $uid = session('user.id');
        if($uid){
            $data = input('post.');
            $data['uid'] = $uid;
            $data['addtime'] = time();
            messageModel::insert($data);
            return $result = ['status'=>0,'code'=>1,'msg'=>'留言成功!','url'=>url('user/index/index')];
        }else{
            return $result = ['status'=>0,'code'=>0,'msg'=>'请先登录平台!'];
        }
    }
}