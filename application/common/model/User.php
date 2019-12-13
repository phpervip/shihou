<?php
/**
 * Created by mac.
 * User: mac
 * Date: 2019/12/13
 * Time: 10:13 PM
 */

namespace app\common\model;


class User extends Base
{
    protected $table = 'users';
    public function userLevel(){
        return $this->hasOne('UserLevel','level_id','level')->field('level_id,level_name');
    }
}