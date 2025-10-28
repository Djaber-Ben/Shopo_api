@extends('admin.layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid my-2">
        <div class="row mb-2">
            <div class="container ">
                <h4>Site Info Pages</h4>
                <ul class="list-group mt-3">
                    <li class="list-group-item"><a href="{{ route('siteInfos.contact') }}">Contact Us</a></li>
                    <li class="list-group-item"><a href="{{ route('faqs.index') }}">FAQ</a></li>
                    <li class="list-group-item"><a href="{{ route('siteInfos.about') }}">About Us</a></li>
                </ul>
            </div>
        </div>
    </div>    
</section>
@endsection
