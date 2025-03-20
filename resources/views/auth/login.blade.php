@extends('layouts.login')
@section('meta_title') Login @endsection
@section('meta_desc') Login @endsection
@section('content')


<div class="regesterWrap container-fluid">
	<div class="row">
		
	
		<div class="col-lg-6">
		<div class="regesterCenter">
		<div class="innerBlock loginpage">
			<!-- Session Status -->
			<x-auth-session-status class="mb-4 successMsg" :status="session('status')" />
			<x-input-error :messages="$errors->get('email')" class="alert alert-danger mt-2" />
			<form method="POST" id="loginForm" action="{{ route('login') }}">
				@csrf
				<h2 class="font_bold">Login</h2>
				<div class="formWrap">
					<div class="control">
						<label for="Email">Username / Email</label>
						<x-text-input id="email" class="inputText" type="email" name="email" :value="old('email')"	placeholder="Username/ Email address" />

					</div>

					<div class="control">
						<label for="password">Password</label>
						<x-text-input id="password" class="inputText mt-1 w-full" type="password" name="password" placeholder="********" />
						<span toggle="#password" class="fa fa-eye field-icon toggle-password"></span>	
						<x-input-error :messages="$errors->get('password')" class="mt-2" />
						
					</div>
					
				</div>
				<div class="actionBtn">
					<button type="submit" class="greenBtn">Login</button>
					@if (Route::has('password.request'))
					<a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
						href="{{ route('password.request') }}">
						{{ __('Forgot your password?') }}
					</a>
					@endif
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
  /** password show /hide */
  $(document).on("click",".toggle-password",function() {	
      $(this).toggleClass("fa-eye fa-eye-slash");
      var input = $($(this).attr("toggle"));
      if (input.attr("type") == "password") {
              input.attr("type", "text");
      } else {
              input.attr("type", "password");
      }
  });
</script>

<script>
	$(document).ready(function () {
		$("#loginForm").validate({
			// Specify validation rules
			rules: {
				email: {
					required: true,
					email: true
				},
				password: {
					required: true,
				},

			},
		});
	});

</script>
@endsection