<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Status;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    /**
     *This function returns All of the Completed Orders of a certain user
     **  الطلب الرابع في الطلبات الأساسية  
     **/
    public function getPurchasedOrders(Request $request)
    {
        $orders = Order::where('user_id', $request->user_id)->where('status_id', 1)->select('id', 'total', 'shipping_cost')->get();
        return response()->json($orders, 200);
    }

    /**
     *This function returns All the Products of certain Order
     ** تكملة الطلب الرابع في الطلبات الأساسية  
     **/
    public function getOrderProducts($id)
    {
        $products = Order::findOrFail($id)->products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $product->pivot->quantity,
                'shop' => Shop::where('id', $product->shop_id)->pluck('name')->first()
            ];
        });

        return response()->json(['message' => 'your Order consists of the following products :', 'data' => $products], 200);
    }

    /**
     * this function is to delete (cancel) an order and return all products quantities to the original products
     **   الطلب الخامس في الطلبات الأساسية 
     */
    public function deleteOrders(Request $request)
    {
        $orders = Order::findMany($request->id);
        foreach ($orders as $order) {
            $file = $this->getOrderProducts($order->id);
            $products = $file->getData()->data;
            foreach ($products as $product) {
                $this->deleteProductFromOrder($product);
            }
            $order->delete();
        }
    }

    /**
     * this function is to update an order by adding , deleting and updating products informations
     ** تكملة الطلب الخامس في الطلبات الأساسية
     */
    public function updateOrder(Request $request)
    {
        $response = 'Process accomplished successfully';
        $status = 200;
        if ($request->op === 'update') {
            $state = filter_var($request->state, FILTER_VALIDATE_BOOLEAN);
            $product = Order::findOrFail($request->orderId)->products->where('id', $request->productId)->first();

            $response = $this->updateProductInOrder($product, $request->quantity,  $state);
            $status = $response->getStatusCode();
            $response = $response->getData()->data;
            
        } elseif ($request->op === 'add') {
            $order = Order::findOrFail($request->orderId);
            $order->products()->attach($request->productId, [
                'quantity' => $request->quantity
            ]);
            $product = Product::findOrFail($request->productId);
            $product->quantity -= $request->quantity;
            if ($product->quantity < 0) {
                $response =  "the remaining quantity is less than $request->quantity";
                $status = 400;
            } else
                $product->save();
        }
        $this->setTotal($request->orderId);
        return response()->json(['message' => $response], $status);
    }

    /**
     *This function calculates the total cost of an order
     **/
    private function setTotal($id)
    {
        $order = Order::findOrFail($id);
        $total = 0;
        $file = $this->getOrderProducts($order->id);
        $products = $file->getData()->data;
        foreach ($products as $product) {
            $total += $product->price * $product->quantity;
        }
        $order->update(['total' => $total]);
    }

    /**
     * this function is to deletes product quantity from an order and returns its quantity to the original products quantity 
     */
    private function deleteProductFromOrder($product)
    {
        $orignalProduct = Product::findOrFail($product->id);
        $orignalProduct->quantity += $product->pivot->quantity;
        $orignalProduct->save();
    }

    /** 
     * this function updates a product in an order by incressing/decressin quantity or deletes the product from the order
     */
    private function updateProductInOrder($product, $quantity, $op)
    {
        if ($product->pivot->quantity < $quantity && !$op) {
            return  response()->json(['data'=>'invalid opration'] , 400);
        }
        if ($product->pivot->quantity == $quantity && !$op) {
            $this->deleteProductFromOrder($product);
            $product->pivot->delete();
        } else if ($product->pivot->quantity  > $quantity && !$op) {
            $product->pivot->quantity  -= $quantity;
            $product->pivot->save();

            $orignalProduct = Product::findOrFail($product->id);
            $orignalProduct->quantity  += $quantity;
            $orignalProduct->save();
        } elseif ($op) {
            $orignalProduct = Product::findOrFail($product->id);
            $orignalProduct->quantity  -= $quantity;
            if ($orignalProduct->quantity < 0) {
                return response()->json(['data'=>"the remaining quantity is less than $quantity"] , 400);
            }
            $orignalProduct->save();

            $product->pivot->quantity  += $quantity;
            $product->pivot->save();
        }
        return response()->json(['data'=>'Process accomplished successfully'] , 200);
    }
}
