@extends('layouts.admin-login')
@section('title') 404 Page Not Found @endsection
@section('content')
<div class="auth-wrapper">
  <div class="auth-content">
    <div class="row align-items-center text-center">
      <div class="col-md-12">
        <div class="error-content">
          <img src="{{url('backend/images/404.png')}}" alt="404 error">
          <p>Page not found. Please try using the admin sidebar navigation to locate the content you're looking for.</p>
          <p class="text-center">
            <a href="{{ route('admin.dashboard')}}" class="btn btn-dark text-white align-center">      
              <span>Back To Dashboard</span>
            </a>
          </p>
        </div>
      </div>
    </div>    
  </div>
</div>
@endsection