<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OfflinePayment;
use Illuminate\Support\Facades\Validator;

class OfflinePaymentController extends Controller
{
    /**
     * Display the offline payment to the store owners via API.
     */
    public function show()
    {
        $payment = OfflinePayment::first();

        return response()->json([
            'payment' => $payment,
        ], 200);
    }

    /**
     * Show the form for editing an offline payment record.
     */
    public function index()
    {
        $payment = OfflinePayment::first();

        return view('admin.payment_info.list', compact('payment'));
    }

    /**
     * Show the form for editing an offline payment record.
     */
    public function edit($id)
    {
        $payment = OfflinePayment::findOrFail($id);

        return view('admin.payment_info.edit', compact('payment'));
    }

    /**
     * Update an offline payment record.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'family_name' => 'required|string|max:255',
            'ccp_number' => 'required|numeric|digits_between:9,12',
            'cle' => 'required|numeric|digits_between:1,3',
            'rip' => 'required|numeric|digits_between:20,24',
            'address' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Always update the first record (or create it if it doesn't exist)
        $payment = OfflinePayment::updateOrCreate(
            ['id' => 1], // condition (the single record)
            $request->only(['name', 'family_name', 'ccp_number', 'cle', 'rip', 'address'])
        );

        $request->session()->flash('success', 'تم تحديث تفاصيل حساب الدفع بنجاح.');
        return response()->json([
            'status' => true,
            'message' => 'Payment details updated successfully',
            'payment_id' => $payment->id
        ]);
    }
}
