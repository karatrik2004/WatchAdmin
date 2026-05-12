@extends('layouts.admin')
@section('title')
    Edit Deal
@endsection
@section('inline-css')
    <style>
        .image-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .image-item {
            position: relative;
            width: 150px;  /* Fixed size for images */
            height: 150px;
            display: inline-block;
            overflow: hidden;
            border-radius: 8px;
        }

        .delete-btn {
            background: rgba(255, 255, 255, 0.7);
            border: none;
            border-radius: 50%;
            padding: 5px;
            cursor: pointer;
            z-index: 1;
        }

        .delete-btn i {
            color: #ff4d4d;
            font-size: 18px;
        }

        .delete-btn:hover {
            background-color: rgba(255, 255, 255, 1);
        }

        /* Ensure images are contained nicely inside their container */
        .img-thumbnail {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-item:hover .delete-btn {
            display: block;
        }

    </style>
@endsection
@section('content')
    <!-- Page Heading -->

    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <h4>Update Deal</h4>
                    </div>
                    <!-- Invoice PDF buttons moved to view page -->
                </div>
            </div>

            <div class="card-body">

                {{ html()->modelForm($deal, 'PATCH', route('admin.deals.update', $deal->id))
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
                src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDlih8YoivOQttZo8FngvranY97IhFJIeM&libraries=places"
                async
                defer>
            </script>
    <script>
        jQuery('.validatedForm').validate();
        initBrandAutocomplete($("#brand_name"));
        let brandId = $('#brand_id').val();

        if (brandId) {
            $.ajax({
                url: "{{ route('admin.deal.brand.name') }}",
                type: "GET",
                data: { id: brandId },
                success: function (data) {
                    $('#brand_name').val(data.name);
                },
                error: function () {
                    console.log('Failed to load brand name');
                }
            });
        }

        // Initialize autocomplete
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
    <script>
        $(document).ready(function () {
            function loadCities(stateId, selectedCity = null) {
                if (stateId) {
                    $.ajax({
                        url: '{{ route("admin.get-cities", ":stateId") }}'.replace(':stateId', stateId),
                        type: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            $('#buyer_city_id').empty().append('<option value="">Select City</option>');
                            $.each(data, function (key, value) {
                                let isSelected = selectedCity && key == selectedCity ? 'selected' : '';
                                $('#buyer_city_id').append('<option value="' + key + '" ' + isSelected + '>' + value + '</option>');
                            });
                        }
                    });
                } else {
                    $('#buyer_city_id').empty().append('<option value="">Select City</option>');
                }
            }

            // On state change, load cities
            $('#buyer_state_id').on('change', function () {
                loadCities($(this).val());
            });

            // For edit page, load cities if a state is pre-selected
            let selectedState = $('#buyer_state_id').val();

            let selectedCity = "{{ isset($deal->dealBuyerDetail) && isset($deal->dealBuyerDetail->buyer_city_id) ? $deal->dealBuyerDetail->buyer_city_id : '' }}";
            if (selectedState) {
                loadCities(selectedState, selectedCity);
            }
        });
        $(document).ready(function () {
            function toggleBuyerDetails() {
                var dealStatus = $('#deal_status').val();
                if (dealStatus == 3) {
                    $('#dealSoldOutDiv').show();
                    $('#buyer_name, #buyer_email, #buyer_country_id, #buyer_state_id, #buyer_city_id, #buyer_address, #buyer_zipcode, #buyer_sale_price, #invoice_number, #invoice_date')
                        .prop('disabled', false);
                } else {
                    $('#dealSoldOutDiv').hide();
                    $('#buyer_name, #buyer_email, #buyer_country_id, #buyer_state_id, #buyer_city_id, #buyer_address, #buyer_zipcode, #buyer_sale_price, #invoice_number, #invoice_date')
                        .prop('disabled', true);
                }
            }

            // Run on page load (for edit page)
            toggleBuyerDetails();

            // Run on change event (when user selects a new status)
            $(document).on("change", "#deal_status", function () {
                toggleBuyerDetails();
            });
        });
    </script>
    <script>
        function removeInvoice() {
            // Hide the current invoice and show the file input
            document.getElementById('invoice-input-container').innerHTML = `
            <input type="file" name="invoice_file" class="form-control" required>
        `;
        }

        function removeImage(imageId, imageName) {
            // Remove the image from the display
            var imageItem = document.querySelector(`li[data-image-id="${imageId}"]`);
            imageItem.remove();

            // Get the current value of the hidden input field
            var deletedImages = document.getElementById('deletedImages').value;

            // If there are already deleted images, append the new one, otherwise initialize the array
            deletedImages = deletedImages ? deletedImages.split(',') : [];

            // Add the image ID (or name) to the array of deleted images
            deletedImages.push(imageId);

            // Update the hidden input field with the new list of deleted image IDs
            document.getElementById('deletedImages').value = deletedImages.join(',');
        }

        $(document).ready(function () {
            function loadCompanyCities(stateId, selectedCity = null) {
                if (stateId) {
                    $.ajax({
                        url: '{{ route("admin.get-cities", ":stateId") }}'.replace(':stateId', stateId),
                        type: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            $('#company_city_id').empty().append('<option value="">Select City</option>');
                            $.each(data, function (key, value) {
                                let isSelected = selectedCity && key == selectedCity ? 'selected' : '';
                                $('#company_city_id').append('<option value="' + key + '" ' + isSelected + '>' + value + '</option>');
                            });
                        }
                    });
                } else {
                    $('#company_city_id').empty().append('<option value="">Select City</option>');
                }
            }

            // On state change, load cities
            $('#company_state_id').on('change', function () {
                loadCompanyCities($(this).val());
            });

            // For edit page, load cities if a state is pre-selected
            let selectedCompanyState = $('#company_state_id').val();

            let selectedCompanyCity = "{{ isset($deal->dealCustomerTypeDetail) && isset($deal->dealCustomerTypeDetail->company_city_id) ? $deal->dealCustomerTypeDetail->company_city_id : '' }}";
            if (selectedCompanyCity) {
                loadCompanyCities(selectedCompanyState, selectedCompanyCity);
            }

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
            <!-- JavaScript for Image Previews -->
            <script>
                function previewImages() {
                    const files = document.getElementById('images').files;
                    const previewContainer = document.getElementById('image-previews');
                    previewContainer.innerHTML = '';  // Clear any previous previews

                    // Limit the number of images to 7
                    const imageLimit = 7;
                    const totalImages = files.length > imageLimit ? imageLimit : files.length;

                    for (let i = 0; i < totalImages; i++) {
                        const file = files[i];
                        const reader = new FileReader();

                        reader.onload = function (e) {
                            const imagePreview = document.createElement('div');
                            imagePreview.classList.add('image-preview');

                            const imgElement = document.createElement('img');
                            imgElement.src = e.target.result;

                            const removeButton = document.createElement('button');
                            removeButton.classList.add('remove-preview');
                            removeButton.innerHTML = '×';
                            removeButton.onclick = function () {
                                removePreview(imagePreview, file);
                            };

                            imagePreview.appendChild(imgElement);
                            imagePreview.appendChild(removeButton);
                            previewContainer.appendChild(imagePreview);
                        }

                        reader.readAsDataURL(file);
                    }

                    // Show an empty preview container message if no files are selected
                    if (files.length === 0) {
                        previewContainer.classList.add('empty');
                    } else {
                        previewContainer.classList.remove('empty');
                    }
                }

                function removePreview(preview, file) {
                    // Remove the selected image preview from the UI
                    preview.remove();

                    // Remove the file from the input
                    const input = document.getElementById('images');
                    const dt = new DataTransfer();  // Create a new DataTransfer object
                    const files = Array.from(input.files);

                    files.splice(files.indexOf(file), 1);  // Remove the file from the array
                    files.forEach(file => dt.items.add(file));  // Add the remaining files to DataTransfer

                    input.files = dt.files;  // Update the input field with the new files
                }
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

            </script>
            <script>
                function initCompanyAutocomplete() {
                    // Select the address input field
                    const companyAddressField = document.getElementById('company_address');
                    let cityFound = false; // To track if city is found

                    // Create the autocomplete object
                    const autoCompanycomplete = new google.maps.places.Autocomplete(companyAddressField, {
                        types: ['address'], // Restrict to street-level addresses
                        componentRestrictions: {country: ['in', 'us']} // Restrict to specific countries (India and US)
                    });

                    // Listen for the place_changed event when a user selects a suggestion
                    autoCompanycomplete.addListener('place_changed', function () {
                        const place = autoCompanycomplete.getPlace();

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

            </script>
            <script>
                function initBuyerAutocomplete() {
                    // Select the address input field
                    const buyerAddressField = document.getElementById('buyer_address');
                    let cityFound = false; // To track if city is found

                    // Create the autocomplete object
                    const autoBuyercomplete = new google.maps.places.Autocomplete(buyerAddressField, {
                        types: ['address'], // Restrict to street-level addresses
                        componentRestrictions: {country: ['in', 'us']} // Restrict to specific countries (India and US)
                    });

                    // Listen for the place_changed event when a user selects a suggestion
                    autoBuyercomplete.addListener('place_changed', function () {
                        const place = autoBuyercomplete.getPlace();

                        // Clear all fields if no address is entered or selected
                        if (!buyerAddressField.value.trim() || !place.geometry) {
                            clearBuyerFields();
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
                            buyerAddressField.value = streetAddress.trim();

                            if (types.includes('locality')) { // City
                                document.getElementById('buyer_city').value = component.long_name;
                                cityFound = true;
                            }

                            if (types.includes('administrative_area_level_1')) { // State
                                // document.getElementById('state').value = component.short_name;
                                $('#buyer_state').val(component.short_name).trigger('change'); // Set to
                            }

                            if (types.includes('postal_code')) { // Zip code
                                document.getElementById('buyer_zipcode').value = component.long_name;
                            }
                            if (types.includes('country')) { // Country
                                document.getElementById('buyer_country').value = component.long_name; // Populate the country field
                            }
                            // Fallback for city if locality is not available
                            if (!cityFound && types.includes('sublocality')) { // Sub-locality (another fallback for city)
                                document.getElementById('buyer_city').value = component.long_name;
                            }
                        });
                    });

                    // Add an event listener to clear fields when the address input is empty
                    buyerAddressField.addEventListener('input', function () {
                        if (!buyerAddressField.value.trim()) {
                            clearBuyerFields();
                        }
                    });

                    // Function to clear all related fields
                    function clearBuyerFields() {
                        document.getElementById('buyer_address').value = '';
                        document.getElementById('buyer_country').value = '';
                        document.getElementById('buyer_city').value = '';
                        document.getElementById('buyer_state').value = '';
                        document.getElementById('buyer_zipcode').value = '';
                    }
                }


                window.onload = function () {

                    initAutocomplete();
                    initBuyerAutocomplete();
                    initCompanyAutocomplete();
                };

            </script>
@endsection
