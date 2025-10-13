<?php

namespace App\Http\Controllers\Api;

use App\Models\Store;
use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SubscriptionPlanApiController extends Controller
{
    /**
     * Display all active subscription plans (API).
     */
    public function index()
    {
        $plans = SubscriptionPlan::active()->get();
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
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $store = Store::where('vendor_id', Auth::id())->findOrFail($request->store_id);
        $plan = SubscriptionPlan::active()->findOrFail($request->subscription_plan_id);

        // Check if store has an active subscription
        $activeSubscription = StoreSubscription::active()
            ->where('store_id', $store->id)
            ->first();

        if ($activeSubscription) {
            return response()->json(['message' => 'Store already has an active subscription'], 400);
        }

        $startDate = now();
        $endDate = now()->addDays($plan->duration_days);

        $subscription = StoreSubscription::create([
            'store_id' => $store->id,
            'subscription_plan_id' => $plan->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'active',
        ]);

        return response()->json(['subscription' => $subscription], 201);
    }
}
