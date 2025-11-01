@extends('admin.layouts.app')

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">                    
    <div class="container-fluid my-2">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>تعديل المتجر</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('stores.index') }}" class="btn btn-primary" style="float: left !important">رجوع</a>
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->
</section>
<!-- Main content -->
<section class="content">
    <!-- Default box -->
    <div class="container-fluid">
        <div class="row">
            {{-- <form action="{{ route('stores.update', $store->id) }}" 
                method="post" 
                enctype="multipart/form-data" 
                id="storeForm">
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-body">                                
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="status">Status</label>    
                                    <select name="status" id="status" class="form-control">
                                        <option value="active" {{ ($store->status == 'active') ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ ($store->status == 'inactive') ? 'selected' : '' }}>Block</option>
                                    </select>
                                    <p></p>    
                                </div>
                            </div>
                        </div>
                    </div>                            
                </div>
                <div class="pb-5 pt-3">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('stores.index') }}" class="btn btn-outline-dark ml-3">Cancel</a>
                </div>
            </form> --}}
            <div class="col-md-8">
                @include('admin.message')
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="card">
                    <div class="card-header pt-3">
                        <div class="row invoice-info">
                            <div class="col-sm-12 invoice-col">
                                <h1 class="h5 mb-3">معلومات المتجر</h1>
                                <p>
                                    الإسم: <strong>{{ $store->store_name}}</strong><br>
                                    رقم الهاتف: <strong>{{ $store->phone_number }}</strong><br>
                                    العنوان: <strong>{{ $store->address_url }}</strong><br>
                                    تاريخ الإنشاء:
                                    <strong>
                                        @if (!empty($store->created_at))
                                        {{ \Carbon\Carbon::parse($store->created_at)->format('Y - M -  d') }}
                                        @else
                                        N/A
                                        @endif
                                    </strong>
                                    <br>
                                    حالة المتجر: <strong> {{ ($store->status) }}</strong>
                                </p>
                            </div>
                            <div class="col-md-9">
                                <div class="mb-3">
                                    <p></p>    
                                </div>
                            </div>
                            <br>
                        </div>
                    </div>                           
                </div>
                <div class="card">
                    <div class="card-header pt-3">
                        <div class="row invoice-info">
                            <div class="col-sm-9 invoice-col">
                                {{-- <div class="card"> --}}
                                    {{-- <div class="card-body"> --}}
                                        <h1 class="h5 mb-3">إشتراك المتجر</h1>
                                        <b>رقم إشتراك المتجر:</b> {{ $store->subscriptions->first()->id ?? 'No Subscription' }}<br>
                                        <b>خطة الإشتراك الخاصة بالمتجر:</b> {{ $store->subscriptions->first()->subscriptionPlan->name ?? 'No plan' }}<br>
                                        <b>سعر خطة الإشتراك:</b> {{ $store->subscriptions->first()->subscriptionPlan->price ?? 'No plan' }}<br>
                                        <b>الحالة الخاصة بخطة الإشتراك:</b> {{ $store->subscriptions->first()->subscriptionPlan->status ?? 'No plan' }}<br>
                                        <b>صورة الوصل الخاصة بتسديد مستحقات إشتراك المتجر:</b>
                                            @if(!empty($store->subscriptions->first()->payment_receipt_image))
                                                <div>
                                                    <img width="250" src="{{ asset('storage/'.$store->subscriptions->payment_receipt_image) }}" alt="">
                                                </div>
                                            @endif
                                        <br>
                                        <b>حالة إشتراك المتجر:</b>
                                            @if(!empty($store->subscriptions->first()->status))
                                                @if ($store->subscriptions->first()->status == 'expired' ?? 'No Subscription')
                                                    <span class="text-muted"><strong> منتهية الصلاحية </strong></span>
                                                @elseif($store->subscriptions->first()->status == 'cancelled' ?? 'No Subscription')
                                                    <span class="text-info"><strong> ملغات </strong></span>
                                                @elseif($store->subscriptions->first()->status == 'pending' ?? 'No Subscription')
                                                    <span class="text-danger"><strong> قيد الإنتظار </strong></span>
                                                @elseif($store->subscriptions->first()->status == 'active' ?? 'No Subscription')
                                                    <span class="text-success"><strong> نشطة </strong></span>
                                                @endif
                                            @endif
                                        <br>
                                    {{-- </div> --}}
                                {{-- </div> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <form action="{{ route('stores.update', $store->id) }}" method="post" id="storeForm">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="mb-3">
                                <h2 class="h4 mb-3" for="status">حالة المتجر</h2>   
                                <select name="status" id="status" class="form-control">
                                    <option value="active" {{ ($store->status == 'active') ? 'selected' : '' }}>نشط</option>
                                    <option value="inactive" {{ ($store->status == 'inactive') ? 'selected' : '' }}>موقف</option>
                                </select>
                                <p></p>    
                            </div>
                            <h2 class="h4 mb-3">حالة إشتراك المتجر</h2>
                            <div class="mb-3">
                                <select name="subscription_status" class="form-control">
                                    <option value="">حدد حالة إشتراك المتجر</option>
                                    @if(!empty($store->subscriptions->first()))
                                        <option value="active" {{ $store->subscriptions->first()->status == 'active' ? 'selected' : '' }}>نشطة</option>
                                        <option value="pending" {{ $store->subscriptions->first()->status == 'pending' ? 'selected' : '' }}>قيد الإنتظار </option>
                                        <option value="cancelled" {{ $store->subscriptions->first()->status == 'cancelled' ? 'selected' : '' }}>ملغات</option>
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">تحديث</button>
                                <a href="{{ route('stores.index') }}" class="btn btn-outline-dark ml-3">إلغاء</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /.card -->
</section>
<!-- /.content -->
@endsection

@section('customJs')
<script>


</script>
@endsection
