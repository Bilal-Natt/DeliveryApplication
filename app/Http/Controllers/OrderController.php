<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Status;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function getPurchasedOrders(Request $request)
    {
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
    }

    public function getOrderProducts($id)
    {
        $products = Order::findOrFail($id)->products;
        return $products;
    }

    public function setTotal($id)
    {
        $order = Order::findOrFail($id);
        $total = 0;
        $products = OrderController::getOrderProducts($order->id);
        foreach ($products as $product) {
            $total += $product->price * $product->pivot->quantity;
        }
        $order->update(['total' => $total]);
    }
}
