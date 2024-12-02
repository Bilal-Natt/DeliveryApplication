<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;
use function Laravel\Prompts\select;

class ProductController extends Controller
{
    public function getProducts()
    {
        $products = Product::select('name', 'price', 'quantity', 'shop_id')->get();
        foreach ($products as $key => $product) {
            $shop = Shop::where('id', $product->shop_id)->first('name');
            $product->shop = $shop->name;
            $products[$key] = $product;
        }
        return response()->json($products->select('name', 'price', 'quantity', 'shop'), 200);
    }

    public function getProduct(Request $request)
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

    public function deleteProduct($id, $number)
    {
        $product = Product::findOrFail($id);
        if ($product->quantity == $number) {
            $product->delete();
        } else {
            $product->quantity = $product->quantity - $number;
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
}
