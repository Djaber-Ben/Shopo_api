<?php

namespace App\Http\Controllers\Api;

use Exception;
use Carbon\Carbon;
use App\Models\Store;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\OfflinePayment;
use Illuminate\Validation\Rule;
use App\Models\SubscriptionPlan;
use App\Models\StoreSubscription;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Mail\AdminNewSubscriptionNotification;

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
    // public function extractCoordinates($mapUrl)
    // {
    //     // Match patterns like @40.7128,-74.0060 or q=40.7128,-74.0060
    //     if (preg_match('/@(-?\d+\.\d{1,8}),(-?\d+\.\d{1,8})/', $mapUrl, $matches) ||
    //         preg_match('/q=(-?\d+\.\d{1,8}),(-?\d+\.\d{1,8})/', $mapUrl, $matches)) {
    //         $latitude = (float) $matches[1];
    //         $longitude = (float) $matches[2];

    //         // Validate coordinate ranges
    //         if ($latitude >= -90 && $latitude <= 90 && $longitude >= -180 && $longitude <= 180) {
    //             return [
    //                 'latitude' => $latitude,
    //                 'longitude' => $longitude,
    //             ];
    //         }
    //     }
    //     return null;
    // }

    // if (! function_exists('extractCoordinatesFromGoogleMapsUrl')) {
    /**
     * Extract latitude and longitude from any Google Maps URL.
     *
     * @param  string  $url
     * @return array|null  ['lat' => float, 'lng' => float] or null if not found
     */
    public function extractCoordinatesFromGoogleMapsUrl(string $url): ?array
{
        $cacheKey = 'coords_' . md5($url);

        // ✅ Check cache first
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        Log::info('🔗 Original URL:', ['url' => $url]);

        // 1️⃣ Expand shortened URLs
        $expandedUrl = self::expandUrl($url);
        Log::info('🌐 Expanded URL:', ['expanded_url' => $expandedUrl]);

        // 2️⃣ Try to extract coordinates from the URL
        $coords = self::extractFromUrl($expandedUrl);
        if ($coords) {
            Cache::put($cacheKey, $coords, now()->addDays(7));
            return $coords;
        }

        // 3️⃣ If not found, scrape the HTML page
        $coords = self::extractFromPage($expandedUrl);
        if ($coords) {
            Cache::put($cacheKey, $coords, now()->addDays(7));
            return $coords;
        }

        Log::warning('⚠️ Coordinates not found for URL:', ['checked_url' => $expandedUrl]);
        return null;
    }

    protected static function expandUrl(string $url): string
    {
        try {
            $headers = get_headers($url, 1);
            if (isset($headers['Location'])) {
                return is_array($headers['Location']) ? end($headers['Location']) : $headers['Location'];
            }
        } catch (\Exception $e) {
            Log::error('Failed to expand URL', ['error' => $e->getMessage()]);
        }
        return $url;
    }

    protected static function extractFromUrl(string $url): ?array
    {
        // Match patterns like @36.12345,3.56789
        if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $m)) {
            return ['lat' => $m[1], 'lng' => $m[2]];
        }

        // Match patterns like !3d36.12345!4d3.56789
        if (preg_match('/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/', $url, $m)) {
            return ['lat' => $m[1], 'lng' => $m[2]];
        }

        return null;
    }

    protected static function extractFromPage(string $url): ?array
    {
        try {
            $html = @file_get_contents($url);
            if (!$html) return null;

            // Look for coordinates in the page source
            if (preg_match('/"latitude":([\-0-9.]+),"longitude":([\-0-9.]+)/', $html, $m)) {
                return ['lat' => $m[1], 'lng' => $m[2]];
            }

            if (preg_match('/center=([\-0-9.]+),([\-0-9.]+)/', $html, $m)) {
                return ['lat' => $m[1], 'lng' => $m[2]];
            }
        } catch (\Exception $e) {
            Log::error('Failed to scrape page', ['error' => $e->getMessage()]);
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
            // subscription plan
            'subscription_plan_id' => [
                'required',
                Rule::exists('subscription_plans', 'id')->where('status', 'active'),
            ],

            // store subscription
            'payment_receipt_image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg',
                'max:2048',
                function ($attribute, $value, $fail) use ($request) {
                    $plan = SubscriptionPlan::find($request->subscription_plan_id);
                    if ($plan && !$plan->is_trial && !$request->hasFile('payment_receipt_image')) {
                        $fail('The payment receipt image is required for non-trial plans.');
                    }
                },
            ],

            'category_id' => 'required|exists:categories,id',
            'store_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:13',
            'address' => 'required|string|max:500',

            // ✅ Fixed double escaping inside regex rules
            'address_url' => [
                'required',
                'url',
                'max:500',
                'regex:/^https:\\/\\/(www\\.google\\.com\\/maps|maps\\.app\\.goo\\.gl|goo\\.gl\\/maps|apple\\.com\\/maps)/'
            ],

            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

            // ✅ Social link regexes (correctly escaped)
            'whatsapp' => [
                'nullable',
                'url',
                'max:255',
                'regex:/^https:\\/\\/(wa\\.me|api\\.whatsapp\\.com|web\\.whatsapp\\.com)/'
            ],
            'facebook' => [
                'nullable',
                'url',
                'max:255',
                'regex:/^https:\\/\\/(www\\.)?facebook\\.com/'
            ],
            'instagram' => [
                'nullable',
                'url',
                'max:255',
                'regex:/^https:\\/\\/(www\\.)?instagram\\.com/'
            ],
            'tiktok' => [
                'nullable',
                'url',
                'max:255',
                'regex:/^https:\\/\\/(www\\.)?tiktok\\.com/'
            ],
        ], [
            'subscription_plan_id.exists' => 'The selected subscription plan is invalid or not active.',
            'address_url.regex' => 'The address URL must be a valid Google Maps URL.',
            'whatsapp.regex' => 'The WhatsApp URL must be a valid WhatsApp link.',
            'facebook.regex' => 'The Facebook URL must be a valid Facebook link.',
            'instagram.regex' => 'The Instagram URL must be a valid Instagram link.',
            'tiktok.regex' => 'The TikTok URL must be a valid TikTok link.',
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
        try {
            $user->update(['user_type' => 'vendor']);
        } catch (\Exception $e) {
            Log::error('Failed to update user type: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update user type.'], 500);
        }
        
        // Extract lat/lng from the Google Maps link
        // Then save to the store
        $coords = $this->extractCoordinatesFromGoogleMapsUrl($request->address_url);
        $latitude = $coords['lat'] ?? null;
        $longitude = $coords['lng'] ?? null;

        try{
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
        } catch (Exception $e) {
            Log::error('Failed to create store: ' . $e->getMessage());
            // Rollback user type update
            $user->update(['user_type' => 'client']);
            return response()->json(['message' => 'Failed to create store.'], 500);
        }

        // Fetch subscription plan
        $subscriptionPlan = SubscriptionPlan::where('id', $request->subscription_plan_id)
            ->where('status', 'active')
            ->firstOrFail();

        // Handle subscription
        try {

            if ($subscriptionPlan->is_trial) {
                $start = Carbon::now();
                $end = $start->copy()->addDays($subscriptionPlan->duration_days);
                $store->update(['subscription_expires_at' => $end]);

                $subscription = StoreSubscription::create([
                    'store_id' => $store->id,
                    'subscription_plan_id' => $subscriptionPlan->id,
                    'start_date' => $start,
                    'end_date' => $end,
                    'status' => 'active',
                ]);

                return response()->json([
                    'message' => 'Store created successfully with trial subscription until ' . $end->toFormattedDateString(),
                    'store' => $store,
                    'subscription' => $subscription,
                ], 201);

            } else {
                $store->update(['status' => 'inactive']);

                // recive the offline payment receipt image from the store owners
                $payment_receipt_image = null;
                if ($request->hasFile('payment_receipt_image')) {
                    $payment_receipt_image = $request->file('payment_receipt_image')->store('images/storeSubscriptions/payment_receipt_image', 'public');
                }

                $subscription = StoreSubscription::create([
                    'store_id' => $store->id,
                    'subscription_plan_id' => $subscriptionPlan->id,
                    'payment_receipt_image' => $payment_receipt_image,
                    'status' => 'pending',
                ]);

                // send email to admin
                Mail::to('admin@mail.com')->send(new AdminNewSubscriptionNotification($store, $subscription));

                return response()->json([
                    'message' => 'Store and subscription created successfully, but the store is inactive and the subscription is pending. The admin will activate them once your payment receipt Image is approved.',
                    'store' => $store,
                    'subscription' => $subscription,
                ], 201);
            }
            
        } catch (\Exception $e) {
            $store->delete(); // Rollback store creation on failure
            return response()->json(['message' => 'Failed to create subscription.'], 500);
        }
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
            'category_id' => 'required|exists:categories,id',
            'store_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:13',
            'address' => 'required|string|max:500',
            'address_url' => [
                'required',
                'url',
                'max:500',
                'regex:/^https:\\/\\/(www\\.google\\.com\\/maps|maps\\.app\\.goo\\.gl|goo\\.gl\\/maps|apple\\.com\\/maps)/'
            ],

            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

            // ✅ Social link regexes (correctly escaped)
            'whatsapp' => [
                'nullable',
                'url',
                'max:255',
                'regex:/^https:\\/\\/(wa\\.me|api\\.whatsapp\\.com|web\\.whatsapp\\.com)/'
            ],
            'facebook' => [
                'nullable',
                'url',
                'max:255',
                'regex:/^https:\\/\\/(www\\.)?facebook\\.com/'
            ],
            'instagram' => [
                'nullable',
                'url',
                'max:255',
                'regex:/^https:\\/\\/(www\\.)?instagram\\.com/'
            ],
            'tiktok' => [
                'nullable',
                'url',
                'max:255',
                'regex:/^https:\\/\\/(www\\.)?tiktok\\.com/'
            ],
        ], [
            'address_url.regex' => 'The address URL must be a valid Google Maps URL.',
            'whatsapp.regex' => 'The WhatsApp URL must be a valid WhatsApp link.',
            'facebook.regex' => 'The Facebook URL must be a valid Facebook link.',
            'instagram.regex' => 'The Instagram URL must be a valid Instagram link.',
            'tiktok.regex' => 'The TikTok URL must be a valid TikTok link.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        // Extract lat/lng from the Google Maps link
        // Then save to the store
        $coords = $this->extractCoordinatesFromGoogleMapsUrl($request->address_url);
        if ($coords) {
            $store->latitude = $coords['lat'] ?? null;
            $store->longitude = $coords['lng'] ?? null;
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
