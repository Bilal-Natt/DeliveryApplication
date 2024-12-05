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
     * This function is used to store a new order and update the quantities of the products associated with that order.
     * The request body must include the following parameters:
     *
     * user_id: The ID of the user placing the order.
     * products: An array of objects, where each object contains:
     * product_id: The ID of the product being ordered.
     * quantity: The quantity of that product.
     */
    public function storeOrder(Request $request)
    {
        $order = Order::create([
            'user_id' => $request->user_id,
            'status_id' => Status::where('name','empty')->first()->id,
        ]);
        $products = $request -> products;
        foreach ($products as $product) {
            $productDB = Product::find($product['product_id']);
            if($product['quantity'] > $productDB->quantity) {
                return response()->json(['message' => $productDB->name.' quantity isn\'t available' ], 400);
            }
            elseif ($product['quantity'] == $productDB->quantity) {
                $productDB->delete();
            }
            else {
                $productDB->update(['quantity' => $productDB->quantity - $product['quantity']]);
                $productDB->save();
            }
            $order->products()->attach(
                $product['product_id'],
                [
                'quantity' => $product['quantity'],
                ]);
        }
        $total = $this->setTotal($order->id);
        $order->update(['shipping_cost' => $total*0.02]);

        return response()->json(["message" => "Order added successfully", "order" =>[
            "id" => $order->id,
            "user_id" => $order->user_id,
            "status_id" => $order->status_id,
            "total" => $total,
            "shipping_cost" => $order->shipping_cost
        ]]);
    }

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
        return $total;
    }
}
