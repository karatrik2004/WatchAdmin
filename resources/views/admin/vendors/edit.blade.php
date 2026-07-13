@extends('layouts.admin')

@section('title')
    Edit Vendor
@endsection

@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <h4>Update Vendor</h4>
                </div>
            </div>
        </div>

        <div class="card-body">
            {{ html()->modelForm($vendor, 'PATCH', route('admin.vendors.update', $vendor->id))
                ->class('validatedForm')
                ->id('vendor_form')
                ->open() }}

            {{ csrf_field() }}
            @include('admin.vendors._form')
            {{ html()->form()->close() }}
        </div>
    </div>
</div>
@endsection
