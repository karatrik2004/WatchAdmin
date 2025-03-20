@extends('layouts.default')
@section('meta_title') Verify Email @endsection
@section('meta_desc') Verify Email @endsection
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
            <p>
                {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
            </p>
            @if (session('status') == 'verification-link-sent')
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </div>
            @endif
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf

                <div class="actionBtn">                    
                    <x-primary-button class="greenBtn">
                        {{ __('Resend Verification Email') }}
                    </x-primary-button>
                    <a href="{{ route('custom-logout') }}" class="greenBtn">Logout</a>
                </div>
            </form>           
        </div>
    </div>  

                </div>
            


        </div>
</div>







@endsection
@section('inline-js')

@endsection