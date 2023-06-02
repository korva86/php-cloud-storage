<?php

namespace app\models;

//use Illuminate\Database\Eloquent\Model;
use wfm\Model;

class File extends Model
{
    protected $table = 'files';

    public function folder()
    {
        return $this->belongsTo(Directory::class, 'directory_id', 'id');
    }

    public function user()
    {
        return $this->hasMany(User::class, 'name');
    }
}