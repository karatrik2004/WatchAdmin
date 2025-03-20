@extends('layouts.admin-login')
@section('meta_title') Reset Password @endsection
@section('meta_desc') Reset Password @endsection
@section('content')

<div class="auth-wrapper admin_login">
	<div class="auth-content">
		<div class="card">
			<div class="row align-items-center text-center">
				<div class="col-md-12">
					<div class="card-body">
						<h2 class="h4 f-w-900 pb-2"> {{ config('app.name') }}</h2>
						<h4 class="mb-3 f-w-400">Create New Password</h4>	
						{{session('status')}}					
						<x-auth-session-status class="mb-4 successMsg" :status="session('status')" />
						<form method="POST" id="passwordResetForm" action="{{ route('password.store') }}">
							@csrf
							<input type="hidden" name="token" value="{{ $request->route('token') }}">
							<div class="input-group mb-3">
								<span class="input-group-text"><i data-feather="mail"></i></span>
								<input id="email" type="email" class="form-control @error('email') is-invalid @enderror required" name="email" placeholder="Username/Email">
								@error('email')								
								<label class="error" role="alert">{{ $message }}</label>
								@enderror
							</div>
							<div class="input-group mb-4">
								<span class="input-group-text"><i data-feather="lock"></i></span>
								<input id="password" type="password" class="form-control @error('password') is-invalid @enderror required" name="password" placeholder="Enter a new password">
								@error('password')
								<label class="error" role="alert">{{ $message }}</label>
								@enderror
							</div>
							<div class="input-group mb-4">
								<span class="input-group-text"><i data-feather="lock"></i></span>
								<input id="password_confirmation" type="password"
									class="form-control @error('password_confirmation') is-invalid @enderror required" name="password_confirmation" placeholder="Confirm new password">								
								@error('password_confirmation')
								<span class="invalid-feedback" role="alert">
									<strong>{{ $message }}</strong>
								</span>
								@enderror
							</div>							
							<button class="btn btn-block btn-primary mb-4" type="submit">Reset Password</button>
						</form>						
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@endsection
@section('inline-js')
<script>
	$(document).ready(function () {
		$("#passwordResetForm").validate({			
			rules: {
				email: {
					required: true,
					email: true
				},
				password: {
					required: true,
					minlength: 8,
				},
				password_confirmation: {
					required: true,
					minlength: 8,
					equalTo: "#password"
				},

			},
		});
	});
</script>
@endsection