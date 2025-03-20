@if(Session::has('alert-success'))
<script type="text/javascript">
	showSuccessMessageTopRight("{!! Session::get('alert-success') !!}");
</script>
@endif

@if(Session::has('status'))
<script type="text/javascript">
	showSuccessMessageTopRight("{!! Session::get('status') !!}");
</script>
@endif

@if(Session::has('alert-error'))
<script type="text/javascript">
	showErrorMessageTopRight("{!! Session::get('alert-error') !!}");
</script>
@endif