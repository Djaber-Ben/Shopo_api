<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Store;
use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use App\Models\StoreSubscription;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Mail\AdminNewSubscriptionNotification;

class SubscriptionPlanApiController extends Controller
{
    /**
     * Display all active subscription plans (API).
     */
    public function index()
    {
        $user = Auth::user();

        // Get the vendor's store
        $store = $user->store ?? null;
 
        if (!$store) {
            return response()->json([
                'message' => 'You must have a store to view subscription plans.'
            ], 403);
        }

        // Start with all active plans
        $plansQuery = SubscriptionPlan::where('status', 'active');

        // if ($store) {
        //     // Get all trial plans that this store already used
        //     $usedTrialPlanIds = StoreSubscription::where('store_id', $store->id)
        //         ->whereHas('subscriptionPlan', function ($query) {
        //             $query->where('is_trial', true);
        //         })
        //         ->pluck('subscription_plan_id')
        //         ->toArray();

        //     // Exclude them from available plans
        //     if (!empty($usedTrialPlanIds)) {
        //         $plansQuery->whereNotIn('id', $usedTrialPlanIds);
        //     }
        // }

        $hasUsedTrial = StoreSubscription::where('store_id', $store->id)
            ->whereHas('subscriptionPlan', fn($q) => $q->where('is_trial', true))
            ->exists();

        // If used a trial, hide all trial plans
        if ($hasUsedTrial) {
            $plansQuery->where('is_trial', false);
        }

        // Load plans
        $plans = $plansQuery->get();

        return response()->json([
            'plans' => $plans
        ], 200);
    }

    /**
     * Display a specific subscription plan (API).
     */
    public function show(SubscriptionPlan $subscription_plan)
    {
        return response()->json([
            'plan' => $subscription_plan
        ], 200);
    }

    /**
     * Subscribe a store to a plan (API).
     */
    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|exists:stores,id',
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'payment_receipt_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $store = Store::where('vendor_id', Auth::id())->findOrFail($request->store_id);
        $plan = SubscriptionPlan::active()->findOrFail($request->subscription_plan_id);

        // Optional: Prevent duplicate pending subscriptions
        $existing = StoreSubscription::where('store_id', $store->id)
            ->whereIn('status', ['pending'])
            ->first();
        
        if ($existing) {
            return response()->json([
                'message' => 'You already have an active or pending subscription for this store.',
            ], 409);
        }

        // recive and Upload the offline payment receipt image from the store owners
        $payment_receipt_image = null;
        if ($request->hasFile('payment_receipt_image')) {
            $payment_receipt_image = $request->file('payment_receipt_image')
                ->store('images/storeSubscriptions/payment_receipt_image', 'public');
        }
                
        $subscription = StoreSubscription::create([
            'store_id' => $store->id,
            'subscription_plan_id' => $plan->id,
            'payment_receipt_image' => $payment_receipt_image,
            'status' => 'pending',
        ]);

        // Send email to admin
        $admin = User::where('user_type', 'admin')->first();
        if ($admin) {
            Mail::to($admin->email)->send(new AdminNewSubscriptionNotification($store, $subscription));
        }

        return response()->json([
            'message' => 'Store Subscription created successfully, It will be activated when the admin approves your Payment Receipt Image. ',
            'subscription' => $subscription,
        ], 201);
    }
}
