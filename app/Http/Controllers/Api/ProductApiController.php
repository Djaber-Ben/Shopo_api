<?php

namespace App\Http\Controllers\Api;

use App\Models\Store;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductApiController extends Controller
{
    /**
     * Display all products of the store.
     */
    public function index(Request $request)
    {
        // Validate store_id
        $validator = Validator::make($request->query(), [
            'store_id' => 'required|exists:stores,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid store ID',
                'errors' => $validator->errors(),
            ], 422);
        }

        $store_id = $request->query('store_id');

        // Ensure the store exist
        $store = Store::where('id', $store_id)
            ->firstOrFail();

        $products = Product::where('store_id', $store_id)
            ->where('status', 'active')
            // ->with('category')
            ->paginate(10);

        return response()->json([
            'store_id' => $store_id,
            'products' => $products,
        ], 200);

        // $store_products = Store::find($store_id)
        // ->with('products')
        // ->get();

        // return response()->json([
        //     'products' => $store_products,
        // ], 200);
    }
}
