<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    //DONE Relationships ----------------------------------------------------------------
    
    public function shop(){
        return $this->belongsTo(Shop::class);
    }

    public function orders(){
        return $this->belongsToMany(Order::class , 'order_product');
    }
}
