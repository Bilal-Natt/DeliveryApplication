<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;

class ShopController extends Controller
{
   
    public function getShopProducts($id){
        $shopProducts = Shop::findOrFail($id);
        return response()->json($shopProducts->products , 200);
    }

    public function getShop($id){
        $shop = Shop::findOrFail($id);
        return $shop;
    }

}
