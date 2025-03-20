@extends('layouts.admin')
@section('title') Change Password @endsection
@section('inline-css')
@endsection
@section('content')

<div class="row">
	<div class="col-md-12">
		<div class="card">
			<div class="card-header">
				<h5>Change Password</h5>
			</div>
			<div class="card-body">
                <form method="post" action="{{ route('admin.updatepassword') }}"  enctype="multipart/form-data" class="validatedForm">
				{{ csrf_field() }}
					<div class="row">
						<div class="col-md-4">
							<div class="form-group">
                                <label class="form-label">Password <span class="required">*</span></label>
                                <input id="password" type="password" id="password" name="password" class="form-control required">
							</div>
						</div>
                    </div>  
                    <div class="row">  
						<div class="col-md-4">
							<div class="form-group">
                                <label class="form-label">Confirm Password <span class="required">*</span></label>
                                <input id="confirm_password" type="password" name="confirm_password" class="form-control required">
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
                    </form>
			</div>
		</div>
	</div>	
</div>

@endsection
@section('inline-js')
<script>
    jQuery('.validatedForm').validate({
        rules : {
            password : {
                minlength : 8
            },
            confirm_password : {
                minlength : 8,
                equalTo : "#password"
            }
        }
    });
</script> 
@endsection