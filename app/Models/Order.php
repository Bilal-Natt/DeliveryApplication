<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{

    //DONE Relationships ----------------------------------------------------------------

    protected $guarded = ['id' , 'timestamp'];
    public function products(){
        return $this->belongsToMany(Product::class,'order_product')->withPivot('quantity','price');
    }

    public function status(){
        return $this->belongsTo(Status::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }


}
