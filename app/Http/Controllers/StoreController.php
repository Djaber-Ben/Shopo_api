<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use App\Models\StoreSubscription;
use Illuminate\Support\Facades\Validator;

class StoreController extends Controller
{
     public function index(Request $request)
    {
        $keyword = $request->get('keyword');

        $stores = Store::with(['subscriptions.subscriptionPlan'])
            ->when($keyword, function ($query, $keyword) {
                $query->where('store_name', 'like', "%{$keyword}%")
                    ->orWhere('id', $keyword)
                    ->orWhereHas('subscriptions', function ($q) use ($keyword) {
                        $q->where('status', 'like', "%{$keyword}%")
                            ->orWhereHas('subscriptionPlan', function ($qp) use ($keyword) {
                                $qp->where('name', 'like', "%{$keyword}%");
                            });
                    });
            })
            ->latest()
            ->paginate(10);

        return view('admin.store.list', compact('stores'));
    }

    public function edit($storeId, Request $request){
        $store = Store::find($storeId);
        if(empty($store)){
            return redirect()->route('stores.index');
        }
        return view('admin.store.edit', compact('store'));
    }

    public function update( Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required',
            'subscription_status' => 'required|in:active,expired,cancelled,pending',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Find store
        $store = Store::findOrFail($id);

        // Update store status
        $store->status = $request->status;
        $store->save();

        // Get latest subscription (if any)
        $subscription = $store->subscriptions()->latest()->first();

        if ($subscription && $request->filled('subscription_status')) {
            $plan = $subscription->subscriptionPlan;

            $subscription->update([
                'status' => $request->subscription_status,
                'start_date' => now(),
                'end_date' => $plan ? now()->addDays($plan->duration_days) : null,
            ]);
            $store->update(['subscription_expires_at' => $subscription->end_date]);
        }

        $request->session()->flash('success', 'Store and subscription updated successfully.');

        return redirect()->back();
    }
}
