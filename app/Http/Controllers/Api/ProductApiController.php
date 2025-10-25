<?php

namespace App\Http\Controllers\Api;

use App\Models\Store;
use App\Models\Images;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
            ->with('images')
            ->paginate(20);

        return response()->json([
            'store_id' => $store_id,
            'products' => $products,
        ], 200);
    }

    /**
     * Return data needed to create a new product.
     */
    public function create()
    {
        $user = Auth::user();
        $stores = Store::where('vendor_id', $user->id)
        ->where('status', 'active')
        ->get();;

        return response()->json([
            'user' => $user,
            'stores' => $stores,
        ], 200);
    }

    /**
     * Store a newly created product in the database.
     */
    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|exists:stores,id',
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'description' => 'nullable|string',
            'related_products' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'qty' => 'nullable|integer|min:0',
            'track_qty' => 'required|in:yes,no',
            'status' => 'required|in:active,inactive,out_of_stock',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        // Ensure the store belongs to the authenticated vendor
        $store = Store::where('id', $request->store_id)
            ->where('vendor_id', Auth::id())
            ->firstOrFail();

        DB::beginTransaction();

        try {
            // Create product
            $product = Product::create([
                'store_id' => $request->store_id,
                'title' => $request->title,
                'image' => $request->hasFile('image') ? $request->file('image')->store('images/products/main', 'public') : null,
                'description' => $request->description,
                'related_products' => $request->related_products,
                'price' => $request->price,
                'compare_price' => $request->compare_price,
                'qty' => $request->qty,
                'track_qty' => $request->track_qty,
                'status' => $request->status,
            ]);

            // Store additional images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    if ($image->isValid()) {
                        Images::create([
                            'product_id' => $product->id,
                            'image' => $image->store('images/products/sub', 'public'),
                            'is_primary' => false,
                        ]);
                    }
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified product only for the store owner.
     */
    public function edit($id)
    {
        $product = Product::whereHas('store', function ($query) {
            $query->where('vendor_id', Auth::id());
        })
        ->with('images')
        ->findOrFail($id);
        
        return response()->json([
            'product' => $product,
        ], 200);
    }
    
    /**
     * Display the specified product to all users.
     */
    public function show($id)
    {
        $product = Product::with('store')
        ->with('images')
        ->findOrFail($id);

        return response()->json([
            'product' => $product,
        ], 200);
    }


    /**
     * Update the specified product in the database.
     */
    public function update(Request $request, $id)
    {
        $product = Product::whereHas('store', function ($query) {
            $query->where('vendor_id', Auth::id());
        })->findOrFail($id);

        // Validate the request
        $validator = Validator::make($request->all(), [
            'store_id' => 'sometimes|exists:stores,id',
            'title' => 'sometimes|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'description' => 'nullable|string',
            'related_products' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'qty' => 'nullable|integer|min:0',
            'track_qty' => 'sometimes|in:yes,no',
            'status' => 'sometimes|in:active,inactive,out_of_stock',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        // Ensure the store belongs to the authenticated vendor if store_id is provided
        if ($request->has('store_id')) {
            Store::where('id', $request->store_id)
                ->where('vendor_id', Auth::id())
                ->firstOrFail();
        }

        // Generate slug if provided
        $data = $request->only([
            'store_id', 'title', 'description', 'related_products',
            'price', 'compare_price', 'qty', 'track_qty', 'status'
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('images/products/main', 'public');
        } elseif ($request->has('image') && $request->image === null) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = null;
        }

        // Handle additional images
        if ($request->hasFile('images')) {
            // Optionally delete existing images if replacing
            $product->images()->delete();
            foreach ($request->file('images') as $image) {
                if ($image->isValid()) {
                    Images::create([
                        'product_id' => $product->id,
                        'image' => $image->store('images/products', 'public'),
                        'is_primary' => false,
                    ]);
                }
            }
        }

        // Update the product
        $product->update($data);

        // Load images relationship for response
        $product->load('images');

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product,
        ], 200);
    }

    /**
     * Remove the specified product from the database.
     */
    public function destroy($id)
    {
        $product = Product::whereHas('store', function ($query) {
            $query->where('vendor_id', Auth::id());
        })->findOrFail($id);

        // Delete main image if exists
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        // Delete additional images (handled by cascade on images table)
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
        ], 200);
    }
}
