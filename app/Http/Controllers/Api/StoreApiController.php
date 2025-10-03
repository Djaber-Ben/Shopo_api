<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StoreApiController extends Controller
{
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
     * Validate and store the store information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'vendor_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:categories,id',
            'store_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'description' => 'nullable|string',
            'logo' => 'nullable|string|max:255',
            'image' => 'nullable|string|max:255',
            'subscription_timer' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        // Create the store
        $store = Store::create([
            'vendor_id' => $request->vendor_id,
            'category_id' => $request->category_id,
            'store_name' => $request->store_name,
            'description' => $request->description,
            'logo' => $request->logo,
            'image' => $request->image,
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'status' => 'active', // Default status
            'subscription_timer' => $request->subscription_timer,
        ]);

        return response()->json([
            'message' => 'Store created successfully',
            'store' => $store,
        ], 201);
    }
}
