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
<<<<<<< HEAD
        $orders = Order::where('user_id', $request->user_id)->where('status_id', 1)->select('id', 'total', 'shipping_cost')->get();
        return response()->json($orders, 200);
=======
        $orders = Order::where('user_id', $request->id)->get();
        $purchasedOrders = [];
        $productsPerOrder = [];
        foreach ($orders as $order) {
            $status = Status::where('id', $order->status_id)->get();
            if ($status[0]['name'] == 'Purchased') {
                $productsPerOrder[] = OrderController::getOrderProducts($order->id);

                $purchasedOrders[] = $order;
            }
        }
        foreach ($purchasedOrders as $key => $order) {
            $order->products =  $productsPerOrder[$key]->select('name', 'price', 'shop_id');
            // $s = new shopController();
            // foreach ($order->products as $product) {
            // $product['shop_id'] = $s->getShop($product['shop_id'])->name;
            // }
        }
        return response()->json($purchasedOrders, 200);
>>>>>>> ac99f303cc73646adbc7bbe257831b29ed11b4bc
    }

    /** 
     *This function returns All the Products of certain Order
    **/
    public function getOrderProducts($id)
    {
<<<<<<< HEAD
        $products = Order::findOrFail($id)->products->map(function ($product) {
            return [
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $product->pivot->quantity,
                'shop' => Shop::where('id', $product->shop_id)->pluck('name')->first()
            ];
        });
        return response()->json($products, 200);
=======
        $products = Order::findOrFail($id)->products;
        return $products;
>>>>>>> ac99f303cc73646adbc7bbe257831b29ed11b4bc
    }

    /** 
     *This function calculates the total cost of an order
    **/
    private function setTotal($id)
    {
        $order = Order::findOrFail($id);
        $total = 0;
<<<<<<< HEAD
        $file = $this->getOrderProducts($order->id);
        $products = json_decode($file->getContent(), true);
        foreach ($products as $product) {
            $total += $product['price'] * $product['quantity'];
=======
        $products = OrderController::getOrderProducts($order->id);
        foreach ($products as $product) {
            $total += $product->price * $product->pivot->quantity;
>>>>>>> ac99f303cc73646adbc7bbe257831b29ed11b4bc
        }
        $order->update(['total' => $total]);
    }
}
