<div class="form-group row">
	<div class="col-sm-4">
		<label class="form-label">Name <span class="required">*</span></label>
		{{ html()->text('name')->class('form-control form-control-user required') }}
		@if ($errors->has('name'))
		<span class="error" role="alert">{{ $errors->first('name') }}</span>
		@endif
	</div>
	<div class="col-sm-4">
		<label class="form-label">Brand <span class="required">*</span></label>
		{{ html()->text('brand')->class('form-control form-control-user required') }}
		@if ($errors->has('brand'))
		<span class="error" role="alert">{{ $errors->first('brand') }}</span>
		@endif
	</div>
</div>
<div class="form-group row">
	<div class="col-sm-4">
		<label class="form-label">Model Number <span class="required">*</span></label>
		{{ html()->text('model_number')->class('form-control form-control-user required') }}
		@if ($errors->has('model_number'))
		<span class="error" role="alert">{{ $errors->first('model_number') }}</span>
		@endif
	</div>
	<div class="col-sm-4">
		<label class="form-label">Price (A$) <span class="required">*</span></label>
		{{ html()->text('price')->class('form-control form-control-user float-price required number') }}
		@if ($errors->has('price'))
		<span class="error" role="alert">{{ $errors->first('price') }}</span>
		@endif
	</div>
</div>
<div class="form-group row">
	<div class="col-sm-8">
		<label class="form-label">Description</label>
		{{ html()->textarea('description')->class('form-control form-control-user')->rows(4) }}
		@if ($errors->has('description'))
		<span class="error" role="alert">{{ $errors->first('description') }}</span>
		@endif
	</div>
</div>
<div class="form-group row">
	<div class="col-sm-4">
		<label class="form-label">Status (Active/Deactive) <span class="required">*</span></label>
		@php $status = Config::get('constants.STATUS'); @endphp
		{{ html()->select('status', $status)->class('form-control required')->id('status') }}
		@if ($errors->has('status'))
		<span class="error" role="alert">{{ $errors->first('status') }}</span>
		@endif
	</div>
</div>
<div class="form-group row">
	<div class="col-sm-4 mt-3">
		<a href="{{ route('admin.products.index') }}" class="btn btn-dark btn-md">Back</a>&nbsp;
		<button type="submit" id="submit_form" class="btn btn-success btn-user btn-md">Submit</button>
	</div>
</div>
