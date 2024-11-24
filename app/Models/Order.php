<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{

    //DONE Relationships ----------------------------------------------------------------

    public function products(){
        return $this->belongsToMany(Product::class,'order_product');
    }

    public function status(){
        return $this->belongsTo(Status::class);
    }

    
}
