@extends('layouts.admin')
@section('title') Edit Notification Email @endsection
@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h4>Edit Notification Email</h4></div>
            <div class="card-body">
                @include('admin.notification_emails.form')
            </div>
        </div>
    </div>
</div>
@endsection
