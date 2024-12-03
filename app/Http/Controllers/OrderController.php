<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Status;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function getPurchasedOrders(Request $request)
    {
        $orders = Order::where('user_id', $request->user_id)->where('status_id',1)->select('id' , 'total' , 'shipping_cost')->get();
        return response()->json($orders, 200);
    }

    public function getOrderProducts($id)
    {
        $products = Order::findOrFail($id)->products->map(function($product){
            return [
                 'name' => $product->name,
                 'price'=> $product->price,
                 'quantity'=> $product->pivot->quantity
             ];
        });
        return $products;
    }

    public function setTotal($id)
    {
        $order = Order::findOrFail($id);
        $total = 0;
        $products = $this->getOrderProducts($order->id);
        foreach ($products as $product) {
            $total += $product['price']* $product['quantity'];
        }
        $order->update(['total' => $total]);
    }
}
