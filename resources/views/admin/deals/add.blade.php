@extends('layouts.admin')
@section('title') Add Deal @endsection
@section('inline-css')
    <style>
        .image-list { list-style: none; padding: 0; }
        .image-item { display: inline-block; margin: 10px; position: relative; }
        .image-item img { width: 100px; height: 100px; object-fit: cover; }
        .delete-btn { position: absolute; top: 5px; right: 5px; background: red; color: white; border: none; border-radius: 50%; width: 20px; height: 20px; cursor: pointer; }
    </style>
@endsection
@section('content')
<div class="col-md-12">
		<div class="card">
			<div class="card-header">
				<h5>Create Deal</h5>
			</div>
			<div class="card-body">
                {{ html()->form('POST', route('admin.deals.store'))
        ->class('validatedForm')
        ->id('deal_form')
        ->attribute('enctype', 'multipart/form-data') // Add enctype attribute
        ->open() }}

                {{ csrf_field() }}
					@include('includes.admin.deal.form')
				{{ html()->form()->close() }}
		</div>
	</div>
@endsection

@section('inline-js')
        <script
            src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDlih8YoivOQttZo8FngvranY97IhFJIeM&libraries=places&callback=initAutocomplete"
            async
            defer>
        </script>

<script>
    jQuery('.validatedForm').validate();
</script>
<script>
    initBrandAutocomplete($("#brand_name"));
    function initBrandAutocomplete(inputField) {
        $(inputField).autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "{{ route('admin.deal.brand.autocomplete') }}",
                    dataType: "json",
                    data: {
                        term: request.term
                    },
                    beforeSend: function() {
                        $('#loader').show();
                    },
                    success: function(data) {
                        response($.map(data, function(item) {
                            return {
                                label: item.label,
                                value: item.value
                            };
                        }));
                    },
                    complete: function() {
                        $('#loader').hide();
                    }
                });
            },
            minLength: 2,
            select: function(event, ui) {
                $("#brand_name").val(ui.item.label);
                $("#brand_id").val(ui.item.value);
                return false;
            },
            response: function(event, ui) {
                if (ui.content.length === 0) {
                    console.log('No brand suggestions found');
                }
            },
            change: function(event, ui) {
                const term = $(this).val().toLowerCase();
                let found = false;

                $(this).autocomplete("widget").find("div").each(function() {
                    if ($(this).text().toLowerCase() === term) {
                        found = true;
                        return false;
                    }
                });

                if (!found) {
                    $("#brand_name").val('');
                    $("#brand_id").val('');
                }
            },
            create: function() {
                $(this).data("ui-autocomplete")._renderItem = function(ul, item) {
                    return $("<li>")
                        .append("<div>" + item.label + "</div>")
                        .appendTo(ul);
                };
            }
        });
    }


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
        $('#company_state_id').on('change', function () {
            var stateId = $(this).val();
            if (stateId) {
                $.ajax({
                    url: '{{ route("admin.get-cities", ":stateId") }}'.replace(':stateId', stateId),
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        $('#company_city_id').empty().append('<option value="">Select City</option>');
                        $.each(data, function (key, value) {
                            $('#company_city_id').append('<option value="' + key + '">' + value + '</option>');
                        });
                    }
                });
            } else {
                $('#company_city_id').empty().append('<option value="">Select City</option>');
            }
        });

    });
</script>
<script>
    $(document).ready(function () {
        function toggleFields() {
            if ($('input[name="customer_type"]:checked').val() === 'individual') {
                $('#individual-fields').show();
                $('#company-fields').hide();
                $('.individual-field').show().prop('disabled', false).prop('required', false); // Enable and set required for individual fields
                $('.company-field').hide().prop('disabled', true).val('').prop('required', false); // Disable company fields and remove required
            } else {
                $('#company-fields').show();
                $('#individual-fields').hide();
                $('.individual-field').hide().prop('disabled', true).val('').prop('required', false); // Disable individual fields and remove required
                $('.company-field').show().prop('disabled', false).prop('required', false); // Enable company fields and set required
            }
        }


        $('input[name="customer_type"]').change(toggleFields);

        // Run toggleFields() on page load to apply correct state
        toggleFields();
    });
</script>
<script>
    function initAutocomplete() {
        // Select the address input field
        const addressField = document.getElementById('address');
        let cityFound = false; // To track if city is found

        // Create the autocomplete object
        const autocomplete = new google.maps.places.Autocomplete(addressField, {
            types: ['address'], // Restrict to street-level addresses
            componentRestrictions: {country: ['in', 'us']} // Restrict to specific countries (India and US)
        });

        // Listen for the place_changed event when a user selects a suggestion
        autocomplete.addListener('place_changed', function () {
            const place = autocomplete.getPlace();

            // Clear all fields if no address is entered or selected
            if (!addressField.value.trim() || !place.geometry) {
                clearFields();
                return;
            }

            // Extract street address components (street number and route)
            let streetAddress = '';
            cityFound = false; // Reset cityFound for every new selection

            // Fill in the city, state, and zip fields based on the selected address
            const addressComponents = place.address_components;
            addressComponents.forEach(component => {
                const types = component.types;

                if (types.includes('street_number')) { // Street number
                    streetAddress += component.long_name + ' ';
                }

                if (types.includes('route')) { // Street name
                    streetAddress += component.long_name;
                }
                addressField.value = streetAddress.trim();

                if (types.includes('locality')) { // City
                    document.getElementById('city').value = component.long_name;
                    cityFound = true;
                }

                if (types.includes('administrative_area_level_1')) { // State
                    // document.getElementById('state').value = component.short_name;
                    $('#state').val(component.short_name).trigger('change'); // Set to
                }

                if (types.includes('postal_code')) { // Zip code
                    document.getElementById('zipcode').value = component.long_name;
                }
                if (types.includes('country')) { // Country
                    document.getElementById('country').value = component.long_name; // Populate the country field
                }
                // Fallback for city if locality is not available
                if (!cityFound && types.includes('sublocality')) { // Sub-locality (another fallback for city)
                    document.getElementById('city').value = component.long_name;
                }
            });
        });

        // Add an event listener to clear fields when the address input is empty
        addressField.addEventListener('input', function () {
            if (!addressField.value.trim()) {
                clearFields();
            }
        });

        // Function to clear all related fields
        function clearFields() {
            document.getElementById('address').value = '';
            document.getElementById('country').value = '';
            document.getElementById('city').value = '';
            document.getElementById('state').value = '';
            document.getElementById('zipcode').value = '';
        }
    }
    function initCompanyAutocomplete() {
        // Select the address input field
        const companyAddressField = document.getElementById('company_address');
        let cityFound = false; // To track if city is found

        // Create the autocomplete object
        const autocomplete = new google.maps.places.Autocomplete(companyAddressField, {
            types: ['address'], // Restrict to street-level addresses
            componentRestrictions: {country: ['in', 'us']} // Restrict to specific countries (India and US)
        });

        // Listen for the place_changed event when a user selects a suggestion
        autocomplete.addListener('place_changed', function () {
            const place = autocomplete.getPlace();

            // Clear all fields if no address is entered or selected
            if (!companyAddressField.value.trim() || !place.geometry) {
                clearCompanyFields();
                return;
            }

            // Extract street address components (street number and route)
            let streetAddress = '';
            cityFound = false; // Reset cityFound for every new selection

            // Fill in the city, state, and zip fields based on the selected address
            const addressComponents = place.address_components;
            addressComponents.forEach(component => {
                const types = component.types;

                if (types.includes('street_number')) { // Street number
                    streetAddress += component.long_name + ' ';
                }

                if (types.includes('route')) { // Street name
                    streetAddress += component.long_name;
                }
                companyAddressField.value = streetAddress.trim();

                if (types.includes('locality')) { // City
                    document.getElementById('company_city').value = component.long_name;
                    cityFound = true;
                }

                if (types.includes('administrative_area_level_1')) { // State
                    // document.getElementById('state').value = component.short_name;
                    $('#company_state').val(component.short_name).trigger('change'); // Set to
                }

                if (types.includes('postal_code')) { // Zip code
                    document.getElementById('company_zip_code').value = component.long_name;
                }
                if (types.includes('country')) { // Country
                    document.getElementById('company_country').value = component.long_name; // Populate the country field
                }
                // Fallback for city if locality is not available
                if (!cityFound && types.includes('sublocality')) { // Sub-locality (another fallback for city)
                    document.getElementById('company_city').value = component.long_name;
                }
            });
        });

        // Add an event listener to clear fields when the address input is empty
        companyAddressField.addEventListener('input', function () {
            if (!companyAddressField.value.trim()) {
                clearCompanyFields();
            }
        });

        // Function to clear all related fields
        function clearCompanyFields() {
            document.getElementById('company_address').value = '';
            document.getElementById('company_country').value = '';
            document.getElementById('company_city').value = '';
            document.getElementById('company_state').value = '';
            document.getElementById('company_zip_code').value = '';
        }
    }
    window.onload = function () {
        initAutocomplete();
        initCompanyAutocomplete();
    };
</script>
@endsection
