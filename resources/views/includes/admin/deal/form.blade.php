<div class="form-group row">
	<div class="col-sm-4">
		<label class="form-label">Model Number <span class="required">*</span></label>
		{{ html()->text('model_number')->class('form-control form-control-user required') }}
		@if ($errors->has('model_number'))
		<span class="error" role="alert">{{ $errors->first('model_number') }}</span>
		@endif
	</div>
	<div class="col-sm-4">
		<label class="form-label">Serial Number <span class="required">*</span></label>
		{{ html()->text('serial_number')->class('form-control form-control-user required') }}
		@if ($errors->has('serial_number'))
		<span class="error" role="alert">{{ $errors->first('serial_number') }}</span>
		@endif
	</div>
</div>
<div class="form-group row">
	<div class="col-sm-4">
		<label class="form-label">Material of the watch <span class="required">*</span></label>
		{{ html()->text('material_watch')->class('form-control form-control-user required') }}
		@if ($errors->has('material_watch'))
		<span class="error" role="alert">{{ $errors->first('material_watch') }}</span>
		@endif
	</div>
	<div class="col-sm-4">
		<label class="form-label">Condition <span class="required">*</span></label>
		{{ html()->text('condition')->class('form-control form-control-user required') }}
		@if ($errors->has('condition'))
		<span class="error" role="alert">{{ $errors->first('condition') }}</span>
		@endif
	</div>	
</div>
<div class="form-group row">
	<div class="col-sm-4">
		<label class="form-label">Year <span class="required">*</span></label>
		{{ html()->text('year')->class('form-control form-control-user phoneno required')->maxlength(4) }}
		@if ($errors->has('year'))
		<span class="error" role="alert">{{ $errors->first('year') }}</span>
		@endif
	</div>
	<div class="col-sm-4">
		<label class="form-label">Full set or not <span class="required">*</span></label>
		{{ html()->text('full_set')->class('form-control form-control-user required') }}
		@if ($errors->has('full_set'))
		<span class="error" role="alert">{{ $errors->first('full_set') }}</span>
		@endif
	</div>	
</div>
<div class="form-group row">
	<div class="col-sm-4">
		<label class="form-label">Purchase Price (A$)<span class="required">*</span></label>
		{{ html()->text('purchase_price')->class('form-control form-control-user float-price required number') }}
		@if ($errors->has('purchase_price'))
		<span class="error" role="alert">{{ $errors->first('purchase_price') }}</span>
		@endif
	</div>
	<div class="col-sm-4">
		<label class="form-label">Sale Price (A$)<span class="required">*</span></label>
		{{ html()->text('sale_price')->class('form-control form-control-user float-price required number') }}
		@if ($errors->has('sale_price'))
		<span class="error" role="alert">{{ $errors->first('sale_price') }}</span>
		@endif
	</div>	
</div>
@if(isset($deal))
<div class="row">
	<div class="col-sm-12">
		<h5 class="mt-3 mb-3">Deal Address</h5>
	</div>
</div>
<div class="form-group row">	
	<div class="col-sm-4">
		<label class="form-label">Country <span class="required">*</span></label>    		
		{{ html()->select('country_id', $countries)->class('form-control required')->id('country_id')->disabled()  }}									
		@if ($errors->has('country_id'))
			<span class="error" role="alert">{{ $errors->first('country_id') }}</span>
		@endif
	</div>
	<div class="col-sm-4">
		<label class="form-label">State <span class="required">*</span></label>    		
		{{ html()->select('state_id', $states)->class('form-control required')->id('state_id')  }}									
		@if ($errors->has('state_id'))
			<span class="error" role="alert">{{ $errors->first('state_id') }}</span>
		@endif
	</div>
</div>
<div class="form-group row">	
	<div class="col-sm-4">
		<label class="form-label">City <span class="required">*</span></label>    		
		{{ html()->select('city_id', $cities)->class('form-control required')->id('city_id')  }}									
		@if ($errors->has('city_id'))
			<span class="error" role="alert">{{ $errors->first('city_id') }}</span>
		@endif
	</div>
	<div class="col-sm-4">
		<label class="form-label">Address <span class="required">*</span></label>
		{{ html()->text('address')->class('form-control form-control-user required') }}
		@if ($errors->has('address'))
		<span class="error" role="alert">{{ $errors->first('address') }}</span>
		@endif
	</div>
</div>
<div class="form-group row">
	<div class="col-sm-4">
		<label class="form-label">Zipcode <span class="required">*</span></label>
		{{ html()->text('zipcode')->class('form-control form-control-user required') }}
		@if ($errors->has('zipcode'))
		<span class="error" role="alert">{{ $errors->first('zipcode') }}</span>
		@endif
	</div>	
</div>
<div class="row">
	<div class="col-sm-12">
		<h5 class="mt-3 mb-3">Deal Status</h5>
	</div>
</div>
<div class="form-group row">	
	<div class="col-sm-4">
		<label class="form-label">Deal Status <span class="required">*</span></label>    
		@php $deal_status = Config::get('constants.DEAL_STATUS'); @endphp
		{{ html()->select('deal_status', $deal_status)->class('form-control required')->id('deal_status')  }}									
		@if ($errors->has('deal_status'))
			<span class="error" role="alert">{{ $errors->first('deal_status') }}</span>
		@endif
	</div>
	<div class="col-sm-4">
		<label class="form-label">Status (Active/Deactive) <span class="required">*</span></label>    
		@php $status = Config::get('constants.STATUS'); @endphp
		{{ html()->select('status', $status)->class('form-control required')->id('status')  }}									
		@if ($errors->has('status'))
			<span class="error" role="alert">{{ $errors->first('status') }}</span>
		@endif
	</div>
</div>
@endif
<div class="form-group row">
	<div class="col-sm-4 mt-3">
		<a href="{{route('admin.deals.index')}}" class="btn btn-dark btn-md">Back</a>&nbsp;
		<button type="submit" id="submit_form" class="btn btn-success btn-user btn-md">Submit</button>
	</div>
</div>