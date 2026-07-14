@extends('layouts.admin')

@section('title')
    Customer Details
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Customer Details</h4>
                <div>
                    <a href="{{ route('admin.customers.edit', $customer->id) }}" class="btn btn-info btn-sm">Edit</a>
                    <form action="{{ route('admin.customers.destroy', $customer->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this customer?');">Delete</button>
                    </form>
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary btn-sm">Back</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Buyer Name</h5>
                        <p>{{ $customer->buyer_name }}</p>

                        <h5>Buyer Email</h5>
                        <p>{{ $customer->buyer_email }}</p>

                        <h5>Address</h5>
                        <p>{{ $customer->buyer_address }}</p>
                    </div>
                    <div class="col-md-6">
                        <h5>City</h5>
                        <p>{{ $customer->buyer_city }}</p>

                        <h5>State</h5>
                        <p>{{ $customer->buyer_state }}</p>

                        <h5>Country</h5>
                        <p>{{ $customer->buyer_country }}</p>

                        <h5>Zipcode</h5>
                        <p>{{ $customer->buyer_zipcode }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
