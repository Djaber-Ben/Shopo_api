<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class StoreApiController extends Controller
{
    /**
     * Display all avilable stores.
     */
    public function index()
    {
        $stores = Store::with('category')
        ->where('status', 'active')
        ->get();

        return response()->json([
            'stores' => $stores,
        ], 200);
    }

    /**
     * Return the authenticated user and all available categories.
     */
    public function create()
    {
        $user = Auth::user();
        $categories = Category::all();

        return response()->json([
            'user' => $user,
            'categories' => $categories,
        ], 200);
    }

    /**
     * Store a newly created store in the database.
     */
    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'store_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'address_url' => 'required|url|max:500',
            // 'latitude' => 'required|numeric|between:-90,90',
            // 'longitude' => 'required|numeric|between:-180,180',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'whatsapp' => 'nullable|url|max:255',
            'facebook' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'tiktok' => 'nullable|url|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }
        
        // Check if user can become a vendor
        $user = Auth::user();
        if (!in_array($user->user_type, ['client', 'pending'])) {
            return response()->json([
                'message' => 'Only clients or pending users can become vendors, you already have been a vendor and you have a store',
            ], 403);
        }

        // Update user_type to vendor
        $user->update(['user_type' => 'vendor']);

        // Create the store
        $store = Store::create([
            'vendor_id' => $user->id, // Set to authenticated user's ID
            'category_id' => $request->category_id,
            'store_name' => $request->store_name,
            'description' => $request->description,
            'logo' => $request->hasFile('logo') ? $request->file('logo')->store('images/stores/logos', 'public') : null,
            'image' => $request->hasFile('image') ? $request->file('image')->store('images/stores/thumbnails', 'public') : null,
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'address_url' => $request->address_url,
            // 'latitude' => $request->latitude,
            // 'longitude' => $request->longitude,
            'status' => 'active', // Default status
            'whatsapp' => $request->whatsapp,
            'facebook' => $request->facebook,
            'instagram' => $request->instagram,
            'tiktok' => $request->tiktok,
        ]);

        return response()->json([
            'message' => 'Store created successfully',
            'store' => $store,
        ], 201);
    }

    /**
     * Display the specified store.
     */
    public function show($id)
    {
        $store = Store::where('vendor_id', Auth::id())->with('category')->findOrFail($id);

        return response()->json([
            'store' => $store,
        ], 200);
    }

    /**
     * Update the specified store in the database.
     */
    public function update(Request $request, $id)
    {
        $store = Store::where('vendor_id', Auth::id())->findOrFail($id);

        // Validate the request
        $validator = Validator::make($request->all(), [
            'category_id' => 'sometimes|exists:categories,id',
            'store_name' => 'sometimes|string|max:255',
            'phone_number' => 'sometimes|string|max:20',
            'address' => 'sometimes|string|max:500',
            'address_url' => 'sometimes|url|max:500',
            // 'latitude' => 'sometimes|numeric|between:-90,90',
            // 'longitude' => 'sometimes|numeric|between:-180,180',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'whatsapp' => 'nullable|url|max:255',
            'facebook' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'tiktok' => 'nullable|url|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        // Prepare data for update
        $data = $request->only([
            'category_id', 'store_name', 'description',
            'phone_number', 'address', 'address_url', 'whatsapp', 'facebook',
            'instagram', 'tiktok'
        ]);

        // If a new image is uploaded Delete old file if exists
        // and Handle file uploads
        if ($request->hasFile('logo') && $store->logo) {
            Storage::disk('public')->delete($store->logo);
            $data['logo'] = $request->file('logo')->store('images/stores/logos', 'public');
        }
        if ($request->hasFile('image') && $store->image) {
            Storage::disk('public')->delete($store->image);
            $data['image'] = $request->file('image')->store('images/stores/thumbnails', 'public');
        }

        // Update the store
        $store->update($data);

        return response()->json([
            'message' => 'Store updated successfully',
            'store' => $store,
        ], 200);
    }

    /**
     * Remove the specified store from the database.
     */
    public function destroy($id)
    {
        $store = Store::where('vendor_id', Auth::id())->findOrFail($id);
        $store->delete();

        return response()->json([
            'message' => 'Store deleted successfully',
        ], 200);
    }
}
