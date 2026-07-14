@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-md-6 mb-3">
        <label>Buyer Name <span class="text-danger">*</span></label>
        <input type="text" name="buyer_name" required class="form-control" maxlength="255" value="{{ old('buyer_name', $customer->buyer_name ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label>Buyer Email</label>
        <input type="email" name="buyer_email" class="form-control" maxlength="255" value="{{ old('buyer_email', $customer->buyer_email ?? '') }}">
    </div>
    <div class="col-md-12 mb-3">
        <label>Address <span class="text-danger">*</span></label>
        <input type="text" name="buyer_address" required class="form-control" maxlength="255" value="{{ old('buyer_address', $customer->buyer_address ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label>City <span class="text-danger">*</span></label>
        <input type="text" name="buyer_city" required class="form-control" maxlength="255" value="{{ old('buyer_city', $customer->buyer_city ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label>State <span class="text-danger">*</span></label>
        <input type="text" name="buyer_state" required class="form-control" maxlength="255" value="{{ old('buyer_state', $customer->buyer_state ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label>Country <span class="text-danger">*</span></label>
        <input type="text" name="buyer_country" required class="form-control" maxlength="255" value="{{ old('buyer_country', $customer->buyer_country ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label>Zipcode <span class="text-danger">*</span></label>
        <input type="text" name="buyer_zipcode" required class="form-control" maxlength="50" value="{{ old('buyer_zipcode', $customer->buyer_zipcode ?? '') }}">
    </div>
</div>
<div class="mt-3">
    <button type="submit" class="btn btn-primary">{{ $submitText ?? (isset($customer) ? 'Update Customer' : 'Save Customer') }}</button>
    <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">Cancel</a>
</div>
