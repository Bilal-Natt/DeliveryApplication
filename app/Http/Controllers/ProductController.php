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
    public function getAllProducts()
    {
        $products = Product::select('name', 'price', 'quantity', 'shop_id')->get();
        foreach ($products as $key => $product) {
            $shop = Shop::where('id', $product->shop_id)->first('name');
            $product->shop = $shop->name;
            $products[$key] = $product;
        }
        return response()->json($products->select('name', 'price', 'quantity', 'shop'), 200);
    }

    /**
     *This function return the informations of a certian product
    **/
    public function getCertainProduct(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $shop = Shop::where('id', $product->shop_id)->first('name');
        $product->shop = $shop->name;
        return response()->json([
            'name' => $product->name,
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
        $validatedData = $request->validate(
            [
                'name' => 'required|string',
                'price' => 'required',
                'quantity' => 'required',
                'shop_id' => 'required',
            ]
        );

        $product = Product::create($validatedData);
        return response()->json(['message' => 'Product stored successfully'], 200);
    }

    /**
     *This function updates an existing product to the app
    **/
    public function updateProduct(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'price' => 'sometimes',
            'quantity' => 'sometimes',
            'shop_id' => 'required',
        ]);
        $product = Product::findOrFail($id);
        $product->update($validatedData);
        return response()->json(['message' => 'Product updated successfully'], 200);
    }

    /**
     *This function updates the quantity of a product , and if the quantity becomes 0 , it deletes the product from the app
    **/
    public function deleteProduct(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $q = $request->quantity;

        if ($product->quantity == $q) {
            $product->delete();
        } elseif ($product->quantity < $q) {
            return response()->json(['message' => 'This quantity isn\'t available' ], 400);
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
        $products = Product::with('shop')
            ->where('name', 'LIKE', $request->text . '%')
            ->select('name','price','quantity','shop_id')
            ->get()
            ->map(function ($product) {
                return [
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $product->quantity,
                    'shop' => $product->shop->name
                ];
            });

        return response()->json($products, 200);
    }

    public function addToFavorite(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $product->users()->attach(
            $request->user_id
        );
        return response()->json([
            "message" => "Product added to favorite successfully"
        ]);
    }

    public function cancelFromFavorites(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $product->users()->detach(
            $request->user_id
        );
        return response()->json([
            "message" => "Product canceled from favorites successfully"
        ]);
    }
}
