<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{

    //DONE Relationships ----------------------------------------------------------------

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function products(){
        return $this->hasMany(Product::class);
    }
}
