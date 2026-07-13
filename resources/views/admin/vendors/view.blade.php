@extends('layouts.admin')
@section('title') Vendor Details @endsection
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Vendor Details</h4>
                <div>
                    <a href="{{ route('admin.vendors.edit', $vendor->id) }}" class="btn btn-info btn-sm">Edit</a>
                    <form action="{{ route('admin.vendors.destroy', $vendor->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this vendor?');">Delete</button>
                    </form>
                    <a href="{{ route('admin.vendors.index') }}" class="btn btn-secondary btn-sm">Back</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Name</h5>
                        <p>
                            @if(optional($vendor)->vendor_type === 'company')
                                {{ $vendor->company_name }}
                            @else
                                {{ $vendor->first_name }} {{ $vendor->last_name }}
                            @endif
                        </p>

                        <h5>Email</h5>
                        <p>{{ $vendor->email }}</p>

                        <h5>Contact Number</h5>
                        <p>{{ $vendor->contact_number }}</p>

                        @if(optional($vendor)->vendor_type === 'company')
                            <h5>ABN</h5>
                            <p>{{ $vendor->abn }}</p>

                            <h5>Second-hand Dealer Licence Number</h5>
                            <p>{{ $vendor->dealer_licence_number }}</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h5>Address</h5>
                        <p>{{ $vendor->address }}</p>

                        <h5>City / State / Country / Zip</h5>
                        <p>{{ $vendor->city }} / {{ $vendor->state }} / {{ $vendor->country }} / {{ $vendor->zipcode }}</p>

                        @if(optional($vendor)->vendor_type === 'company')
                            <h5>Director Name</h5>
                            <p>{{ $vendor->director_name }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
