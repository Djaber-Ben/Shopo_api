@extends('admin.layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">					
        <div class="container-fluid my-2">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Subscription Plans</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('subscription-plans.create') }}" class="btn btn-primary">New Plan</a>
                </div>
            </div>
        </div>
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        <!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        <!-- Default box -->
        <div class="container-fluid">
            @include('admin.message')
            <div class="card">
                <form action="" method="get">
                    <div class="card-header">
                        <div class="card-tools">
                            <div class="input-group input-group" style="width: 250px;">
                                <input value="{{ Request::get('keyword') }}" type="text" name="keyword" class="form-control float-right" placeholder="Search">
                                
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-default">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <div class="card-titel" style="margin-left: 5px">
                                    <button class="btn btn-dark" type="button" onclick="window.location.href='{{ route('subscription-plans.index') }}'">Reset</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="card-body table-responsive p-0">								
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Duration</th>
                                <th>Duration Days</th>
                                <th>Is Trial</th>
                                <th>Status</th>
                                <th>Store Subscriptions</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($plans->isNotEmpty())
                                @foreach ($plans as $plan)
                                    <tr>
                                        <td>{{ $plan->name }}</td>
                                        <td>{{ number_format($plan->price, 2) }}</td>
                                        <td>{{ ucfirst($plan->duration) }}</td>
                                        <td>{{ $plan->duration_days }}</td>
                                        <td>{{ $plan->is_trial ? 'Yes' : 'No' }}</td>
                                        <td>{{ ucfirst($plan->status) }}</td>
                                        <td>{{ $plan->store_subscriptions_count }}</td>
                                        <td>
                                            <a href="{{ route('subscription-plans.edit', $plan->id) }}">
                                                 <svg class="filament-link-icon w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                                                </svg>
                                            </a>
                                            <form action="{{ route('subscription-plans.delete', $plan->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm p-1" style="border: none;" onclick="return confirm('Are you sure?')">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" 
                                                        viewBox="0 0 20 20" aria-hidden="true">
                                                        <path fill-rule="evenodd" 
                                                            d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" 
                                                            clip-rule="evenodd">
                                                        </path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach                                
                            @else
                                <tr>
                                    <td colspan="5">Records Not Found</td>
                                </tr>
                            @endif                                                                                  
                        </tbody>
                    </table>										
                </div>
                <div class="card-footer clearfix">
                    {{-- {{ $plans->links() }} --}}
                    {{-- <ul class="pagination pagination m-0 float-right">
                      <li class="page-item"><a class="page-link" href="#">«</a></li>
                      <li class="page-item"><a class="page-link" href="#">1</a></li>
                      <li class="page-item"><a class="page-link" href="#">2</a></li>
                      <li class="page-item"><a class="page-link" href="#">3</a></li>
                      <li class="page-item"><a class="page-link" href="#">»</a></li>
                    </ul> --}}
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
