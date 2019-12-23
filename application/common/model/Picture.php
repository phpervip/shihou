<?php
/**
 * Created by mac.
 * User: mac
 * Date: 2019/12/23
 * Time: 10:06 AM
 */

namespace app\common\model;


class Picture extends Base
{
    public function category(){
        return $this->hasOne('Category','id','catid')->field('id,catdir,catname');
    }
}