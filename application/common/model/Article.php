<?php
/**
 * Created by mac.
 * User: mac
 * Date: 2019/12/12
 * Time: 12:04 AM
 */

namespace app\common\model;

class Article extends Base
{
        public function category(){
            return $this->hasOne('Category','id','catid')->field('id,catdir,catname');
        }
}