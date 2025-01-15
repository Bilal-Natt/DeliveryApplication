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

    public function getAllShops(Request $request)
    {
        $shops = Shop::all();
        return response()->json($shops);
    }
    public function getShopProducts(Request $request,$id){
        $shopProducts = Shop::findOrFail($id);
        return response()->json($shopProducts->products , 200);
    }

    /**
     * This function uses to get all the products that begin with the specified prefix text in specified shop,
     * this text and shop have to send with the request as a body parameter {text,shop_id}
     * */
    public function searchShopProducts(Request $request){
        $lang = $request->lang;
        if($lang == null) {return response()->json([
            "message" => "Send The Language Pleas"
        ],400);}
        $productsAR = Product::where('ar_name', 'LIKE', $request->text . '%')->or;
        $productsEN = Product::where('en_name', 'LIKE', $request->text . '%');
        $products = null;
        if($lang=="ar") $products = $productsAR;
        else $products = $productsEN;
            $products = $products->where('shop_id',$request->shop_id)->get();
            return response()->json($products , 200);
    }

    /**
     * This function uses to get all the shops that begin with the specified prefix text,
     * this text has to send with the request as a body parameter {text}
     * */
    public function searchShop(Request $request)
    {
        $lang = $request->lang;
        if($lang == null) {return response()->json([
            "message" => "Send The Language Pleas"
        ],400);}
        if($request->lang == "ar")
            $shops = Shop::where('ar_name', 'LIKE', $request->text . '%')->get();
        else
            $shops = Shop::where('en_name', 'LIKE', $request->text . '%')->get();
        return response()->json($shops , 200);

    }
    public function getImage($id)
    {
        $shop = Shop::findOrFail($id);
        return response()->json([
            "image" => $shop->image_path
        ]);
    }

}
