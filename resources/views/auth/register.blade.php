@extends('layouts.default')
@section('meta_title') Register @endsection
@section('meta_desc') Register @endsection
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
			<form method="POST" id="registerForm" action="{{ route('register') }}">
				@csrf
				<h3 class="font_bold mb-20">{!! $global['global_registration_heading'];  !!}</h3>
				<div class="formWrap">
					<div class="control">
						<label for="Name">First Name</label>
						<input id="firstname" type="text"
							class="inputText @error('firstname') is-invalid @enderror required" name="firstname"
							value="{{ old('firstname') }}" placeholder="First Name">
						@error('firstname')
						<span class="invalid-feedback" role="alert">
							<strong>{{ $message }}</strong>
						</span>
						@enderror
					</div>

					<div class="control">
						<label for="Name">Last Name</label>
						<input id="lastname" type="text"
							class="inputText @error('lastname') is-invalid @enderror required" name="lastname"
							value="{{ old('lastname') }}" placeholder="Last Name">
						@error('lastname')
						<span class="invalid-feedback" role="alert">
							<strong>{{ $message }}</strong>
						</span>
						@enderror
					</div>
					<div class="control">
						<label for="Email">Username / Email</label>
						<input id="email" type="email" class="inputText @error('email') is-invalid @enderror required"
							name="email" value="{{ old('email') }}" placeholder="Username/ Email address">
						@error('email')
						<span class="invalid-feedback" role="alert">
							<strong>{{ $message }}</strong>
						</span>
						@enderror
					</div>
					<div class="control">
						<label for="birthday">Date of Birth</label>
						<input id="birthday" type="text"
							class="inputText @error('birthday') is-invalid @enderror required" name="birthday"
							value="{{ old('birthday') }}" placeholder="07/04/1988">
						@error('birthday')
						<span class="invalid-feedback" role="alert">
							<strong>{{ $message }}</strong>
						</span>
						@enderror
					</div>
					<div class="control">
						<label for="password">Password</label>
						<input id="password" type="password"
							class="inputText @error('password') is-invalid @enderror required" id="password" name="password"
							placeholder="********">
							<span toggle="#password" class="fa fa-fw fa-eye field-icon toggle-password"></span>
						@error('password')
						<span class="invalid-feedback" role="alert">
							<strong>{{ $message }}</strong>
						</span>
						@enderror
					</div>
					<div class="control">
						<label for="password">Confirm Password</label>
						<input id="password_confirmation" type="password" class="inputText required"
							name="password_confirmation" placeholder="********">
							<span toggle="#password_confirmation" class="fa fa-fw fa-eye field-icon toggle-password"></span>
					</div>
				</div>
				<div class="termsBlock">
					<input name="is_term" value="1" class="required" type="checkbox" />
					
				</div>
				<div class="actionBtn">
					<button type="submit" class="greenBtn">Sign Up</button>
					@if (Route::has('password.request'))
					<a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
						href="{{ route('login') }}">{{ __('Already registered?') }}</a>
					@endif
				</div>
			</form>
		</div>
	</div>
</div>

</div>



</div>

@endsection
@section('inline-js')
<style>
.field-icon {
    margin-left: -25px;
    position: relative;
    z-index: 2;
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
		$("#registerForm").validate({
			rules: {
				firstname: "required",
				lastname: "required",
				email: {
					required: true,
					email: true
				},
				birthday: "required",
				password: {
					required: true,
					minlength: 8,
				},
				password_confirmation: {
					required: true,
					minlength: 8,
					equalTo: "#password"
				}
			}
		});
	});
</script>
@endsection