<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\OrderProduct;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */
    public function index(Request $request)
    {
        $products = Product::where('user_id', $request->user()->id)->orderByDesc('sales')->get();
        return response()->json([
            'success' => true,
            'msg' => 'Successful query',
            'product' => $products
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
            'name' => 'required',
            'price' => 'required'
        ]);
        $msg = "It wasn't possible to create your product";
        try {
            $product = Product::where('name',$request->name)->where('user_id',$request->user()->id)->first();
            if($product)
            {
                $product->name = $request->name;
                $product->price = $request->price;
                $product->save();
                $msg = "Your product was successfully updated";
            }
            else
            {
                $product = new Product();
                $product->name = $request->name;
                $product->price = $request->price;
                $product->sales = 0;
                $product->user_id = $request->user()->id;
                $product->save();
                $msg = "Your product was successfully created";
            }
            return response()->json([
                'success' => true,
                'msg' => $msg,
                'product' => $product
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'msg' => $msg
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product, Request $request)
    {
        if(!$this->belongsToUser($product, $request))
        {
            return response()->json([
                'success' => false,
                'msg' => "Unable to view the requested source. Unauthorized."
            ], 401);
        }
        return response()->json([
            'success' => true,
            'msg' => "Successful request.",
            'product' => $product
        ], 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Product $product, Request $request)
    {
        if(!$this->belongsToUser($product, $request))
        {
            return response()->json([
                'success' => false,
                'msg' => "Unable to access to the requested source. Unauthorized."
            ], 401);
        }
        try
        {
            $product->name = $request->name;
            $product->price = $request->price;
            $product->save();

            return response()->json([
                'success' => true,
                'msg' => "Your product was successfully updated"
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'msg' => "It wasn't possible to update your product"
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product, Request $request)
    {
        if(!$this->belongsToUser($product, $request))
        {
            return response()->json([
                'success' => false,
                'msg' => "Unable to delete the requested source. Unauthorized."
            ], 401);
        }
        try
        {
            $product->delete();
            return response()->json([
                'success' => true,
                'msg' => "The product was successfully removed"
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'msg' => "It wasn't possible to delete your product"
            ], 400);
        }
    }

    /**
     * Indica si el producto pertenece o no al usuario
     *
     * @param  \App\Models\Product $prodct
     * @return \Illuminate\Http\Response
     */
    public function belongsToUser(Product $product, Request $request)
    {
        if($product->user_id == $request->user()->id)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}
