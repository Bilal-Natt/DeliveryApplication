<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Shop;
use App\Models\Status;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    /** 
     *This function returns All of the Completed Orders of a certain user
    **/
    public function getPurchasedOrders(Request $request)
    {

        $orders = Order::where('user_id', $request->user_id)->where('status_id', 1)->select('id', 'total', 'shipping_cost')->get();
        return response()->json($orders, 200);
    }

    /** 
     *This function returns All the Products of certain Order
    **/
    public function getOrderProducts($id)
    {
        $products = Order::findOrFail($id)->products->map(function ($product) {
            return [
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $product->pivot->quantity,
                'shop' => Shop::where('id', $product->shop_id)->pluck('name')->first()
            ];
        });
        return response()->json($products, 200);
    }

    /** 
     *This function calculates the total cost of an order
    **/
    private function setTotal($id)
    {
        $order = Order::findOrFail($id);
        $total = 0;
        $file = $this->getOrderProducts($order->id);
        $products = json_decode($file->getContent(), true);
        foreach ($products as $product) {
            $total += $product['price'] * $product['quantity'];
        }
        $order->update(['total' => $total]);
    }
}
