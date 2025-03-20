@extends('layouts.admin-login')
@section('title') Admin Login @endsection
@section('inline-css')
@endsection
@section('content')

<div class="auth-wrapper admin_login">
  <div class="auth-content">
    <div class="card">
      <div class="row align-items-center text-center">
        <div class="col-md-12">
          <div class="card-body">            
            <h2 class="h4 f-w-900 pb-2"> {{ config('app.name') }}</h2>
            <h4 class="mb-3 f-w-400">Admin Login</h4>
            @php
            $formUrl = route('admin.loginprocess');
            if(!empty($return_url)){
              $formUrl = route('admin.loginprocess',['return_url='.$return_url]);
            }
            @endphp
            <form id="loginform" class="user" method="POST" action="{{ $formUrl }}">
              @csrf
              <div class="input-group mb-3">
                <span class="input-group-text"><i data-feather="mail"></i></span>                
                <input type="email" class="form-control required" id="adminInputEmail" aria-describedby="emailHelp" placeholder="Enter Email Address" name="email" @if(Cookie::has('frontemail')) value="{{Cookie::get('frontemail')}}" @else value="{{ old('email') }}" @endif>
                @if ($errors->has('email'))
                <label class="error" role="alert">{{ $errors->first('email') }}</label>
                @endif
              </div>
              <div class="input-group mb-4">
                <span class="input-group-text"><i data-feather="lock"></i></span>
                <input type="password" class="form-control required" id="adminInputPassword" placeholder="Password" name="password" 
                  @if(Cookie::has('frontpassword')) 
                    value="{{Cookie::get('frontpassword')}}"
                  @else
                    value="{{ old('password') }}" 
                  @endif
                >
                @if ($errors->has('password'))
                <span class="error" role="alert">{{ $errors->first('password') }}</span>
                @endif
              </div>
              <div class="form-group text-left mt-2">
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" 
                    @if(Cookie::has('frontemail')) checked @endif 
                    name="remember" {{ old('remember') ? 'checked' : '' }} id="customCheck">
                  <label class="form-check-label" for="customCheck">Remember Me</label>
                </div>
              </div>
              <button class="btn btn-block btn-dark mb-4" type="submit">Login</button>
            </form>
            
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
		$('#loginform').validate();				  
	});
</script>
@endsection