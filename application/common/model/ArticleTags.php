<?php
/**
 * Created by mac.
 * User: mac
 * Date: 2019/12/23
 * Time: 10:55 AM
 */

namespace app\common\model;


class ArticleTags extends Base
{
    public function article(){
        return $this->hasOne('Article','id','article_id')->field('id,catid,title,createtime,thumb,hits');
    }
}