@extends('admin.layouts.app')

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">                    
    <div class="container-fluid my-2">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Edit Store</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('stores.index') }}" class="btn btn-primary">Back</a>
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->
</section>
<!-- Main content -->
<section class="content">
    <!-- Default box -->
    <div class="container-fluid">
        <form action="{{ route('stores.update', $store->id) }}" 
              method="post" 
              enctype="multipart/form-data" 
              id="storeForm">
            @csrf
             @method('PUT')
            <div class="card">
                <div class="card-body">                                
                    <div class="row">
                        {{-- <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name">Name</label>
                                <input type="text" name="name" id="name" value="{{ $store->name }}" class="form-control" placeholder="Name">
                                <p></p>    
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="image">Image</label>
                                <div id="imageUpload" class="dropzone"></div>
                                <input type="hidden" name="image" id="image"> {{-- store filename --}}
                                {{-- </div>
                                @if(!empty($store->image))
                                <div>
                                    <img width="250" src="{{ asset('storage/'.$store->image) }}" alt="">
                                </div>
                                @endif
                            </div>                                 --}}
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
                        {{-- <div class="col-md-6">
                            <div class="mb-3">
                                <label for="show">Show on Home</label>    
                                <select name="show" id="show" class="form-control">
                                    <option {{ ($store->show == '1') ? 'selected' : '' }} value="1">Yes</option>
                                    <option {{ ($store->show == '0') ? 'selected' : '' }} value="0">No</option>
                                </select>
                            </div>
                        </div>                                      --}}
                    </div>
                </div>                            
            </div>
            <div class="pb-5 pt-3">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('stores.index') }}" class="btn btn-outline-dark ml-3">Cancel</a>
            </div>
        </form>
    </div>
    <!-- /.card -->
</section>
<!-- /.content -->
@endsection

@section('customJs')
<script>

// Handle form submit
$("#storeForm").submit(function(event){
    event.preventDefault();
    var formData = $(this).serialize();

    $.ajax({
        url: "{{ route('stores.update', $store->id) }}",
        type: 'Post',
        data: formData,
        dataType: 'json',
        success: function(response){
            if(response.status === true){
                window.location.href = "{{ route('stores.index') }}";
            } else {
                var errors = response.errors;
                if(errors.name){
                    $('#status').addClass('is-invalid')
                        .siblings('p')
                        .addClass('invalid-feedback').html(errors.status);
                } else {
                    $('#status').removeClass('is-invalid')
                        .siblings('p')
                        .removeClass('invalid-feedback').html("");
                }
            }
        },
        error: function(jqXHR){
            console.log("Error:", jqXHR.responseText);
        }
    });
});
</script>
@endsection
