<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;
use function Laravel\Prompts\select;

class ProductController extends Controller
{
    /**
     *This function returns All the products in the app
    **/
    public function getAllProducts(Request $request)
    {
        $lang = $request->lang;
        if($lang == null) {return response()->json([
            "message" => "Send The Language Pleas"
        ],400);}
        $products = Product::select('ar_name','en_name','price','quantity', 'shop_id')->get();
        foreach ($products as $key => $product) {
            $shop = Shop::where('id', $product->shop_id)->first();
            $name = [];
            $name["ar"] = $shop->ar_name;
            $name["en"] = $shop->en_name;
            $product->shop = $name[$lang];
            $products[$key] = $product;
        }
        return response()->json($products->select('ar_name','en_name','price', 'quantity','shop'), 200);
    }

    /**
     *This function return the informations of a certian product
    **/
    public function getCertainProduct(Request $request)
    {
        $lang = $request->lang;
        if($lang == null) {return response()->json([
            "message" => "Send The Language Pleas"
        ],400);}
        $product = Product::findOrFail($request->id);
        $shop = Shop::where('id', $product->shop_id)->first();
        $name = [];
        $name["ar"] = $shop->ar_name;
        $name["en"] = $shop->en_name;
        $product->shop = $name[$lang];
        return response()->json([
            'ar_name' => $product->ar_name,
            'en_name' =>$product->en_name,
            'price' => $product->price,
            'quantity' => $product->quantity,
            'shop' => $product->shop,
        ], 200);
    }

     /**
     *This function stores a new product to the app
    **/
    public function storeProduct(Request $request)
    {
        $lang = $request->lang;
        if($lang == null) {return response()->json([
            "message" => "Send The Language Pleas"
        ],400);}
        $validatedData = $request->validate(
            [
                'en_name' => 'required|string',
                'ar_name' => 'required|string',
                'price' => 'required',
                'quantity' => 'required',
                'shop_id' => 'required',
            ]
        );

        $product = Product::create($validatedData);
        $message = [];
        $message["ar"]="تم حفظ المنتج بنجاح";
        $message["en"]='Product stored successfully';
        return response()->json(['message' => $message[$lang]], 200);
    }

    /**
     *This function updates an existing product to the app
    **/
    public function updateProduct(Request $request, $id)
    {
        $lang = $request->lang;
        if($lang == null) {return response()->json([
            "message" => "Send The Language Pleas"
        ],400);}
        $validatedData = $request->validate([
            'en_name' => 'required|string',
            'ar_name' => 'required|string',
            'price' => 'sometimes',
            'quantity' => 'sometimes',
            'shop_id' => 'required',
        ]);
        $product = Product::findOrFail($id);
        $product->update($validatedData);
        $message = [];
        $message["ar"]="تم تعديل المنتج بنجاح";
        $message["en"]='Product updated successfully';
        return response()->json(['message' => $message[$lang]], 200);
    }

    /**
     *This function updates the quantity of a product , and if the quantity becomes 0 , it deletes the product from the app
    **/
    public function deleteProduct(Request $request)
    {
        $lang = $request->lang;
        if($lang == null) {return response()->json([
            "message" => "Send The Language Pleas"
        ],400);}
        $product = Product::findOrFail($request->id);
        $q = $request->quantity;

        if ($product->quantity == $q) {
            $product->delete();
        } elseif ($product->quantity < $q) {
            $message = [];
            $message["ar"] = 'هذه الكمية غير متاحة';
            $message["en"] = 'This quantity isn\'t available';
            $message = $message[$lang];
            return response()->json(['message' => $message ], 400);
        }
        else {
            $product->quantity = $product->quantity - $q;
            $product->save();
        }

        return response()->json(['message' => 'The quantity of the product has been updated'], 200);
    }


    /**
     * This function uses to get all the products that begin with the specified prefix text,
     * this text has to send with the request as a body parameter {text}
     */

    public function searchProduct(Request $request)
    {
        $lang = $request->lang;
        if($lang == null) {return response()->json([
            "message" => "Send The Language Pleas"
        ],400);}
        $products = Product::with('shop')
            ->where('ar_name', 'LIKE', $request->text . '%')
            ->orWhere('en_name','LIKE',$request->text . '%')
            ->select('ar_name', 'en_name', 'price', 'quantity', 'shop_id')
            ->get()
            ->map(function ($product) {
                return [
                    'ar_name' => $product->ar_name,
                    'en_name' => $product->en_name,
                    'price' => $product->price,
                    'quantity' => $product->quantity,
                    'shop' => $product->shop->name
                ];
            });

        return response()->json($products, 200);
    }

    public function addToFavorite(Request $request)
    {
        $lang = $request->lang;
        if($lang == null) {return response()->json([
            "message" => "Send The Language Pleas"
        ],400);}
        $product = Product::findOrFail($request->product_id);
        $product->users()->attach(
            $request->user_id
        );
        $message = [];
        $message["ar"] = 'تمت إضافة المنتج إلى المفضلة';
        $message["en"] = 'Product added to favorite successfully';
        return response()->json([
            "message" => $message[$lang]
        ]);
    }

    public function cancelFromFavorites(Request $request)
    {
        $lang = $request->lang;
        if($lang == null) {return response()->json([
            "message" => "Send The Language Pleas"
        ],400);}
        $product = Product::findOrFail($request->product_id);
        $product->users()->detach(
            $request->user_id
        );
        $message = [];
        $message["ar"] = 'تم حذف المنتج من المفضلة';
        $message["en"] = 'Product canceled from favorites successfully';
        return response()->json([
            "message" => $message[$lang]
        ]);
    }
    public function getImage($id)
    {
        $product = Product::findOrFail($id);
        return response()->json([
            "image" => $product->image_path
        ]);
    }
}
