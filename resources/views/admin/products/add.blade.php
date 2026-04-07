@extends('layouts.admin')
@section('title') Add Product @endsection
@section('inline-css')
@endsection
@section('content')

<div class="col-md-12">
	<div class="card">
		<div class="card-header">
			<h5>Create Product</h5>
		</div>
		<div class="card-body">
			{{ html()->form('POST', route('admin.products.store'))->class('validatedForm')->id('product_form')->open() }}
				{{ csrf_field() }}
				@include('includes.admin.product.form')
			{{ html()->form()->close() }}
		</div>
	</div>
</div>

@endsection
@section('inline-js')
<script>
    jQuery('.validatedForm').validate();
</script>
@endsection
