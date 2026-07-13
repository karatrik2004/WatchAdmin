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
    <div class="col-md-12 mb-3">
        <label>Vendor Type <span class="text-danger">*</span></label>
        <div>
            <div class="form-check form-check-inline">
                <input class="form-check-input vendor-type-radio" type="radio" name="vendor_type" id="vendor_individual" value="individual" {{ old('vendor_type', $vendor->vendor_type ?? 'individual') == 'individual' ? 'checked' : '' }}>
                <label class="form-check-label" for="vendor_individual">Individual</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input vendor-type-radio" type="radio" name="vendor_type" id="vendor_company" value="company" {{ old('vendor_type', $vendor->vendor_type ?? '') == 'company' ? 'checked' : '' }}>
                <label class="form-check-label" for="vendor_company">Company</label>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3 individual-field">
        <label>First Name <span class="text-danger">*</span></label>
        <input type="text" name="first_name" required class="form-control" maxlength="255" value="{{ old('first_name', $vendor->first_name ?? '') }}">
        @error('first_name')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-6 mb-3 individual-field">
        <label>Last Name <span class="text-danger">*</span></label>
        <input type="text" name="last_name" required class="form-control" maxlength="255" value="{{ old('last_name', $vendor->last_name ?? '') }}">
        @error('last_name')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-6 mb-3 company-field" style="display:none;">
        <label>Company Name <span class="text-danger">*</span></label>
        <input type="text" name="company_name" class="form-control" maxlength="255" value="{{ old('company_name', $vendor->company_name ?? '') }}">
        @error('company_name')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label>Email <span class="text-danger">*</span></label>
        <input type="email" name="email" required class="form-control" maxlength="255" value="{{ old('email', $vendor->email ?? '') }}">
        @error('email')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label>Contact Number <span class="text-danger">*</span></label>
        <input type="tel" name="contact_number" required class="form-control" maxlength="50" pattern="^[0-9+\-() ]+$" value="{{ old('contact_number', $vendor->contact_number ?? '') }}">
        @error('contact_number')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-12 mb-3">
        <label>Address <span class="text-danger">*</span></label>
        <textarea name="address" required class="form-control" maxlength="2000">{{ old('address', $vendor->address ?? '') }}</textarea>
        @error('address')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label>City <span class="text-danger">*</span></label>
        <input type="text" name="city" required class="form-control" maxlength="255" value="{{ old('city', $vendor->city ?? '') }}">
        @error('city')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label>State <span class="text-danger">*</span></label>
        <input type="text" name="state" required class="form-control" maxlength="255" value="{{ old('state', $vendor->state ?? '') }}">
        @error('state')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label>Country <span class="text-danger">*</span></label>
        <input type="text" name="country" required class="form-control" maxlength="255" value="{{ old('country', $vendor->country ?? '') }}">
        @error('country')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label>Zipcode <span class="text-danger">*</span></label>
        <input type="text" name="zipcode" required class="form-control" maxlength="50" value="{{ old('zipcode', $vendor->zipcode ?? '') }}">
        @error('zipcode')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-6 mb-3 company-field" style="display:none;">
        <label>ABN <span class="text-danger">*</span></label>
        <input type="text" name="abn" class="form-control" maxlength="255" value="{{ old('abn', $vendor->abn ?? '') }}">
        @error('abn')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-6 mb-3 company-field" style="display:none;">
        <label>Second-hand Dealer Licence Number <span class="text-danger">*</span></label>
        <input type="text" name="dealer_licence_number" class="form-control" maxlength="255" value="{{ old('dealer_licence_number', $vendor->dealer_licence_number ?? '') }}">
        @error('dealer_licence_number')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-12 mb-3 company-field" style="display:none;">
        <label>Director Name <span class="text-danger">*</span></label>
        <input type="text" name="director_name" class="form-control" maxlength="255" value="{{ old('director_name', $vendor->director_name ?? '') }}">
        @error('director_name')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
</div>
<div class="mt-3">
    <button type="submit" class="btn btn-primary">{{ $submitText ?? (isset($vendor) ? 'Update Vendor' : 'Save Vendor') }}</button>
    <a href="{{ route('admin.vendors.index') }}" class="btn btn-secondary">Cancel</a>
</div>
<script>
    (function(){
        function toggleVendorFields() {
            var type = document.querySelector('input[name="vendor_type"]:checked').value;
            var individualFields = document.querySelectorAll('.individual-field');
            var companyFields = document.querySelectorAll('.company-field');

            if (type === 'company') {
                individualFields.forEach(function(el){ el.style.display = 'none'; el.querySelectorAll('input,textarea,select').forEach(i=>i.removeAttribute('required')); });
                companyFields.forEach(function(el){ el.style.display = ''; el.querySelectorAll('input,textarea,select').forEach(i=>i.setAttribute('required','required')); });
            } else {
                individualFields.forEach(function(el){ el.style.display = ''; el.querySelectorAll('input,textarea,select').forEach(i=>i.setAttribute('required','required')); });
                companyFields.forEach(function(el){ el.style.display = 'none'; el.querySelectorAll('input,textarea,select').forEach(i=>i.removeAttribute('required')); });
            }
        }
        document.querySelectorAll('.vendor-type-radio').forEach(function(r){ r.addEventListener('change', toggleVendorFields); });
        // run once on load
        document.addEventListener('DOMContentLoaded', toggleVendorFields);
        // also run immediately in case not DOMContent
        toggleVendorFields();
    })();
</script>
