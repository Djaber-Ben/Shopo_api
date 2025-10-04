<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StoreController extends Controller
{
     public function index(Request $request){
        $stores = Store::latest();
        if(!empty($request->get('keyword'))){
            $stores = $stores->where('store_name', 'like', '%'.$request->get('keyword').'%');
        }
        
        $stores = $stores->paginate(10);
        // $data['store'] = $store;
        return view('admin.store.list', compact('stores'));
    }

    public function edit($storeId, Request $request){
        $store = Store::find($storeId);
        if(empty($store)){
            return redirect()->route('stores.index');
        }
        return view('admin.store.edit', compact('store'));
    }

    public function update($storeId, Request $request)
    {
        $store = Store::find($storeId);

        if (empty($store)) {
            $request->session()->flash('error', 'Store Not Found');
            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => 'Store Not Found',
            ]);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $store->status = $request->status;

        $store->save();

        $request->session()->flash('success', 'Store updated successfully');

        return response()->json([
            'status' => true,
            'message' => 'Store updated successfully'
        ]);
    }
}
