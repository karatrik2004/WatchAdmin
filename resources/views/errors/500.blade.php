@extends('layouts.admin-login')
@section('title') 500 Internal Server Error @endsection
@section('content')
<div class="auth-wrapper">
  <div class="auth-content">
    <div class="row align-items-center text-center">
      <div class="col-md-12">
        <div class="error-content">
          <img src="{{url('backend/images/500.png')}}" alt="500 error">
          <p> We are currently addressing issues with the {{ config('app.name') }}.
           Thank you for your patience as we work on resolving them.</p>
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