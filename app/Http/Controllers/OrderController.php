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
        $lang = $request->lang;
        if($lang == null) {return response()->json([
            "message" => "Send The Language Pleas"
        ],400);}
        $order = null;
        if($lang == "ar") {
            $order = Order::create([
                'user_id' => $request->user_id,
                'status_id' => Status::where('ar_name', 'قيد الانتظار')->first()->id,
            ]);
        }
        else{
            $order = Order::create([
                'user_id' => $request->user_id,
                'status_id' => Status::where('en_name', 'In Queue')->first()->id,
            ]);
        }
        $products = $request -> products;
        foreach ($products as $product) {
            $productDB = Product::find($product['product_id']);
            $message = [];
            $message["ar"] = 'الكمية غير متاحة';
            $message["en"] = 'quantity isn\'t available';
            $message = $message[$lang];
//            $name = $name[$lang];
            if($product['quantity'] > $productDB->quantity) {
                return response()->json(['message' => $message], 400);
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
                    'price' => $productDB->price,
                ]);
        }
        $total = $this->setTotal($order->id);
        $message = [];
        $message["ar"] = 'تمت إضافة الطلب بنجاح';
        $message["en"] = 'Order added successfully';
        $message = $message[$lang];

        return response()->json(["message" => $message, "order" =>[
            "id" => $order->id,
            "user_id" => $order->user_id,
            "status_id" => $order->status_id,
            "total" => $total,
            "shipping_cost" => $order->shipping_cost
        ]]);
    }

    /**
     *This function returns All of the Completed Orders of a certain user
     * The request body must include the following parameters:
     ** $user_id : the id of the user requesting his purchased orders
     **  الطلب الرابع في الطلبات الأساسية
     **/
    public function getOrders(Request $request)
    {
        $orders = Order::where('user_id', $request->user_id)->get();
        $orders = Order::join('statuses', 'orders.status_id', '=', 'statuses.id')
            ->select('orders.id','orders.total','orders.shipping_cost', 'statuses.ar_name as ar_status','statuses.en_name as en_status')->get();
        return response()->json($orders, 200);
    }

    /**
     *This function returns All the Products of certain Order
     * The request body must include the following parameters:
     ** $orderID : the ID of the order to get his products
     ** تكملة الطلب الرابع في الطلبات الأساسية
     **/
    public function getOrderProducts(Request $request)
    {
        $products = Order::findOrFail($request->order_id)->products->map(function ($product) {
            return [
                'en_name' => $product->en_name,
                'ar_name' => $product->ar_name,
                'price' => $product->pivot->price,
                'quantity' => $product->pivot->quantity,
                'ar_shop' => Shop::where('id', $product->shop_id)->pluck('ar_name')->first(),
                'en_shop' => Shop::where('id', $product->shop_id)->pluck('en_name')->first()
            ];
        });

        return response()->json(['message' => 'your Order consists of the following products :', 'data' => $products], 200);
    }

    /**
     * this function is to delete (cancel) an order and return all products quantities to the original products
     * The request body must include the following parameters:
     ** $orderID : the ID of the order to delete it
     **   الطلب الخامس في الطلبات الأساسية
     */
    public function deleteOrders(Request $request)
    {
        $lang = $request->lang;
        if($lang == null) {return response()->json([
            "message" => "Send The Language Pleas"
        ],400);}
        $orders = Order::findMany($request->orderID);
        foreach ($orders as $order) {
            $orderProducts = Order::findOrFail($order->id)->products;
            foreach ($orderProducts as $product) {
                $this->deleteProductFromOrder($product);
            }
            $order->delete();
        }
        $message = [];
        $message["ar"] = 'تم حذف الطلب بنجاح';
        $message["en"] = 'The orders has been deleted successfully';
        $message = $message[$lang];
        return response()->json(['message' => $message] , 200);
    }

    /**
     * this function is to update an order by adding , deleting and updating products informations
     * The request body must include the following parameters:
     *witch
     ** $orderID : The ID of the order that its products being updated
     ** $productID : The ID of the product being updated/added
     ** $quantity : The quantity of the product
     ** $op : the operation to be performed takes only two values (update OR add) : update (to update the quantity) OR add (to add a new product)
     * كل المتغيرات السابقة لازمة لتحديد أي من العملبتبن سوف تتنفذ
     ** $state : this param is for the update operation , it is a int param 0 is to decress the quantity (delete a product or sub its quantity) and 1 is to add incress the quantity of an existing product
     * الخاصة بتعديل كمية منتج موجود مسبقا update هذا المتغير خاص فقط بعملية ال
     ** تكملة الطلب الخامس في الطلبات الأساسية
     */
    public function updateOrder(Request $request)
    {
        $lang = $request->lang;
        if($lang == null) {return response()->json([
            "message" => "Send The Language Pleas"
        ],400);}
        $message = [];
        $message["ar"] = 'تمت العملية بنجاح';
        $message["en"] = 'Process accomplished successfully';
        $response = $message[$lang];
        $status = 200;
        if ($request->op === 'update') {
            $product = Order::findOrFail($request->orderID)->products->where('id', $request->productID)->first();

            $response = $this->updateProductInOrder($product, $request->quantity, $request->state,$lang);
            $status = $response->getStatusCode();
            $response = $response->getData()->data;


        } elseif ($request->op === 'add') {
            $product = Product::findOrFail($request->productID);
            if ($product->quantity - $request->quantity <= 0) {
                $message = [];
                $message["ar"] = 'الكمية المتبقية أقل من';
                $message["en"] = 'the remaining quantity is less than';
                $message = $message[$lang];
                $response = "$message $request->quantity";
                $status = 400;
            }
            else{
            $order = Order::findOrFail($request->orderID);
            $order->products()->attach(
                $request->productID,
                [
                'quantity' => $request->quantity
                ]
            );
            $product->update(['quantity' => $product->quantity -= $request->quantity]);
        }
        }
        $this->setTotal($request->orderID);
        return response()->json(['message' => $response], $status);
    }

    /**
     *This function calculates the total cost of an order
     ** $orderID : the ID of the order to calculate its total and the shipping cost
     **/
    private function setTotal($orderID)
    {
        $order = Order::findOrFail($orderID);
        $total = 0;
        $products = Order::findOrFail($orderID)->products;
        foreach ($products as $product) {
            $total += $product->pivot->price * $product->pivot->quantity;
        }
        $order->update([
            'total' => $total,
            'shipping_cost' => $total*0.02
            ]);
        return $total;
    }

    /**
     * this function is to deletes product quantity from an order and returns its quantity to the original products quantity
     ** $product : the object of type product to delete its quantity from the order
     * */
    private function deleteProductFromOrder($product)
    {
        $orignalProduct = Product::findOrFail($product->id);
        $orignalProduct->update (['quantity' => $orignalProduct->quantity += $product->pivot->quantity]);
    }

    /**
     * this function updates a product in an order by incressing/decressing quantity or deletes the product from the order
     */
    private function updateProductInOrder($product, $quantity, $op,$lang)
    {
        if ($product->pivot->quantity < $quantity && $op == 0) {
            return  response()->json(['data'=>'invalid opration'] , 400);
        }
        if ($product->pivot->quantity == $quantity && $op == 0 ) {
            $this->deleteProductFromOrder($product);
            $product->pivot->delete();
        } else if ($product->pivot->quantity  > $quantity && $op == 0) {
            $product->pivot->update(['quantity' => $product->pivot->quantity -= $quantity]);

            $orignalProduct = Product::findOrFail($product->id);
            $orignalProduct->update(['quantity' => $orignalProduct->quantity+= $quantity]);
        } elseif ($op == 1) {
            $orignalProduct = Product::findOrFail($product->id);
            if ($orignalProduct->quantity - $quantity < 0 ) {
                $message["ar"] = 'الكمية المتبقية أقل من';
                $message["en"] = 'the remaining quantity is less than';
                $message = $message[$lang];
                return response()->json(['data'=>"the remaining quantity is less than $quantity"] , 400);
            }
            $orignalProduct->update(['quantity' => $orignalProduct->quantity  -= $quantity]) ;
            $product->pivot->update (['quantity' => $product->pivot->quantity  += $quantity]);
        }
        $message = [];
        $message["ar"] = 'تمت العملية بنجاح';
        $message["en"] = 'Process accomplished successfully';
        $message = $message[$lang];
        return response()->json(['data'=> $message] , 200);
    }
}
