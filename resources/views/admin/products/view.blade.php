@extends('layouts.admin')
@section('title')
    View Product
@endsection
@section('inline-css')
@endsection
@section('content')

<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h5>View Product Detail</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-10">
                    <div class="row">
                        <div class="col-sm-3">
                            <label class="form-label fw-bold">Name:</label>
                        </div>
                        <div class="col-sm-3">
                            <p>{{ $product->name }}</p>
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label fw-bold">Brand:</label>
                        </div>
                        <div class="col-sm-3">
                            <p>{{ $product->brand }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <label class="form-label fw-bold">Model Number:</label>
                        </div>
                        <div class="col-sm-3">
                            <p>{{ $product->model_number }}</p>
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label fw-bold">Price:</label>
                        </div>
                        <div class="col-sm-3">
                            <p>A$ {{ number_format($product->price, 2) }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <label class="form-label fw-bold">Description:</label>
                        </div>
                        <div class="col-sm-9">
                            <p>{{ $product->description }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <label class="form-label fw-bold">Status (Active/Deactive):</label>
                        </div>
                        <div class="col-sm-3">
                            @php $status = Config::get('constants.STATUS'); @endphp
                            <p>{!! $status[$product->status] !!}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <label class="form-label fw-bold">Created:</label>
                        </div>
                        <div class="col-sm-3">
                            <p>{{ date('D, M d, Y h:i:s a', strtotime($product->created_at)) }}</p>
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label fw-bold">Updated:</label>
                        </div>
                        <div class="col-sm-3">
                            <p>{{ date('D, M d, Y h:i:s a', strtotime($product->updated_at)) }}</p>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-sm-4">
                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-success btn-user btn-md">Edit</a>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-dark btn-md">Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
