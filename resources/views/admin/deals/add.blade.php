@extends('layouts.admin')
@section('title') Add Deal @endsection
@section('inline-css')
@endsection
@section('content')


<div class="col-md-12">
		<div class="card">
			<div class="card-header">
				<h5>Create Deal</h5>
			</div>
			<div class="card-body">				
				{{ html()->form('POST', route('admin.deals.store'))->class('validatedForm')->id('deal_form')->open() }}
					{{ csrf_field() }}
					@include('includes.admin.deal.form')
				{{ html()->form()->close() }}
		</div>
	</div>



@endsection
@section('inline-js')
<script>
    jQuery('.validatedForm').validate();	
</script> 
<script>
    $(document).ready(function () {
        $('#state_id').on('change', function () {
            var stateId = $(this).val();
            if (stateId) {
                $.ajax({
                    url: '{{ route("admin.get-cities", ":stateId") }}'.replace(':stateId', stateId),
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        $('#city_id').empty().append('<option value="">Select City</option>');
                        $.each(data, function (key, value) {
                            $('#city_id').append('<option value="' + key + '">' + value + '</option>');
                        });
                    }
                });
            } else {
                $('#city_id').empty().append('<option value="">Select City</option>');
            }
        });
    });
</script>
@endsection