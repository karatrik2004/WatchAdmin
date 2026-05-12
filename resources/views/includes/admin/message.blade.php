@if(Session::has('alert-success'))
<script type="text/javascript">
	showSuccessMessageTopRight({!! json_encode(Session::get('alert-success')) !!});
</script>
@endif

@if(Session::has('status'))
<script type="text/javascript">
	showSuccessMessageTopRight({!! json_encode(Session::get('status')) !!});
</script>
@endif

@if(Session::has('alert-error'))
<script type="text/javascript">
	showErrorMessageTopRight({!! json_encode(Session::get('alert-error')) !!});
</script>
@endif

@if(Session::has('alert-danger'))
<script type="text/javascript">
	showErrorMessageTopRight({!! json_encode(Session::get('alert-danger')) !!});
</script>
@endif