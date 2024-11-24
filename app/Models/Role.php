<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    
    //Done Relationships ----------------------------------------------------------------
    
    protected $guarded = ['id' , 'timestamp'];

    public function users(){
        return $this->hasMany( User::class);
    }
}
