<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    //DONE Relationships ----------------------------------------------------------------

    public function orders(){
        return $this->hasMany(Order::class);
    }
}
