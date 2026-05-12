@extends('layouts.admin')
@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h5>Create Brand</h5>
        </div>
        <div class="card-body">
            @include('admin.brands._form', [
                'action' => route('admin.brands.store'),
                'method' => 'POST',
                'buttonText' => 'Create',
                'brand' => null
            ])
        </div>
    </div>
</div>
@endsection