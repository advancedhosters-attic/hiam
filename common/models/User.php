<?php

namespace common\models;

use Yii;

/**
 * User model
 */
class User extends \hiam\common\models\User
{

    public static function findByUsername ($username,$password=null) {
        $query = static::find()
            ->select    (['c.obj_id AS id','c.obj_id','c.login','c.type_id','c.state_id','c.reseller_id AS seller_id',
                        'r.login AS seller','y.name AS type','z.name AS state','c.login AS username',
                        'coalesce(c.email,k.email) AS email'])
            ->from      ('client        c')
            ->innerJoin ('client        r',"r.obj_id=c.reseller_id")
            ->innerJoin ('type          y',"y.obj_id=c.type_id")
            ->innerJoin ('type          z',"z.obj_id=c.state_id AND z.name IN ('active')")
            ->leftJoin  ('contact       k',"k.obj_id=c.obj_id")
            ->where     (['or','c.login=:username','c.email=:username','c.obj_id=:id'])
            ->addParams ([':username'=>$username,':id'=>(int)$username ?: null])
        ;
        if ($password) $query
            ->leftJoin  ('value         t',"t.obj_id=c.obj_id AND t.prop_id=prop_id('client,access:tmp_pwd')")
            ->andWhere  ("check_password(:password,c.password) OR check_password(:password,t.value)")
            ->addParams ([':password'=>$password])
        ;
        $user = $query->one();
        return $user;
    }

}
