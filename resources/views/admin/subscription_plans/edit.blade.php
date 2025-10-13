@extends('admin.layouts.app')

@section('content')
<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid my-2">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Edit Subscription Plan</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('subscription-plans.index') }}" class="btn btn-dark">Back</a>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
</section>

<!-- Main Content -->
<section class="content">
    
    <div class="container-fluid">
        <form action="{{ route('subscription-plans.update', $plan->id) }}" method="POST">
            <div class="row">
                @csrf
                @method('PUT')
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-body">
                            <!-- Name -->
                            <div class="mb-3">
                                <label for="name">Plan Name</label>
                                <input 
                                    type="text" 
                                    name="name" 
                                    id="name" 
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $plan->name) }}" 
                                    placeholder="Enter plan name">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description">Description</label>
                                <textarea 
                                    name="description" 
                                    id="description" 
                                    class="form-control @error('description') is-invalid @enderror" 
                                    {{-- class="summernote"  --}}
                                    placeholder="Description"
                                >{{ old('description', $plan->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Price -->
                            <div class="mb-3">
                                <label for="price">Price</label>
                                <input 
                                    type="number" 
                                    name="price" 
                                    id="price" 
                                    class="form-control @error('price') is-invalid @enderror"
                                    value="{{ old('price', $plan->price) }}" 
                                    step="0.01"
                                    placeholder="Enter price">
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Compare Price -->
                            <div class="mb-3">
                                <label for="compare_price">Compare at Price</label>
                                <input 
                                    type="number" 
                                    name="compare_price" 
                                    id="compare_price" 
                                    class="form-control @error('compare_price') is-invalid @enderror"
                                    value="{{ old('compare_price', $plan->compare_price) }}" 
                                    step="0.01"
                                    placeholder="Enter compare price">
                                @error('compare_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-body">
                            <!-- Duration -->
                            <div class="mb-3">
                                <label for="duration">Duration</label>
                                <select 
                                    name="duration" 
                                    id="duration" 
                                    class="form-control @error('duration') is-invalid @enderror">
                                    <option value="daily" {{ old('duration', $plan->duration) === 'daily' ? 'selected' : '' }}>Daily</option>
                                    <option value="monthly" {{ old('duration', $plan->duration) === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="yearly" {{ old('duration', $plan->duration) === 'yearly' ? 'selected' : '' }}>Yearly</option>
                                </select>
                                @error('duration')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Duration Days -->
                            <div class="mb-3">
                                <label for="duration_days">Duration Days</label>
                                <input 
                                    type="number" 
                                    name="duration_days" 
                                    id="duration_days" 
                                    class="form-control @error('duration_days') is-invalid @enderror"
                                    value="{{ old('duration_days', $plan->duration_days) }}"
                                    placeholder="Enter duration in days">
                                @error('duration_days')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Is Trial -->
                            {{-- <div class="mb-3 form-check">
                                <input 
                                    type="checkbox" 
                                    name="is_trial" 
                                    id="is_trial" 
                                    class="form-check-input"
                                    {{ old('is_trial', $plan->is_trial) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_trial">Is Trial</label>
                            </div> --}}

                            <div class="mb-3">
                                <label for="is_trial">Is Trial</label>
                                <select name="is_trial" id="is_trial" class="form-control">
                                    <option value="0" {{ old('is_trial', $plan->is_trial) == '0' ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ old('is_trial', $plan->is_trial) == '1' ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>

                            <!-- Status -->
                            <div class="mb-3">
                                <label for="status">Status</label>
                                <select 
                                    name="status" 
                                    id="status" 
                                    class="form-control @error('status') is-invalid @enderror">
                                    <option value="active" {{ old('status', $plan->status) === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $plan->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="text-right">
                        <button type="submit" class="btn btn-success">Update Plan</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection
