<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    
    /**
     * This function returns all of the products that belongs to a certain shop by its id 
     * */
    public function getShopProducts($id){
        $shopProducts = Shop::findOrFail($id);
        return response()->json($shopProducts->products , 200);
    }

    /**
     * This function returns the shop by its id 
     * */
    public function getShop($id){
        $shop = Shop::findOrFail($id);
        return $shop;
    }

    /**
     * This function uses to get all the products that begin with the specified prefix text in specified shop,
     * this text and shop have to send with the request as a body parameter {text,shop_id}
     * */
    public function searchShopProducts(Request $request){
            $products = Product::where('name', 'LIKE', $request->text . '%')
                ->where('shop_id',$request->shop_id)->get();
            return response()->json($products , 200);
    }

    /**
     * This function uses to get all the shops that begin with the specified prefix text,
     * this text has to send with the request as a body parameter {text}
     * */
    public function searchShop(Request $request)
    {
        $shops = Shop::where('name', 'LIKE', $request->text . '%')->get();
        return response()->json($shops , 200);

    }

}
