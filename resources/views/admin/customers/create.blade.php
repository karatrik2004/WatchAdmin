@extends('layouts.admin')

@section('title')
    Add Customer
@endsection

@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h4>Add Customer</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.customers.store') }}" method="POST" class="validatedForm" id="customer_form">
                @csrf
                @include('admin.customers._form')
            </form>
        </div>
    </div>
</div>
@endsection
