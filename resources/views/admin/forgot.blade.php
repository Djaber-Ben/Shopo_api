<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Laravel Shop :: Administrative Panel</title>
		<!-- Google Font: Source Sans Pro -->
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
		<!-- Font Awesome -->
		<link rel="stylesheet" href="{{ asset('admin-assets/plugins/fontawesome-free/css/all.min.css') }}">
		<!-- Theme style -->
		<link rel="stylesheet" href="{{ asset('admin-assets/css/adminlte.min.css') }}">
		<link rel="stylesheet" href="{{ asset('admin-assets/css/custom.css') }}">
	</head>
	<body class="hold-transition login-page">
		<div class="login-box">
			<!-- /.login-logo -->
			{{-- @include('admin.message') --}}
			<div class="card card-outline card-warning">
			  	<div class="card-header text-center">
					<a  class="h3 on-hover text-warning">Forgot Password ?</a>
			  	</div>
			  	<div class="card-body">
					<p class="login-box-msg">enter your email to reset new password</p>
					{{-- @if ($errors->any())
						<div class="alert alert-danger">
							<ul>
								@foreach ($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
					@endif --}}
					<form action="{{ route('admin.forgotPassword') }}" method="post">
						@csrf
				  		<div class="input-group mb-3">
							<input type="email" value="{{ old('email') }}" name="email" id="email" class="form-control @error('email') is-invalid @enderror" placeholder="Email">
							<div class="input-group-append">
					  			<div class="input-group-text">
									<span class="fas fa-envelope"></span>
					  			</div>
							</div>
							@error('email')
								<p class="invalid-feedback" >{{ $message }}</p>
							@enderror
				  		</div>
				  		<div class="row">
							<div class="col-12">
					  			<button type="submit" class="btn btn-outline-warning btn-block">send Password reset link to email</button>
							</div>
				  		</div>
					</form>
		  			<p class="mb-1 mt-3">
				  		<a href="{{ route('admin.login') }}">Login</a>
					</p>					
			  	</div>
			  	<!-- /.card-body -->
			</div>
			<!-- /.card -->
		</div>
		<!-- ./wrapper -->
		<!-- jQuery -->
		<script src="{{ asset('admin-assets/plugins/jquery/jquery.min.js') }}"></script>
		<!-- Bootstrap 4 -->
		<script src="{{ asset('admin-assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
		<!-- AdminLTE App -->
		<script src="{{ asset('admin-assets/js/adminlte.min.js') }}"></script>
		<!-- AdminLTE for demo purposes -->
		{{-- <script src="{{ asset('admin-assets/js/demo.js') }}"></script> --}}
	</body>
</html>