@extends('layouts.admin-login')
@section('title') Forgot Password @endsection
@section('inline-css')
@endsection
@section('content')
<div class="auth-wrapper admin_login">
	<div class="auth-content">
		<div class="card">
			<div class="row align-items-center text-center">
				<div class="col-md-12">
					<div class="card-body">
						<h2 class="h4 f-w-900 pb-2">{{ config('app.name') }}</h2>
						<h4 class="mb-3 f-w-400">Forgot Your Password?</h4>
						<p class="mb-4">We get it, stuff happens. Just enter your email address below
							and we'll send you a link to reset your password!</p>
						
						<form id="forgetpassform" class="user" method="POST" action="{{ route('password.email') }}">
							@csrf
							@csrf
							<div class="input-group mb-3">
								<span class="input-group-text"><i data-feather="mail"></i></span>
								<input type="email" class="form-control form-control-user required" id="adminInputEmail" aria-describedby="emailHelp"
									placeholder="Enter Email Address" name="email" value="{{ old('email') }}" autofocus>
								@if ($errors->has('email'))
								<label class="error" role="alert">{{ $errors->first('email') }}</label>
								@endif
							</div>
							<button class="btn btn-block btn-dark mb-4" type="submit">Reset Password</button>
						</form>
						<p class="mb-0 text-muted">
							Already have an account? <a href="{{route('admin.login')}}" class="f-w-400">Login</a>
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
@section('inline-js')
<script type="text/javascript">
	$(document).ready(function(){
		 $('#forgetpassform').validate();   			  
	});
</script>
@endsection