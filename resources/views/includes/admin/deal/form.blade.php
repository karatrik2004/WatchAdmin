<div class="form-group row">
    <div class="col-sm-4">
        <label class="form-label">Model <span class="required">*</span></label>
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
    <div class="col-sm-4">
        <label class="form-label">Reference Number <span class="required" aria-required="true">*</span></label>
        {{ html()->text('material_watch')
            ->id('material_watch')
            ->class('form-control form-control-user')
            ->attribute('pattern', '[^/]*')
            ->attribute('title', 'Forward slash (/) is not allowed')
            ->attribute('onkeydown', "if (event.key === '/' || event.code === 'Slash' || event.code === 'NumpadDivide' || event.keyCode === 191) { event.preventDefault(); }")
            ->attribute('onbeforeinput', "if (event.data && event.data.indexOf('/') !== -1) { event.preventDefault(); }")
            ->attribute('oninput', "if (this.value.indexOf('/') !== -1) { this.value = this.value.replace(/\\//g, ''); }")
            ->attribute('onpaste', "setTimeout(() => { if (this.value.indexOf('/') !== -1) { this.value = this.value.replace(/\\//g, ''); } }, 0)")
            ->attribute('data-msg-pattern', 'Forward slash (/) is not allowed in Reference Number') }}
        @if ($errors->has('material_watch'))
            <span class="error" role="alert">{{ $errors->first('material_watch') }}</span>
        @endif
    </div>
    

</div>
<div class="form-group row">
    <div class="col-sm-4">
        <label class="form-label">Condition <span class="required" aria-required="true">*</span></label>
        {{ html()->text('condition')->class('form-control form-control-user') }}
        @if ($errors->has('condition'))
            <span class="error" role="alert">{{ $errors->first('condition') }}</span>
        @endif
    </div>
    <div class="col-sm-4">
        <label class="form-label">Year <span class="required" aria-required="true">*</span></label>
        {{ html()->text('year')->class('form-control form-control-user phoneno')->maxlength(4) }}
        @if ($errors->has('year'))
            <span class="error" role="alert">{{ $errors->first('year') }}</span>
        @endif
    </div>
    <div class="col-sm-4">
        <label class="form-label">Full set or not <span class="required" aria-required="true">*</span></label>
        {{ html()->select('full_set', config('constants.FULL_STATUS'))->class('form-control')->id('full_set')  }}
        @if ($errors->has('full_set'))
            <span class="error" role="alert">{{ $errors->first('full_set') }}</span>
        @endif
    </div>
</div>
<div class="form-group row">
    <div class="col-sm-4">
        <label class="form-label">Purchase Price <span class="required" aria-required="true">*</span></label>
        <div class="input-group">
            <select name="purchase_currency" id="purchase_currency" class="form-control" style="max-width:120px;">
                @foreach(config('constants.CURRENCIES', ['aud' => 'AUD', 'usd' => 'USD']) as $code => $label)
                    <option value="{{ $code }}" {{ old('purchase_currency', $deal->purchase_currency ?? config('constants.DEFAULT_CURRENCY', 'aud')) == $code ? 'selected' : '' }}>{{ strtoupper($label) }}
                    </option>
                @endforeach
            </select>
            {{ html()->text('purchase_price')->class('form-control form-control-user float-price number')->id('purchase_price') }}
        </div>
        @if ($errors->has('purchase_price'))
            <span class="error" role="alert">{{ $errors->first('purchase_price') }}</span>
        @endif
    </div>
    <div class="col-sm-4">
        <label class="form-label">Estimated Sale Price <span class="required" aria-required="true">*</span></label>
        {{ html()->text('sale_price')->class('form-control form-control-user float-price number') }}
        @if ($errors->has('sale_price'))
            <span class="error" role="alert">{{ $errors->first('sale_price') }}</span>
        @endif
    </div>
    <div class="col-sm-4 nra">
        <label class="form-label">Brand <span class="required" aria-required="true">*</span></label>
        <!-- Backup: AJAX autosuggestion code below
        <input type="text" name="brand_name" id="brand_name" class="form-control brand-autocomplete" autocomplete="off"
               value="{{ old('brand_name') }}">
        <input type="hidden" name="brand_id" id="brand_id" class="brand-id"
               value="{{ old('brand_id',$deal->brand_id ?? '') }}">
        -->
        @php
            $brands = \App\Models\WatchBrand::orderBy('name')->get();
        @endphp
        <select name="brand_id" id="brand_id" class="form-control">
            <option value="">Select Brand</option>
            @foreach($brands as $brand)
                <option value="{{ $brand->id }}" {{ old('brand_id', $deal->brand_id ?? '') == $brand->id ? 'selected' : '' }}>
                    {{ $brand->name }}</option>
            @endforeach
        </select>
        @if ($errors->has('brand_id'))
            <span class="error" role="alert">{{ $errors->first('brand_id') }}</span>
        @endif
    </div>
    <div class="col-sm-4 mt-3">
        <label class="form-label">GST Code <span class="required" aria-required="true">*</span></label>
        {{ html()->select('gst_code', ['' => 'Select Option'] + config('constants.GST_CODE'), old('gst_code', $deal->gst_code ?? ''))->class('form-control')->id('gst_code') }}
        @if ($errors->has('gst_code'))
            <span class="error" role="alert">{{ $errors->first('gst_code') }}</span>
        @endif
    </div>
    <div class="col-sm-4 mt-3">
        <label class="form-label">Deal Supplier Status <span class="required" aria-required="true">*</span></label>
        {{ html()->select('deal_supplier_status', ['' => 'Select Option'] + config('constants.DEAL_SUPPLIER_STATUS'), old('deal_supplier_status', $deal->deal_supplier_status ?? ''))->class('form-control')->id('deal_supplier_status') }}
        @if ($errors->has('deal_supplier_status'))
            <span class="error" role="alert">{{ $errors->first('deal_supplier_status') }}</span>
        @endif
    </div>
    <div class="col-sm-4 mt-3">
        <label class="form-label">Invoice Number <span class="required" aria-required="true">*</span></label>
        {{ html()->text('purchase_invoice_number', old('purchase_invoice_number', $deal->purchase_invoice_number ?? ''))->class('form-control') }}
        @if ($errors->has('purchase_invoice_number'))
            <span class="error" role="alert">{{ $errors->first('purchase_invoice_number') }}</span>
        @endif
    </div>
    <div class="col-sm-4 mt-3">
        <label class="form-label">Invoice Date <span class="required" aria-required="true">*</span></label>
        {{ html()->date('purchase_invoice_date', old('purchase_invoice_date', isset($deal->purchase_invoice_date) ?
    \Carbon\Carbon::parse($deal->purchase_invoice_date)->format('Y-m-d') : ''))->class('form-control') }}
        @if ($errors->has('purchase_invoice_date'))
            <span class="error" role="alert">{{ $errors->first('purchase_invoice_date') }}</span>
        @endif
    </div>
    <div class="col-sm-4 mt-3">
        <label class="form-label">Dial <span class="required" aria-required="true">*</span></label>
        {{ html()->text('dial')->class('form-control form-control-user') }}
        @if ($errors->has('dial'))
            <span class="error" role="alert">{{ $errors->first('dial') }}</span>
        @endif
    </div>
    {{--Indifidual/ComPany--}}

    {{-- Removed extra closing div here --}}
    <div class="form-group row mt-3 mb-3">
        <div class="col-sm-4">
            <label class="form-label">Select Type 

            <div class="form-check form-check-inline">
                {{ html()->radio('customer_type', old('customer_type', $deal->customer_type ?? '') == 'individual' || !old('customer_type') && empty($deal->customer_type), 'individual')
    ->class('form-check-input required')
    ->id('individual') }}
                <label class="form-check-label" for="individual">Individual</label>
            </div>

            <div class="form-check form-check-inline">
                {{ html()->radio('customer_type', old('customer_type', $deal->customer_type ?? '') == 'company', 'company')
    ->class('form-check-input required')
    ->id('company') }}
                <label class="form-check-label" for="company">Company</label>
            </div>

            @if ($errors->has('customer_type'))
                <span class="error" role="alert">{{ $errors->first('customer_type') }}</span>
            @endif
        </div>
    </div>

    @php
        $selectedVendor = isset($deal) ? $deal->vendor : null;
        $selectedVendorName = '';
        if ($selectedVendor) {
            $selectedVendorName = $selectedVendor->vendor_type === 'company'
                ? ($selectedVendor->company_name ?? '')
                : trim(($selectedVendor->first_name ?? '') . ' ' . ($selectedVendor->last_name ?? ''));
        }
    @endphp

    <style>
        .vendor-suggest-menu {
            max-height: 280px;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 2500;
            margin-top: 6px;
            padding: 0;
            list-style: none;
            border: 1px solid #d8dde6;
            border-radius: 8px;
            background: #ffffff;
            box-shadow: 0 10px 28px rgba(17, 24, 39, 0.12);
        }

        .vendor-suggest-menu .ui-menu-item {
            margin: 0;
            padding: 0;
            border-bottom: 1px solid #edf1f6;
        }

        .vendor-suggest-menu .ui-menu-item:last-child {
            border-bottom: none;
        }

        .vendor-suggest-menu .ui-menu-item-wrapper {
            display: block;
            margin: 0;
            padding: 10px 12px;
            border: none;
            background: #ffffff;
        }

        .vendor-suggest-name {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
            line-height: 1.35;
        }

        .vendor-suggest-meta {
            display: block;
            margin-top: 3px;
            font-size: 12px;
            color: #6b7280;
            line-height: 1.35;
            white-space: normal;
            word-break: break-word;
        }

        .vendor-suggest-menu .ui-state-active,
        .vendor-suggest-menu .ui-menu-item-wrapper.ui-state-active {
            background: #eef5ff;
            color: inherit;
            margin: 0;
            border: none;
        }

        .vendor-suggest-menu .ui-state-active .vendor-suggest-name {
            color: #0a58ca;
        }

        .vendor-suggest-menu .ui-state-active .vendor-suggest-meta {
            color: #3b82f6;
        }
    </style>

    <div class="form-group row mb-4">
        <div class="col-sm-12">
            <div class="p-3 border rounded bg-light-subtle">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <label class="form-label mb-0 fw-semibold">Vendor Lookup (Optional)</label>
                    <button type="button" id="clear_vendor_selection" class="btn btn-outline-secondary btn-sm">Clear Selection</button>
                </div>

                <div class="input-group">
                    <span class="input-group-text"><i class="fa fa-search"></i></span>
                    <input type="text" id="vendor_search" class="form-control" value="{{ old('vendor_search', $selectedVendorName) }}"
                        placeholder="Search vendor by name, email, contact, ABN">
                </div>

                <input type="hidden" name="vendor_id" id="vendor_id" value="{{ old('vendor_id', $deal->vendor_id ?? '') }}">

                <div class="d-flex flex-wrap justify-content-between align-items-center mt-2 gap-2">
                    <small class="text-muted" id="vendor_lookup_hint">Choose a vendor to auto-fill details. You can still edit all fields manually.</small>
                    <small id="vendor_type_badge" class="badge bg-secondary text-uppercase"></small>
                </div>

                @if ($errors->has('vendor_id'))
                    <span class="error d-block mt-1" role="alert">{{ $errors->first('vendor_id') }}</span>
                @endif

                <div id="vendor_lookup_feedback" class="small mt-2"></div>
            </div>
        </div>
    </div>

    <!-- Individual Fields -->
    <div id="individual-fields" class="row mt-3">
        <div class="col-sm-4">
            <label class="form-label">First Name <span class="required" aria-required="true">*</span></label>
            {{ html()->text('first_name', old('first_name', $deal->first_name ?? ''))
    ->class('form-control individual-field') }}
            @if ($errors->has('first_name'))
                <span class="error" role="alert">{{ $errors->first('first_name') }}</span>
            @endif
        </div>
        <div class="col-sm-4">
            <label class="form-label">Last Name <span class="required" aria-required="true">*</span></label>
            {{ html()->text('last_name', old('last_name', $deal->last_name ?? ''))
    ->class('form-control individual-field') }}
            @if ($errors->has('last_name'))
                <span class="error" role="alert">{{ $errors->first('last_name') }}</span>
            @endif
        </div>
        <div class="col-sm-4">
            <label class="form-label">Email <span class="required" aria-required="true">*</span></label>
            {{ html()->email('email', old('email', $deal->email ?? ''))
    ->class('form-control individual-field') }}
            @if ($errors->has('email'))
                <span class="error" role="alert">{{ $errors->first('email') }}</span>
            @endif
        </div>
        <div class="col-sm-4 mt-3">
            <label class="form-label">Contact Number <span class="required" aria-required="true">*</span></label>
            {{ html()->text('mobile', old('mobile', $deal->mobile ?? ''))
    ->class('form-control individual-field') }}
            @if ($errors->has('mobile'))
                <span class="error" role="alert">{{ $errors->first('mobile') }}</span>
            @endif
        </div>
        <div class="row">
            <div class="col-sm-12">
                <h5 class="mt-3 mb-3">Individual Address</h5>
            </div>
        </div>
        <div class="form-group row">
            {{--<div class="col-sm-4">
                <label class="form-label">Country </label>
                {{ html()->select('country_id', $countries)->class('form-control')->id('country_id') }}
                @if ($errors->has('country_id'))
                <span class="error" role="alert">{{ $errors->first('country_id') }}</span>
                @endif
            </div>--}}

            <div class="col-sm-4">
                <label class="form-label">Address <span class="required" aria-required="true">*</span></label>
                {{ html()->text('address')->class('form-control form-control-user individual-field') }}
                @if ($errors->has('address'))
                    <span class="error" role="alert">{{ $errors->first('address') }}</span>
                @endif
            </div>
            <div class="col-sm-4">
                <label class="form-label">City <span class="required" aria-required="true">*</span></label>
                {{ html()->text('city')->class('form-control individual-field')->id('city') }}
                @if ($errors->has('city'))
                    <span class="error" role="alert">{{ $errors->first('city') }}</span>
                @endif
            </div>
            <div class="col-sm-4">
                <label class="form-label">State <span class="required" aria-required="true">*</span></label>
                {{ html()->text('state')->class('form-control individual-field')->id('state') }}
                @if ($errors->has('state'))
                    <span class="error" role="alert">{{ $errors->first('state') }}</span>
                @endif
            </div>
            <div class="col-sm-4  mt-3">
                <label class="form-label">Country <span class="required" aria-required="true">*</span></label>
                {{ html()->text('country')->class('form-control individual-field')->id('country') }}
                @if ($errors->has('country'))
                    <span class="error" role="alert">{{ $errors->first('country') }}</span>
                @endif
            </div>
            {{--<div class="col-sm-4">
                <label class="form-label">State </label>
                {{ html()->select('state_id', $states)->class('form-control individual-field')->id('state_id') }}
                @if ($errors->has('state_id'))
                <span class="error" role="alert">{{ $errors->first('state_id') }}</span>
                @endif
            </div>
            <div class="col-sm-4">
                <label class="form-label">City </label>
                {{ html()->select('city_id', $cities)->class('form-control individual-field')->id('city_id') }}
                @if ($errors->has('city_id'))
                <span class="error" role="alert">{{ $errors->first('city_id') }}</span>
                @endif
            </div>--}}
            <div class="col-sm-4 mt-3">
                <label class="form-label">Zipcode <span class="required" aria-required="true">*</span></label>
                {{ html()->text('zipcode')->class('form-control form-control-user individual-field') }}
                @if ($errors->has('zipcode'))
                    <span class="error" role="alert">{{ $errors->first('zipcode') }}</span>
                @endif
            </div>
        </div>
    </div>
    <!-- Company Fields -->
    <div id="company-fields" class="row mt-3">
        <div class="col-sm-4">
            <label class="form-label">Company Name <span class="required" aria-required="true">*</span></label>
            {{ html()->text('company_name', old('company_name', $deal->dealCustomerTypeDetail->company_name ?? ''))
    ->class('form-control company-field') }}
            @if ($errors->has('company_name'))
                <span class="error" role="alert">{{ $errors->first('company_name') }}</span>
            @endif
        </div>
        <div class="col-sm-4">
            <label class="form-label">Company Email <span class="required" aria-required="true">*</span></label>
            {{ html()->email('company_email', old('company_email', $deal->dealCustomerTypeDetail->company_email ?? ''))
    ->class('form-control company-field') }}
            @if ($errors->has('company_email'))
                <span class="error" role="alert">{{ $errors->first('company_email') }}</span>
            @endif
        </div>
        <div class="col-sm-4">
            <label class="form-label">Company Contact Number <span class="required" aria-required="true">*</span></label>
            {{ html()->text('company_mobile', old('company_mobile', $deal->dealCustomerTypeDetail->company_mobile ?? ''))
    ->class('form-control company-field') }}
            @if ($errors->has('company_name'))
                <span class="error" role="alert">{{ $errors->first('company_mobile') }}</span>
            @endif
        </div>
        <div class="col-sm-4 mt-3">
            <label class="form-label">Upload Invoice</label>

            <div id="invoice-input-container">
                @if (empty($deal->dealCustomerTypeDetail->invoice_file))
                    <!-- Display the file input field when no file is uploaded -->
                    <input type="file" name="invoice_file" class="form-control">
                @else
                    <!-- Display current invoice details with View and Remove options -->
                    <div class="current-invoice p-3 border rounded shadow-sm">
                        <p><strong>Current File:</strong></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ asset('storage/' . $deal->dealCustomerTypeDetail->invoice_file) }}" target="_blank"
                                class="btn btn-primary btn-sm">View Invoice</a>
                            <a href="javascript:void(0)" onclick="removeInvoice()" class="btn btn-danger btn-sm">
                                <i class="fas fa-times"></i> Remove Invoice
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            @if ($errors->has('invoice_file'))
                <span class="error" role="alert">{{ $errors->first('invoice_file') }}</span>
            @endif
        </div>

        <div class="row">
            <div class="col-sm-12">
                <h5 class="mt-3 mb-3">Company Address</h5>
            </div>
        </div>
        <div class="form-group row">

            <div class="col-sm-4">
                <label class="form-label">Address <span class="required" aria-required="true">*</span></label>
                {{ html()->text('company_address', old('company_address', $deal->dealCustomerTypeDetail->company_address ?? ''))
    ->class('form-control form-control-user company-field') }}
                @if ($errors->has('company_address'))
                    <span class="error" role="alert">{{ $errors->first('company_address') }}</span>
                @endif
            </div>
            <div class="col-sm-4">
                <label class="form-label">City <span class="required" aria-required="true">*</span></label>
                <input type="text" name="company_city" id="company_city" class="form-control company-field"
                    value="{{ old('company_city', $deal->dealCustomerTypeDetail->company_city ?? '') }}">
                @if ($errors->has('company_city'))
                    <span class="error" role="alert">{{ $errors->first('company_city') }}</span>
                @endif
            </div>
            <div class="col-sm-4">
                <label class="form-label">State <span class="required" aria-required="true">*</span></label>
                <input type="text" name="company_state" id="company_state" class="form-control company-field"
                    value="{{ old('company_state', $deal->dealCustomerTypeDetail->company_state ?? '') }}">
                @if ($errors->has('company_state'))
                    <span class="error" role="alert">{{ $errors->first('company_state') }}</span>
                @endif
            </div>
            <div class="col-sm-4 mt-3">
                <label class="form-label">Country <span class="required" aria-required="true">*</span></label>
                <input type="text" name="company_country" id="company_country" class="form-control"
                    value="{{ old('company_country', $deal->dealCustomerTypeDetail->company_country ?? '') }}">
                @if ($errors->has('company_country'))
                    <span class="error" role="alert">{{ $errors->first('company_country') }}</span>
                @endif
            </div>


            {{-- <div class="col-sm-4">
                <label class="form-label">Country </label>
                {{ html()->select('company_country_id', $countries)
                ->class('form-control')
                ->id('company_country_id')
                ->value(old('company_country_id', $deal->dealCustomerTypeDetail->company_country_id ?? ''))
                }}
                @if ($errors->has('company_country_id'))
                <span class="error" role="alert">{{ $errors->first('company_country_id') }}</span>
                @endif
            </div>

            <div class="col-sm-4">
                <label class="form-label">State</label>
                {{ html()->select('company_state_id', $states)
                ->class('form-control company-field')
                ->id('company_state_id')
                ->value(old('company_state_id', $deal->dealCustomerTypeDetail->company_state_id ?? '')) }}
                @if ($errors->has('company_state_id'))
                <span class="error" role="alert">{{ $errors->first('company_state_id') }}</span>
                @endif
            </div>

            <div class="col-sm-4">
                <label class="form-label">City</label>
                {{ html()->select('company_city_id', $cities)
                ->class('form-control company-field')
                ->id('company_city_id')
                ->value(old('company_city_id', $deal->dealCustomerTypeDetail->company_city_id ?? '')) }}
                @if ($errors->has('company_city_id'))
                <span class="error" role="alert">{{ $errors->first('company_city_id') }}</span>
                @endif
            </div>--}}


            <div class="col-sm-4 mt-3">
                <label class="form-label">Zipcode <span class="required" aria-required="true">*</span></label>
                {{ html()->text('company_zip_code', old('company_zip_code', $deal->dealCustomerTypeDetail->company_zip_code ?? ''))
    ->class('form-control form-control-user company-field') }}
                @if ($errors->has('company_zip_code'))
                    <span class="error" role="alert">{{ $errors->first('company_zip_code') }}</span>
                @endif
            </div>

            <div class="col-sm-4 mt-3">
                <label class="form-label">ABN Number <span class="required" aria-required="true">*</span></label>
                {{ html()->text('abn_number', old('abn_number', $deal->dealCustomerTypeDetail->abn_number ?? ''))
    ->class('form-control form-control-user company-field') }}
                @if ($errors->has('abn_number'))
                    <span class="error" role="alert">{{ $errors->first('abn_number') }}</span>
                @endif
            </div>

            <div class="col-sm-4 mt-3">
                <label class="form-label">Director's Name <span class="required" aria-required="true">*</span></label>
                {{ html()->text('director_name', old('director_name', $deal->dealCustomerTypeDetail->director_name ?? ''))
    ->class('form-control form-control-user company-field') }}
                @if ($errors->has('director_name'))
                    <span class="error" role="alert">{{ $errors->first('director_name') }}</span>
                @endif
            </div>

            <div class="col-sm-4 mt-3">
                <label class="form-label">Second-hand Dealer Licence Number <span class="required" aria-required="true">*</span></label>
                {{ html()->text('dealer_licence_number', old('dealer_licence_number', $deal->dealCustomerTypeDetail->dealer_licence_number ?? ''))
    ->class('form-control form-control-user company-field') }}
                @if ($errors->has('dealer_licence_number'))
                    <span class="error" role="alert">{{ $errors->first('dealer_licence_number') }}</span>
                @endif
            </div>
        </div>

    </div>
</div>

@php
    $steps = isset($deal->checklist_steps) ? json_decode($deal->checklist_steps, true) ?? [] : [];
@endphp
<div class="form-group row">
    <label class="form-label fw-bold mb-3 d-block">Conditions for Evaluation of Deals</label>

    <div class="row">
        <div class="col-md-4 col-sm-12 mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="step[2]" value="1" id="step2" {{ old('step.2', $steps[2] ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="step2">
                    Confirmed Written offer & Evidence received
                </label>
            </div>
        </div>

        <div class="col-md-4 col-sm-12 mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="step[3]" value="1" id="step3" {{ old('step.3', $steps[3] ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="step3">
                    Expected Turnaround time must be specified in the email
                </label>
            </div>
        </div>

        <div class="col-md-4 col-sm-12 mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="step[4]" value="1" id="step4" {{ old('step.4', $steps[4] ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="step4">
                    Deposit required for Deals above $300K or more
                </label>
            </div>
        </div>
    </div>
</div>

@if(isset($deal))

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
    <div id="dealSoldOutDiv" style="display: none">
        <div class="row">
            <div class="col-sm-12">
                <h5 class="mt-3 mb-3">Details of the Buyer</h5>
            </div>
        </div>
        <div class="form-group row mb-3">
            <div class="col-sm-12">
                <div class="p-3 border rounded bg-light-subtle">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                        <label class="form-label mb-0 fw-semibold">Customer Lookup (Optional)</label>
                        <button type="button" id="clear_customer_selection" class="btn btn-outline-secondary btn-sm">Clear Selection</button>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-search"></i></span>
                        <input type="text" id="customer_search" class="form-control"
                            value="{{ old('customer_search', optional($deal->customer)->buyer_name) }}"
                            placeholder="Search customer by name, email, city, country">
                    </div>
                    <input type="hidden" name="customer_id" id="customer_id_lookup" value="{{ old('customer_id', $deal->customer_id ?? '') }}">
                    <small class="text-muted d-block mt-2">Select customer to auto-fill buyer fields below. You can still edit manually.</small>
                    <div id="customer_lookup_feedback" class="small mt-1"></div>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-sm-4">
                <label class="form-label">Name of Buyer <span class="required">*</span></label>
                {{ html()->text('buyer_name', optional($deal->dealBuyerDetail)->buyer_name)
        ->class('form-control required')
        ->id('buyer_name')
        ->disabled() }}
                @error('buyer_name')
                    <span class="error" role="alert">{{ $message }}</span>
                @enderror
            </div>
            <div class="col-sm-4">
                <label class="form-label">Buyer email</label>
                {{ html()->email('buyer_email', old('buyer_email', optional($deal->dealBuyerDetail)->buyer_email))
                ->class('form-control')
                ->id('buyer_email')

                ->attribute('placeholder', 'buyer@example.com') }}
                @error('buyer_email')
                    <span class="error" role="alert">{{ $message }}</span>
                @enderror
            </div>
            <div class="col-sm-4">
                <label class="form-label">Address <span class="required">*</span></label>
                {{ html()->text('buyer_address', optional($deal->dealBuyerDetail)->buyer_address)
        ->class('form-control form-control-user required')
        ->disabled() }}
                @error('buyer_address')
                    <span class="error" role="alert">{{ $message }}</span>
                @enderror
            </div>
            <div class="col-sm-4 mt-3">
                <label class="form-label">City <span class="required">*</span></label>
                <input type="text" name="buyer_city" id="buyer_city" class="form-control required"
                    value="{{ old('buyer_city', optional($deal->dealBuyerDetail)->buyer_city) }}">
                @error('buyer_city')
                    <span class="error" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="col-sm-4 mt-3">
                <label class="form-label">State <span class="required">*</span></label>
                <input type="text" name="buyer_state" id="buyer_state" class="form-control required"
                    value="{{ old('buyer_state', optional($deal->dealBuyerDetail)->buyer_state) }}">
                @error('buyer_state')
                    <span class="error" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="col-sm-4 mt-3">
                <label class="form-label">Country <span class="required">*</span></label>
                <input type="text" name="buyer_country" id="buyer_country" class="form-control required"
                    value="{{ old('buyer_country', optional($deal->dealBuyerDetail)->buyer_country) }}">
                @error('buyer_country')
                    <span class="error" role="alert">{{ $message }}</span>
                @enderror
            </div>

                {{--<div class="col-sm-4">
                    <label class="form-label">City <span class="required">*</span></label>
                    {{ html()->select('buyer_city_id', $cities, optional($deal->dealBuyerDetail)->buyer_city_id)
                    ->class('form-control required')
                    ->id('buyer_city_id')
                    ->disabled() }}
                    @error('buyer_city_id')
                    <span class="error" role="alert">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-sm-4">
                    <label class="form-label">State <span class="required">*</span></label>
                    {{ html()->select('buyer_state_id', $states, optional($deal->dealBuyerDetail)->buyer_state_id)
                    ->class('form-control required')
                    ->id('buyer_state_id')
                    ->disabled() }}
                    @error('buyer_state_id')
                    <span class="error" role="alert">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-sm-4">
                    <label class="form-label">Country <span class="required">*</span></label>
                    {{ html()->select('buyer_country_id', $countries, optional($deal->dealBuyerDetail)->buyer_country_id)
                    ->class('form-control required')
                    ->id('buyer_country_id')
                    ->disabled() }}
                    @error('buyer_country_id')
                    <span class="error" role="alert">{{ $message }}</span>
                    @enderror
                </div>--}}


            <div class="col-sm-4 mt-3">
                <label class="form-label">Zipcode <span class="required">*</span></label>
                {{ html()->text('buyer_zipcode', optional($deal->dealBuyerDetail)->buyer_zipcode)
        ->class('form-control form-control-user required')
        ->disabled() }}
                @error('buyer_zipcode')
                    <span class="error" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="col-sm-4 mt-3">
                <label class="form-label">Sold Watch Sale Price <span class="required">*</span></label>
                <div class="input-group">
                    <select name="buyer_currency" id="buyer_currency" class="form-control" style="max-width:120px;">
                        <option value="aud" {{ old('buyer_currency', optional($deal->dealBuyerDetail)->buyer_currency ?? $deal->buyer_currency ?? 'aud') == 'aud' ? 'selected' : '' }}>AUD</option>
                        <option value="usd" {{ old('buyer_currency', optional($deal->dealBuyerDetail)->buyer_currency ?? $deal->buyer_currency ?? 'aud') == 'usd' ? 'selected' : '' }}>USD</option>
                    </select>
                    {{ html()->text('buyer_sale_price', optional($deal->dealBuyerDetail)->buyer_sale_price)
        ->class('form-control form-control-user float-price required number')
        ->id('buyer_sale_price')
        ->disabled() }}
                </div>
                @error('buyer_sale_price')
                    <span class="error" role="alert">{{ $message }}</span>
                @enderror
            </div>
            <div class="col-sm-4 mt-3">
                <label class="form-label">Profit / Loss Preview</label>
                <div id="pl_preview" class="p-2 border rounded bg-light">
                    @if(isset($deal) && $deal->profit_amount !== null && $deal->sale_to_purchase_rate !== null)
                        <div id="pl_rate">Rate: {{ $deal->sale_to_purchase_rate }}</div>
                        <div id="pl_sale_in_purchase">Sale in
                            {{ strtoupper($deal->profit_currency ?? $deal->purchase_currency ?? 'AUD') }}:
                            {{ $deal->sale_in_purchase_currency }}</div>
                        <div id="pl_profit" class="fw-bold">
                            {{ $deal->is_loss ? 'Loss: ' . number_format(abs($deal->profit_amount), 2) : 'Profit: ' . number_format($deal->profit_amount, 2) }}
                        </div>
                    @else
                        <div id="pl_rate">Rate: -</div>
                        <div id="pl_sale_in_purchase">Sale in purchase currency: -</div>
                        <div id="pl_profit" class="fw-bold">Profit: -</div>
                    @endif
                    <div id="pl_error" class="text-danger small mt-1" style="display:none;"></div>
                </div>
            </div>
            <div class="col-sm-4 mt-3">
                <label class="form-label">Invoice Number</label>
                {{ html()->text('invoice_number', optional($deal->dealBuyerDetail)->invoice_number)
        ->class('form-control')
        ->id('invoice_number')
        ->attribute('readonly', true) }}
                @error('invoice_number')
                    <span class="error" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="col-sm-4 mt-3">
                <label class="form-label">Invoice Date</label>
                <input type="date" name="invoice_date" id="invoice_date" class="form-control"
                    value="{{ old('invoice_date', optional($deal->dealBuyerDetail)->invoice_date) }}">
                @error('invoice_date')
                    <span class="error" role="alert">{{ $message }}</span>
                @enderror
            </div>
            <div class="col-sm-4 mt-3">
                <label class="form-label">GST Inclusive</label>
                {{ html()->select('gst_type', config('constants.GST_TYPE'), old('gst_type', optional($deal->dealBuyerDetail)->gst_type))
        ->class('form-control')
        ->id('gst_type') }}
                @error('gst_type')
                    <span class="error" role="alert">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>
    <div class="row mt-2" id="buyer_loss_remark_container" style="display:none;">
        <div class="col-sm-12">
            <label class="form-label">Loss Remark <span class="required">*</span></label>
            <textarea name="buyer_loss_remark" id="buyer_loss_remark" rows="3"
                class="form-control">{{ old('buyer_loss_remark', optional($deal->dealBuyerDetail)->buyer_loss_remark ?? '') }}</textarea>
            @error('buyer_loss_remark')
                <span class="error" role="alert">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <h5 class="mt-3 mb-3">Delivery Cost</h5>
        </div>
    </div>
    <div class="form-group row">
        <div class="col-sm-4">
            <label class="form-label">Delivery Cost</label>
            {{ html()->text('delivery_cost')->class('form-control form-control-user float-price number') }}
            @if ($errors->has('delivery_cost'))
                <span class="error" role="alert">{{ $errors->first('delivery_cost') }}</span>
            @endif
        </div>
    </div>
@endif

<div class="form-group row">
    <div class="col-sm-12">
        <label class="form-label">Note</label>
        {{ html()->textarea('note', old('note', $deal->note ?? ''))->class('form-control form-control-user')->rows(3) }}
        @if ($errors->has('note'))
            <span class="error" role="alert">{{ $errors->first('note') }}</span>
        @endif
    </div>
</div>

<div class="form-group row">

    {{ html()->label('Upload Images')->for('images') }}

    <!-- Instruction note for users -->
    <small class="text-muted d-block mb-2">You can upload a maximum of 7 images.</small>

    <!-- File input for multiple image uploads -->
    <input type="file" name="images[]" id="images" class="form-control" multiple accept="image/*">

    <!-- Display the error message for 'images' -->
    @error('images')
        <div class="alert alert-danger mt-2">
            {{ $message }}
        </div>
    @enderror

</div>

@if(isset($deal) && $deal->images->isNotEmpty())
    <div class="mt-3">
        <h4>Uploaded Images</h4>
        <ul class="image-gallery list-unstyled d-flex flex-wrap">
            @foreach($deal->images as $image)
                <li class="image-item position-relative me-3 mb-3" data-image-id="{{ $image->id }}">
                    <!-- Image display -->
                    <img src="{{ asset('storage/deals/' . $deal->id . '/' . $image->document_name) }}" alt="Uploaded Image"
                        class="img-thumbnail" style="width: 150px; height: 150px; object-fit: cover;">

                    <!-- Delete icon positioned at top-right -->
                    <button type="button" class="delete-btn position-absolute top-0 end-0 p-2"
                        onclick="removeImage({{ $image->id }}, '{{ $image->document_name }}')">
                        <i class="fas fa-trash-alt"></i> <!-- Using Font Awesome trash icon -->
                    </button>
                </li>
            @endforeach
        </ul>

        <!-- Hidden input field to store deleted image IDs -->
        <input type="hidden" id="deletedImages" name="deleted_images" value="">
    </div>
@endif


<div class="form-group row">
    <div class="col-sm-4 mt-3">
        <a href="{{route('admin.deals.index')}}" class="btn btn-dark btn-md">Back</a>&nbsp;
        <button type="submit" id="submit_form" class="btn btn-success btn-user btn-md">Submit</button>
    </div>
</div>

@if(isset($deal) && $deal->id)
    <script>
        (function () {
            const dealId = {{ $deal->id }};
            const csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
            const buyerPriceEl = document.getElementById('buyer_sale_price');
            const buyerCurrencyEl = document.getElementById('buyer_currency');
            const invoiceDateEl = document.getElementById('invoice_date');
            const purchasePriceEl = document.getElementById('purchase_price');
            const purchaseCurrencyEl = document.getElementById('purchase_currency');
            const plRateEl = document.getElementById('pl_rate');
            const plSaleInPurchaseEl = document.getElementById('pl_sale_in_purchase');
            const plProfitEl = document.getElementById('pl_profit');
            const remarkContainer = document.getElementById('buyer_loss_remark_container');
            const remarkEl = document.getElementById('buyer_loss_remark');

            if (!buyerPriceEl || !buyerCurrencyEl) return;

            let timeout = null;
            function computePL() {
                const price = buyerPriceEl.value;
                const currency = buyerCurrencyEl.value;
                const invoice_date = invoiceDateEl ? invoiceDateEl.value : null;
                const purchase_price = purchasePriceEl ? purchasePriceEl.value : null;
                const purchase_currency = purchaseCurrencyEl ? purchaseCurrencyEl.value : null;
                if (!price || isNaN(parseFloat(price))) {
                    plRateEl.textContent = 'Rate: -';
                    plSaleInPurchaseEl.textContent = 'Sale in purchase currency: -';
                    plProfitEl.textContent = 'Profit: -';
                    remarkContainer.style.display = 'none';
                    if (remarkEl) remarkEl.removeAttribute('required');
                    return;
                }

                fetch('/admin/deals/' + dealId + '/compute-pl', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ buyer_sale_price: price, buyer_currency: currency, invoice_date: invoice_date, purchase_price: purchase_price, purchase_currency: purchase_currency })
                }).then(r => r.json()).then(data => {
                    if (!data || !data.success) return;
                    plRateEl.textContent = 'Rate: ' + (data.rate ?? '-');
                    plSaleInPurchaseEl.textContent = 'Sale in ' + (data.purchase_currency ?? '') + ': ' + (data.sale_in_purchase_currency ?? '-');
                    plProfitEl.textContent = ((data.profit || data.profit === 0) ? (data.profit < 0 ? 'Loss: ' + Math.abs(data.profit) : 'Profit: ' + data.profit) : 'Profit: -');
                    if (data.is_loss) {
                        remarkContainer.style.display = 'block';
                        if (remarkEl) remarkEl.setAttribute('required', 'required');
                    } else {
                        remarkContainer.style.display = 'none';
                        if (remarkEl) {
                            remarkEl.removeAttribute('required');
                        }
                    }
                }).catch(err => {
                    console.warn('compute-pl failed', err);
                });
            }

            function scheduleCompute() {
                if (timeout) clearTimeout(timeout);
                timeout = setTimeout(computePL, 400);
            }

            buyerPriceEl.addEventListener('input', scheduleCompute);
            buyerCurrencyEl.addEventListener('change', scheduleCompute);
            if (invoiceDateEl) invoiceDateEl.addEventListener('change', scheduleCompute);
            if (purchasePriceEl) purchasePriceEl.addEventListener('input', scheduleCompute);
            if (purchaseCurrencyEl) purchaseCurrencyEl.addEventListener('change', scheduleCompute);

            // Only auto-compute on load if no stored P/L data from DB
            @if(!isset($deal) || $deal->profit_amount === null || $deal->sale_to_purchase_rate === null)
                document.addEventListener('DOMContentLoaded', function () { scheduleCompute(); });
            @endif
            })();
    </script>
@endif

<script>
    (function initVendorLookup() {
        function setup() {
            if (typeof jQuery === 'undefined' || typeof jQuery.fn.autocomplete === 'undefined') {
                setTimeout(setup, 100);
                return;
            }

            const $vendorSearch = jQuery('#vendor_search');
            const $vendorId = jQuery('#vendor_id');
            const $vendorFeedback = jQuery('#vendor_lookup_feedback');
            const $vendorTypeBadge = jQuery('#vendor_type_badge');

            if (!$vendorSearch.length || !$vendorId.length) {
                return;
            }

            function getCustomerType() {
                return jQuery('input[name="customer_type"]:checked').val() || 'individual';
            }

            function parseVendorSuggestion(item) {
                const rawLabel = (item && item.label) ? item.label : '';
                const separator = ' - ';
                const separatorIndex = rawLabel.indexOf(separator);

                if (separatorIndex === -1) {
                    return {
                        name: rawLabel,
                        meta: '',
                    };
                }

                return {
                    name: rawLabel.slice(0, separatorIndex),
                    meta: rawLabel.slice(separatorIndex + separator.length),
                };
            }

            function setVendorFeedback(message, state) {
                $vendorFeedback
                    .removeClass('text-muted text-success text-danger')
                    .addClass(state === 'success' ? 'text-success' : (state === 'error' ? 'text-danger' : 'text-muted'))
                    .text(message || '');
            }

            function refreshVendorTypeBadge() {
                const type = getCustomerType();
                $vendorTypeBadge.text(type);
            }

            function assignValue(selector, value) {
                const $el = jQuery(selector);
                if ($el.length) {
                    $el.val(value || '');
                }
            }

            function applyVendorData(data) {
                if (!data || !data.vendor_type) {
                    return;
                }

                if (data.vendor_type === 'company') {
                    const company = data.company || {};
                    assignValue('input[name="company_name"]', company.company_name);
                    assignValue('input[name="company_email"]', company.company_email);
                    assignValue('input[name="company_mobile"]', company.company_mobile);
                    assignValue('input[name="company_address"]', company.company_address);
                    assignValue('#company_city', company.company_city);
                    assignValue('#company_state', company.company_state);
                    assignValue('#company_country', company.company_country);
                    assignValue('input[name="company_zip_code"]', company.company_zip_code);
                    assignValue('input[name="abn_number"]', company.abn_number);
                    assignValue('input[name="director_name"]', company.director_name);
                    assignValue('input[name="dealer_licence_number"]', company.dealer_licence_number);
                } else {
                    const individual = data.individual || {};
                    assignValue('input[name="first_name"]', individual.first_name);
                    assignValue('input[name="last_name"]', individual.last_name);
                    assignValue('input[name="email"]', individual.email);
                    assignValue('input[name="mobile"]', individual.mobile);
                    assignValue('input[name="address"]', individual.address);
                    assignValue('#city', individual.city);
                    assignValue('#state', individual.state);
                    assignValue('#country', individual.country);
                    assignValue('input[name="zipcode"]', individual.zipcode);
                }

                setVendorFeedback('Vendor details loaded. You can still edit any field.', 'success');
            }

            function loadVendorDetails(vendorId, applyToForm) {
                if (!vendorId) {
                    return;
                }

                jQuery.ajax({
                    url: "{{ route('admin.deal.vendor.details') }}",
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        vendor_id: vendorId,
                        customer_type: getCustomerType(),
                    },
                    success: function (response) {
                        if (response && response.display_name) {
                            $vendorSearch.val(response.display_name);
                        }

                        if (applyToForm) {
                            applyVendorData(response);
                        }
                    },
                    error: function () {
                        setVendorFeedback('Unable to load vendor details for selected type.', 'error');
                    }
                });
            }

            function clearVendorSelection(clearSearch) {
                $vendorId.val('');
                setVendorFeedback('', 'default');
                if (clearSearch) {
                    $vendorSearch.val('');
                }
            }

            $vendorSearch.autocomplete({
                source: function (request, response) {
                    jQuery.ajax({
                        url: "{{ route('admin.deal.vendor.autocomplete') }}",
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            term: request.term,
                            customer_type: getCustomerType(),
                        },
                        success: function (data) {
                            const items = (data || []).map(function (item) {
                                const parsed = parseVendorSuggestion(item);
                                return Object.assign({}, item, {
                                    name: parsed.name,
                                    meta: parsed.meta,
                                });
                            });

                            response(items);
                        }
                    });
                },
                minLength: 1,
                delay: 120,
                autoFocus: true,
                focus: function (event, ui) {
                    $vendorSearch.val(ui.item.name || ui.item.label || '');
                    return false;
                },
                select: function (event, ui) {
                    $vendorId.val(ui.item.value);
                    $vendorSearch.val(ui.item.name || ui.item.label || '');
                    loadVendorDetails(ui.item.value, true);
                    return false;
                },
                change: function (event, ui) {
                    if (!ui.item && !$vendorSearch.val().trim()) {
                        clearVendorSelection(false);
                    }
                },
                open: function () {
                    const $menu = $vendorSearch.autocomplete('widget');
                    $menu.addClass('vendor-suggest-menu');
                    $menu.outerWidth($vendorSearch.outerWidth());
                }
            });

            $vendorSearch.autocomplete('instance')._renderItem = function (ul, item) {
                const name = item.name || item.label || '';
                const meta = item.meta || '';
                const safeName = jQuery('<div>').text(name).html();
                const safeMeta = jQuery('<div>').text(meta).html();

                return jQuery('<li>')
                    .append(
                        '<div class="ui-menu-item-wrapper">'
                        + '<span class="vendor-suggest-name">' + safeName + '</span>'
                        + (safeMeta ? '<div class="vendor-suggest-meta">' + safeMeta + '</div>' : '')
                        + '</div>'
                    )
                    .appendTo(ul);
            };

            jQuery('#clear_vendor_selection').on('click', function () {
                clearVendorSelection(true);
            });

            jQuery('input[name="customer_type"]').on('change', function () {
                clearVendorSelection(true);
                refreshVendorTypeBadge();
            });

            refreshVendorTypeBadge();

            if ($vendorId.val()) {
                loadVendorDetails($vendorId.val(), false);
            }
        }

        setup();
    })();
</script>

<script>
    (function initCustomerLookup() {
        function setup() {
            if (typeof jQuery === 'undefined' || typeof jQuery.fn.autocomplete === 'undefined') {
                setTimeout(setup, 100);
                return;
            }

            const $customerSearch = jQuery('#customer_search');
            const $customerLookupId = jQuery('#customer_id_lookup');
            const $customerFeedback = jQuery('#customer_lookup_feedback');

            if (!$customerSearch.length) {
                return;
            }

            function setCustomerFeedback(message, state) {
                $customerFeedback
                    .removeClass('text-muted text-success text-danger')
                    .addClass(state === 'success' ? 'text-success' : (state === 'error' ? 'text-danger' : 'text-muted'))
                    .text(message || '');
            }

            function assignValue(selector, value) {
                const $el = jQuery(selector);
                if ($el.length) {
                    $el.val(value || '');
                }
            }

            function parseCustomerSuggestion(item) {
                const rawLabel = (item && item.label) ? item.label : '';
                const separator = ' - ';
                const separatorIndex = rawLabel.indexOf(separator);

                if (separatorIndex === -1) {
                    return {
                        name: rawLabel,
                        meta: '',
                    };
                }

                return {
                    name: rawLabel.slice(0, separatorIndex),
                    meta: rawLabel.slice(separatorIndex + separator.length),
                };
            }

            function applyCustomerData(data) {
                if (!data || !data.buyer) {
                    return;
                }

                const buyer = data.buyer;
                assignValue('#buyer_name', buyer.buyer_name);
                assignValue('#buyer_email', buyer.buyer_email);
                assignValue('input[name="buyer_address"]', buyer.buyer_address);
                assignValue('#buyer_city', buyer.buyer_city);
                assignValue('#buyer_state', buyer.buyer_state);
                assignValue('#buyer_country', buyer.buyer_country);
                assignValue('input[name="buyer_zipcode"]', buyer.buyer_zipcode);

                setCustomerFeedback('Customer details loaded. You can still edit any buyer field.', 'success');
            }

            function loadCustomerDetails(customerId) {
                if (!customerId) {
                    return;
                }

                jQuery.ajax({
                    url: "{{ route('admin.deal.customer.details') }}",
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        customer_id: customerId,
                    },
                    success: function (response) {
                        if (response && response.display_name) {
                            $customerSearch.val(response.display_name);
                        }
                        applyCustomerData(response);
                    },
                    error: function () {
                        setCustomerFeedback('Unable to load customer details.', 'error');
                    }
                });
            }

            function clearCustomerSelection(clearSearch) {
                $customerLookupId.val('');
                if (clearSearch) {
                    $customerSearch.val('');
                }
                setCustomerFeedback('', 'default');
            }

            $customerSearch.autocomplete({
                source: function (request, response) {
                    jQuery.ajax({
                        url: "{{ route('admin.deal.customer.autocomplete') }}",
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            term: request.term,
                        },
                        success: function (data) {
                            const items = (data || []).map(function (item) {
                                const parsed = parseCustomerSuggestion(item);
                                return Object.assign({}, item, {
                                    name: parsed.name,
                                    meta: parsed.meta,
                                });
                            });

                            response(items);
                        }
                    });
                },
                minLength: 1,
                delay: 120,
                autoFocus: true,
                focus: function (event, ui) {
                    $customerSearch.val(ui.item.name || ui.item.label || '');
                    return false;
                },
                select: function (event, ui) {
                    $customerLookupId.val(ui.item.value);
                    $customerSearch.val(ui.item.name || ui.item.label || '');
                    loadCustomerDetails(ui.item.value);
                    return false;
                },
                change: function (event, ui) {
                    if (!ui.item && !$customerSearch.val().trim()) {
                        clearCustomerSelection(false);
                    }
                },
                open: function () {
                    const $menu = $customerSearch.autocomplete('widget');
                    $menu.addClass('vendor-suggest-menu');
                    $menu.outerWidth($customerSearch.outerWidth());
                }
            });

            $customerSearch.autocomplete('instance')._renderItem = function (ul, item) {
                const name = item.name || item.label || '';
                const meta = item.meta || '';
                const safeName = jQuery('<div>').text(name).html();
                const safeMeta = jQuery('<div>').text(meta).html();

                return jQuery('<li>')
                    .append(
                        '<div class="ui-menu-item-wrapper">'
                        + '<span class="vendor-suggest-name">' + safeName + '</span>'
                        + (safeMeta ? '<div class="vendor-suggest-meta">' + safeMeta + '</div>' : '')
                        + '</div>'
                    )
                    .appendTo(ul);
            };

            jQuery('#clear_customer_selection').on('click', function () {
                clearCustomerSelection(true);
            });

            if ($customerLookupId.val()) {
                loadCustomerDetails($customerLookupId.val());
            }
        }

        setup();
    })();
</script>

<script>
    (function waitForValidate() {
        if (typeof jQuery === 'undefined' || typeof jQuery.fn.validate === 'undefined') { setTimeout(waitForValidate, 100); return; }
        jQuery('.validatedForm').validate({
        rules: {
            model_number: {
                required: true
            },
            serial_number: {
                required: true
            },
            material_watch: {
                required: true
            },
            condition: {
                required: true
            },
            year: {
                required: true
            },
            full_set: {
                required: true
            },
            purchase_price: {
                required: true
            },
            purchase_currency: {
                required: true
            },
            sale_price: {
                required: true
            },
            brand_id: {
                required: true
            },
            gst_code: {
                required: true
            },
            deal_supplier_status: {
                required: true
            },
            purchase_invoice_number: {
                required: true
            },
            purchase_invoice_date: {
                required: true
            },
            dial: {
                required: true
            },
            customer_type: {
                required: true
            },
            first_name: {
                required: function() {
                    return $('input[name="customer_type"]:checked').val() === 'individual';
                }
            },
            last_name: {
                required: function() {
                    return $('input[name="customer_type"]:checked').val() === 'individual';
                }
            },
            email: {
                required: function() {
                    return $('input[name="customer_type"]:checked').val() === 'individual';
                },
                email: function() {
                    return $('input[name="customer_type"]:checked').val() === 'individual';
                }
            },
            mobile: {
                required: function() {
                    return $('input[name="customer_type"]:checked').val() === 'individual';
                }
            },
            address: {
                required: function() {
                    return $('input[name="customer_type"]:checked').val() === 'individual';
                }
            },
            city: {
                required: function() {
                    return $('input[name="customer_type"]:checked').val() === 'individual';
                }
            },
            state: {
                required: function() {
                    return $('input[name="customer_type"]:checked').val() === 'individual';
                }
            },
            country: {
                required: function() {
                    return $('input[name="customer_type"]:checked').val() === 'individual';
                }
            },
            zipcode: {
                required: function() {
                    return $('input[name="customer_type"]:checked').val() === 'individual';
                }
            },
            company_name: {
                required: function() {
                    return $('input[name="customer_type"]:checked').val() === 'company';
                }
            },
            company_email: {
                required: function() {
                    return $('input[name="customer_type"]:checked').val() === 'company';
                },
                email: function() {
                    return $('input[name="customer_type"]:checked').val() === 'company';
                }
            },
            company_mobile: {
                required: function() {
                    return $('input[name="customer_type"]:checked').val() === 'company';
                }
            },
            company_address: {
                required: function() {
                    return $('input[name="customer_type"]:checked').val() === 'company';
                }
            },
            company_city: {
                required: function() {
                    return $('input[name="customer_type"]:checked').val() === 'company';
                }
            },
            company_state: {
                required: function() {
                    return $('input[name="customer_type"]:checked').val() === 'company';
                }
            },
            company_country: {
                required: function() {
                    return $('input[name="customer_type"]:checked').val() === 'company';
                }
            },
            company_zip_code: {
                required: function() {
                    return $('input[name="customer_type"]:checked').val() === 'company';
                }
            },
            abn_number: {
                required: function() {
                    return $('input[name="customer_type"]:checked').val() === 'company';
                }
            },
            director_name: {
                required: function() {
                    return $('input[name="customer_type"]:checked').val() === 'company';
                }
            },
            dealer_licence_number: {
                required: function() {
                    return $('input[name="customer_type"]:checked').val() === 'company';
                }
            },
            deal_status: {
                required: true
            },
            status: {
                required: true
            }
        },
        messages: {
            model_number: "Model number is required",
            serial_number: "Serial number is required",
            material_watch: "Reference number is required",
            condition: "Condition is required",
            year: "Year is required",
            full_set: "Full set status is required",
            purchase_price: "Purchase price is required",
            purchase_currency: "Purchase currency is required",
            sale_price: "Estimated sale price is required",
            brand_id: "Brand is required",
            gst_code: "GST code is required",
            deal_supplier_status: "Deal supplier status is required",
            purchase_invoice_number: "Invoice number is required",
            purchase_invoice_date: "Invoice date is required",
            dial: "Dial is required",
            customer_type: "Please select a customer type",
            first_name: "First name is required",
            last_name: "Last name is required",
            email: "A valid email is required",
            mobile: "Mobile number is required",
            address: "Address is required",
            city: "City is required",
            state: "State is required",
            country: "Country is required",
            zipcode: "Zipcode is required",
            company_name: "Company name is required",
            company_email: "A valid company email is required",
            company_mobile: "Company mobile is required",
            company_address: "Company address is required",
            company_city: "Company city is required",
            company_state: "Company state is required",
            company_country: "Company country is required",
            company_zip_code: "Company zipcode is required",
            abn_number: "ABN number is required",
            director_name: "Director name is required",
            dealer_licence_number: "Dealer licence number is required",
            deal_status: "Deal status is required",
            status: "Status is required"
        }
    });
    })();
</script>

