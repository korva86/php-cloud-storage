<?php

namespace app\models;

use RedBeanPHP\R;
use wfm\Model;
//use Illuminate\Database\Eloquent\Model;
//use wfm\Db;

class User extends Model
{
    protected $table = 'user';
//
//    public array $attributes = [];
//    public array $errors = [];
//    public array $rules = [];
//    public array $labels = [];
//
//    public function __construct()
//    {
//        parent::__construct();
//        Db::getInstance();
//    }

    public function get_users(): array
    {
        return R::findAll('user');
    }
}