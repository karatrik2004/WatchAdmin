<div class="modal fade" id="dealApproveModel" tabindex="-1" aria-labelledby="dealApproveModelLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dealApproveModelLabel">Approve Deal
                    # {{ $dealDetails->id }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="messageContainer"></div>

                <form id="dealApprovedForm" method="post">

                    <input type="hidden" name="deal_id" value="{{ $dealDetails->id }}">

                    <div class="mb-3">
                        <label for="note" class="form-label">Deal Approve Note</label>
                        <textarea
                            class="form-control"
                            id="note"
                            name="note"
                            placeholder="Enter the deal approve note"
                        ></textarea>
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <div>
                            <button type="submit" class="btn btn-primary" id="submitDealBtn">Submit</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).on('submit', '#dealApprovedForm', function (e) {
        e.preventDefault(); // Prevent default form submission
        var historyId = "{{ $dealDetails->id }}";
        const form = $(this);
        const url = `{{ route('admin.deals.approve', ':id') }}`.replace(':id', historyId);
        let actionKey = ''; // Default key
        let clickedButton = document.activeElement; // Get the clicked button
        if (clickedButton.id === "submitDealBtn") {
            actionKey = "submit"; // Key for normal submission
        }
        let formData = form.serialize() + '&key=' + actionKey;
        $('#messageContainer').html('<div class="alert alert-info">Please wait...</div>')
        // Disable the submit button to prevent duplicate submissions
        /*form.find('button[type="submit"]').prop('disabled', true).text('Submitting...');*/
        // Disable the clicked button only and change its text
        $(clickedButton).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        $(clickedButton).data('original-text', $(clickedButton).html());
        // Set up CSRF token in the headers
        const token = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': token // Attach CSRF token to the header
            },
            success: function (response) {
                // Assuming the response contains a success status
                if (response.success) {
                    // Show a success message
                    $('#messageContainer').html('<div class="alert alert-success">Deal Approved successfully!</div>');
                    // Reload the page after 3 seconds
                    setTimeout(function () {
                        location.reload();
                    }, 2000); // 3000ms = 3 seconds

                    /*$('#dealApproveModel').modal('hide');*/
                } else {
                    // Show an error message if something goes wrong
                    $('#messageContainer').html('<div class="alert alert-danger">Something went wrong. Please try again.</div>');
                }
            },
            error: function (xhr, status, error) {
                console.log(xhr.responseText); // Log the full response text for debugging

                // Handle validation errors (422 status code)
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let errorMsg = '<ul>';

                    $.each(errors, function (key, errorMessages) {
                        errorMessages.forEach(function (message) {
                            errorMsg += `<li>${message}</li>`;
                        });
                    });

                    errorMsg += '</ul>';
                    $('#messageContainer').html('<div class="alert alert-danger">Validation Errors: ' + errorMsg + '</div>');
                } else if (xhr.status === 500) {
                    // Handle server errors
                    $('#messageContainer').html('<div class="alert alert-danger">Server Error: Please try again later.</div>');
                } else {
                    // Handle other types of errors
                    $('#messageContainer').html('<div class="alert alert-danger">Something went wrong. Please try again.</div>');
                }
            },
            complete: function () {
                // Re-enable the clicked button after completion and restore its original text
                // $(clickedButton).prop('disabled', false).html(clickedButton.dataset.originalText || 'Submit');
                $(clickedButton).prop('disabled', false).html('Submit');
            }
        });
    });
</script>


