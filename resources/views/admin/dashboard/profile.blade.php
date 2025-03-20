@extends('layouts.admin')
@section('title') My Profile @endsection
@section('inline-css')
@endsection
@section('content')

<div class="row">
	<div class="col-md-12">
		<div class="card">
			<div class="card-header">
				<h5>My Profile</h5>
			</div>
			<div class="card-body">
				{{ html()->modelForm($current_user,'POST',route('admin.submit_profile',$current_user->id))->class('validatedForm')->id('profile_form')->acceptsFiles()->open() }}

					<div class="row">
						<div class="col-md-4">
							<div class="form-group">
								<label class="form-label">First Name <span class="required">*</span></label>
								{{ html()->text('firstname')->class('form-control form-control-user required') }}
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label class="form-label">Last Name</label>
								{{ html()->text('lastname')->class('form-control form-control-user') }}
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							<div class="form-group">
								<label class="form-label">Email Address <span class="required">*</span></label>
								{{ html()->email('email')->class('form-control form-control-user')->isReadonly() }}
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label class="form-label">Mobile</label>
								{{ html()->text('mobile')->class('form-control form-control-user mobile')->maxlength(12) }}
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							<div class="form-group">
								<a href="{{route('admin.dashboard')}}" class="btn  btn-dark">Back</a>
								&nbsp;
								<button type="submit" class="btn  btn-success">Update</button>
							</div>
						</div>
					</div>
				{{ html()->form()->close() }}
			</div>
		</div>
	</div>	
</div>
@endsection
@section('inline-js')
<script>
	$('.validatedForm').validate();
</script>
@endsection