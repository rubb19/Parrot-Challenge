<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Http\Request;


class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)->get();
        return response()->json([
            'success' => true,
            'orders' => $orders
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $request->validate([
            'customer_name' => 'required',
            'products' => 'required'
        ]);
        try
        {
            $total = 0;
            $order = new Order();
            $order->user_id = $request->user()->id;
            $order->customer_name = $request->customer_name;
            $order->products = json_encode($request->products);
            $order->total = $total;
            $order->save();
            foreach ($request->products as $item)
            {
                $product = Product::where('name',$item[0])->where('user_id',$request->user()->id)->first();
                if($product)
                {
                    $product->price = $item[1];
                    $product->sales += $item[2];
                    $product->save();
                }
                else
                {
                    $product = new Product();
                    $product->user_id = $request->user()->id;
                    $product->name = $item[0];
                    $product->price = $item[1];
                    $product->sales += $item[2];
                    $product->save();
                }
                $orderProduct = new OrderProduct();
                $orderProduct->order_id = $order->id;
                $orderProduct->product_id = $product->id;
                $orderProduct->quantity = $item[2];
                $orderProduct->save();
                $total += $item[1] * $item[2];
            }
 
            return response()->json([
                'success' => true,
                'msg' => "Your order was successfully registered!"
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'msg' => "It wasn't possible to create your order",
                'details' => json_encode($th)
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order, Request $request)
    {
        if(!$this->belongsToUser($order, $request))
        {
            return response()->json([
                'success' => false,
                'msg' => "Unable to view the requested source. Unauthorized."
            ], 401);
        }
        return response()->json([
            'success' => true,
            'msg' => "Successful request.",
            'order' => $order
        ], 200);
        
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order, Request $request)
    {
        if(!$this->belongsToUser($order, $request))
        {
            return response()->json([
                'success' => false,
                'msg' => "Unable to delete the requested source. Unauthorized."
            ], 401);
        }
        try
        {
            foreach (json_decode($order->products) as $item)
            {
                $product = Product::where('name',$item[0])->where('user_id',$request->user()->id)->first();
                if($product)
                {
                    $product->sales -= $item[2];
                    $product->save();
                }
            }
            $order->delete();
            return response()->json([
                'success' => true,
                'msg' => "The order was successfully removed"
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'msg' => "It wasn't possible to delete your order"
            ], 400);
        }
    }

    /**
     * Indica si la orden pertenece o no al usuario
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function belongsToUser(Order $order, Request $request)
    {
        if($order->user_id == $request->user()->id)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}
