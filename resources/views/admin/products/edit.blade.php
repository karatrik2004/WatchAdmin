@extends('layouts.admin')
@section('title') Edit Product @endsection
@section('inline-css')
@endsection
@section('content')

<div class="col-md-12">
	<div class="card">
		<div class="card-header">
			<h5>Update Product</h5>
		</div>
		<div class="card-body">
			{{ html()->modelForm($product,'PATCH',route('admin.products.update',$product->id))->class('validatedForm')->id('product_form')->open() }}
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
