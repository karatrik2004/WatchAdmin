@extends('layouts.default')
@section('meta_title') Forgot Password @endsection
@section('meta_desc') Forgot Password @endsection
@section('content')
@php      
	$header      =     getSettings('header'); 
	$global      =     getSettings('global'); 
	$footer      =     getSettings('footer'); 
@endphp 



<div class="regesterWrap container-fluid">
		<div class="row">		
			<div class="col-lg-6">
						@include('includes.front.left-sidebar')
			</div>
			<div class="col-lg-6">
				<div class="regesterCenter">
					<div class="innerBlock">
						<x-auth-session-status class="mb-4 successMsg" :status="session('status')" />
						<form method="POST" id="forgotForm" action="{{ route('password.email') }}">
							@csrf
							<h2 class="font_bold">Forgot Password</h2>
							<br/>
							<p>{{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}</p>
							<div class="formWrap">
								<div class="control">
									<label for="Email">Username / Email</label>
									<input id="email" type="email" class="inputText @error('email') is-invalid @enderror required" name="email" placeholder="Username/ Email address">
									@error('email')
									<span class="invalid-feedback" role="alert">
										<strong>{{ $message }}</strong>
									</span>
									@enderror
								</div>
							</div>
							<div class="actionBtn">
								<button type="submit" class="greenBtn"> {{ __('Email Password Reset Link') }}</button>
							</div>
						</form>
						<p class="signup-info mt-5">Don't have an account yet? <a href="{{url('/')}}" class="signup_link">Sign up</a></p>
					</div>
			</div>	
	</div>
</div>
</div>

@endsection
@section('inline-js')

<style>
.signup-info {
	background: rgb(12 20 35 / 87%);
	padding: 7px;
	color: #fff;
	text-align: center;
}
.signup_link {
	color: #53AE12;
	text-decoration: underline;
	font-weight: bold;
}
.signup_link:hover {
	color: #fff;
}
</style>
<script>
	$(document).ready(function () {
		$("#forgotForm").validate({			
			rules: {
				email: {
					required: true,
					email: true
				}				
			},
		});
	});
</script>
@endsection