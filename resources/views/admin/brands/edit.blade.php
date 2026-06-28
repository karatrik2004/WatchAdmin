@extends('layouts.admin')
@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h5>Edit Brand</h5>
        </div>
        <div class="card-body">
            @include('admin.brands._form', [
                'action' => route('admin.brands.update', $brand->id),
                'method' => 'PUT',
                'buttonText' => 'Update',
                'brand' => $brand
            ])
        </div>
    </div>
</div>
@endsection