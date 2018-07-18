<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    
    /**
     * $fillable
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'task', 'summary','status','category'
    ];
    
}
