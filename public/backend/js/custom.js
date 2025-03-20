function showSuccessMessageTopRight(msg) {
    noty({
        layout: 'topRight',
        theme: 'noty_theme_default',
        type: 'success',
        text: msg,
        timeout: 10000,
        closeButton: true,
        animation: {
            easing: 'swing',
            speed: 150 // opening & closing animation speed
        }
    });
}

function showErrorMessageTopRight(msg) {
    noty({
        layout: 'topRight',
        theme: 'noty_theme_default',
        type: 'error',
        text: msg,
        timeout: 10000,
        closeButton: true,
        animation: {
            easing: 'swing',
            speed: 150 // opening & closing animation speed
        }
    });
}


$(document).ready(function () {
    $(".phoneno").keypress(function (e) {
        var length = $(this).val().length;
        if (length > 15) {
            return false;
        } else if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
            return false;
        } else if ((length == 0) && (e.which == 48)) {
            return false;
        }
    });

    $(".float-price").keypress(function (e) {
        let charCode = e.which;
        let value = $(this).val();
        let length = value.length;

        // Allow only digits (48-57), dot (46), and backspace (8)
        if (charCode !== 8 && charCode !== 0 && (charCode < 48 || charCode > 57) && charCode !== 46) {
            return false;
        }

        // Prevent multiple dots (.)
        if (charCode === 46 && value.includes(".")) {
            return false;
        }

        // Limit to two decimal places
        if (value.includes(".")) {
            let decimalPart = value.split(".")[1];
            if (decimalPart && decimalPart.length >= 2) {
                return false;
            }
        }

        return true;
    });


    // filter form validation
    $('#filter-form').validate();


    // $('select.autocomplate').select2().on("change",function(e){ $(this).valid() });
});


function confirmation(e) {
    e.preventDefault();
    var url = e.currentTarget.getAttribute('href');
    Swal.fire({
        icon: 'warning',
        title: 'Are you sure you want to delete this record?',
        text: 'If you delete this, it will be removed forever.',
        showCancelButton: true,
        confirmButtonColor: '#14A858',
        cancelButtonColor: '#2A602A',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: "No, cancel please!",
        dangerMode: true,
    }).then((result) => {
        if (result.value) {
            window.location.href = url;
        }
    })
}

$(".perpage_select").change(function () {
    $("#filter-form").submit();
});
