@extends('layouts.admin')

@section('title')
    Edit Customer
@endsection

@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h4>Edit Customer</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.customers.update', $customer->id) }}" method="POST" class="validatedForm" id="customer_form">
                @csrf
                @method('PATCH')
                @include('admin.customers._form')
            </form>
        </div>
    </div>
</div>
@endsection
