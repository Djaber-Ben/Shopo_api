<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class StoreApiController extends Controller
{
    
    /**
     * Display all avilable nearby stores.
     */
    public function nearby(Request $request)
    {
        $validator = Validator::make($request->query(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid location coordinates or radius',
                'errors' => $validator->errors(),
            ], 422);
        }

        $latitude = $request->query('latitude');
        $longitude = $request->query('longitude');
        $radius = $request->query('radius', 20); // default 20 km

        if (!$latitude || !$longitude) {
            return response()->json(['message' => 'Location coordinates required.'], 400);
        }

        $stores = Store::selectRaw("
                *,
                (6371 * acos(cos(radians(?))
                * cos(radians(latitude))
                * cos(radians(longitude) - radians(?))
                + sin(radians(?))
                * sin(radians(latitude)))) AS distance
            ", [$latitude, $longitude, $latitude])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->having('distance', '<', $radius)
            ->where('status', 'active')
            ->with(['products' => function ($query) {
                $query->where('status', 'active');
            }])
            ->orderBy('distance')
            ->paginate(20);

        return response()->json([
            'user_location' => ['lat' => $latitude, 'lng' => $longitude],
            'radius_km' => $radius,
            'stores' => $stores,
        ]);
    }

    /**
     * Display all avilable stores.
     */
    public function index()
    {
        $stores = Store::with('category')
        ->where('status', 'active')
        ->with(['products' => function ($query) {
            $query->where('status', 'active');
        }])
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
    
    // Extract lat/lng from the Google Maps link
    public function extractCoordinates($mapUrl)
    {
        // Match patterns like @40.7128,-74.0060 or q=40.7128,-74.0060
        if (preg_match('/@(-?\d+\.\d{1,8}),(-?\d+\.\d{1,8})/', $mapUrl, $matches) ||
            preg_match('/q=(-?\d+\.\d{1,8}),(-?\d+\.\d{1,8})/', $mapUrl, $matches)) {
            $latitude = (float) $matches[1];
            $longitude = (float) $matches[2];

            // Validate coordinate ranges
            if ($latitude >= -90 && $latitude <= 90 && $longitude >= -180 && $longitude <= 180) {
                return [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ];
            }
        }
        return null;
    }

    // public function extractCoordinates($mapUrl)
    // {
    //     // Match various Google Maps URL formats
    //     $patterns = [
    //         '/@(-?\d+\.\d{0,8}),(-?\d+\.\d{0,8})/', // @latitude,longitude
    //         '/[?&]query=(-?\d+\.\d{0,8}),(-?\d+\.\d{0,8})/', // query=latitude,longitude
    //         '/!3d(-?\d+\.\d{0,8})!4d(-?\d+\.\d{0,8})/', // !3dlatitude!4dlongitude
    //     ];

    //     foreach ($patterns as $pattern) {
    //         if (preg_match($pattern, $mapUrl, $matches)) {
    //             $latitude = (float) $matches[1];
    //             $longitude = (float) $matches[2];

    //             // Validate coordinate ranges
    //             if ($latitude >= -90 && $latitude <= 90 && $longitude >= -180 && $longitude <= 180) {
    //                 return [
    //                     'latitude' => $latitude,
    //                     'longitude' => $longitude,
    //                 ];
    //             }
    //         }
    //     }

    //     return null;
    // }
    
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
        
        // Extract lat/lng from the Google Maps link
        // Then save to the store
        $coords = $this->extractCoordinates($request->address_url);
        if ($coords) {
            $latitude = $coords['latitude'];
            $longitude = $coords['longitude'];
        }

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
            'latitude' => $latitude,
            'longitude' => $longitude,
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
     * Display the specified store only for the store owner.
     */
    public function edit($id)
    {
        $store = Store::where('vendor_id', Auth::id())->with('category')->findOrFail($id);

        return response()->json([
            'store' => $store,
        ], 200);
    }
    
    /**
     * Display the specified store to all users.
     */
    public function show($id)
    {
        $store = Store::with('category')
        ->findOrFail($id);

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

        // Extract lat/lng from the Google Maps link
        // Then save to the store
        $coords = $this->extractCoordinates($request->address_url);
        if ($coords) {
            $store->latitude = $coords['latitude'];
            $store->longitude = $coords['longitude'];
        }

        // Prepare data for update
        $data = $request->only([
            'category_id', 'store_name', 'description',
            'phone_number', 'address', 'address_url', 'latitude', 'longitude', 'whatsapp', 'facebook',
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
