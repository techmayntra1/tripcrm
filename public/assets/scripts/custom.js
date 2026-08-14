$(document).ready(function() {
    $('.needs-validation').on('submit', function(e) {
        var form = $(this)[0];
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        $(this).addClass('was-validated');
    });

    // Global double-submit guard for all forms
    $(document).on('submit', 'form', function(e) {
        var $form = $(this);

        if ($form.hasClass('no-submit-guard')) return;

        if ($form.hasClass('needs-validation') && !$form[0].checkValidity()) {
            return;
        }

        if ($form.data('submitting')) {
            e.preventDefault();
            return false;
        }
        $form.data('submitting', true);

        $form.find('button[type="submit"], input[type="submit"]').each(function() {
            var $b = $(this);
            $b.data('original-html', $b.is('button') ? $b.html() : $b.val());
            if ($b.is('button')) {
                $b.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
            }
            $b.prop('disabled', true);
        });
    });

    // Re-enable buttons when returning via browser back/forward cache
    $(window).on('pageshow', function(e) {
        if (e.originalEvent && e.originalEvent.persisted) {
            $('form').each(function() {
                var $form = $(this);
                $form.data('submitting', false);
                $form.find('button[type="submit"], input[type="submit"]').each(function() {
                    var $b = $(this);
                    $b.prop('disabled', false);
                    var orig = $b.data('original-html');
                    if (orig !== undefined) {
                        if ($b.is('button')) $b.html(orig); else $b.val(orig);
                    }
                });
            });
        }
    });

    // Real-time validation clearing and enforcement for number inputs
    $('input[type="number"]').on('input', function() {
        var $input = $(this);
        var val = $input.val();
        var numVal = parseFloat(val);
        var min = parseFloat($input.attr('min'));
        var max = parseFloat($input.attr('max'));

        // Remove leading zeros except for decimal numbers
        if (val.length > 1 && val.charAt(0) === '0' && val.charAt(1) !== '.') {
            val = val.replace(/^0+/, '') || '0';
            $input.val(val);
            numVal = parseFloat(val);
        }

        // Enforce min value
        if (!isNaN(min) && numVal < min) {
            $input.val(min);
            numVal = min;
        }

        // Enforce max value
        if (!isNaN(max) && numVal > max) {
            $input.val(max);
            numVal = max;
        }

        // Clear validation error if value is valid
        if (val !== '' && !isNaN(numVal)) {
            $input.removeClass('is-invalid');
        }
    });

    // Prevent invalid characters in number inputs
    $('input[type="number"]').on('keydown', function(e) {
        // Allow: backspace, delete, tab, escape, enter, decimal point, minus (if min allows)
        var allowedKeys = [8, 9, 27, 13, 46, 110, 190];
        var min = parseFloat($(this).attr('min'));

        // Allow minus only if min is not set or min < 0
        if (isNaN(min) || min < 0) {
            allowedKeys.push(109, 189); // minus keys
        }

        if (allowedKeys.indexOf(e.keyCode) !== -1 ||
            // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
            (e.keyCode === 65 && e.ctrlKey === true) ||
            (e.keyCode === 67 && e.ctrlKey === true) ||
            (e.keyCode === 86 && e.ctrlKey === true) ||
            (e.keyCode === 88 && e.ctrlKey === true) ||
            // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)) {
            return;
        }
        // Ensure that it is a number and stop the keypress if not
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });

    // Clear validation on focus for better UX
    $('input, select, textarea').on('focus', function() {
        $(this).removeClass('is-invalid');
    });

    // Clear validation on valid input for selects
    $('select').on('change', function() {
        if ($(this).val()) {
            $(this).removeClass('is-invalid');
        }
    });

    // Clear validation on valid input for text inputs
    $('input[type="text"], input[type="email"], input[type="date"], textarea').on('input', function() {
        if ($(this).val().trim()) {
            $(this).removeClass('is-invalid');
        }
    });

    $('input[type="tel"]').on('input', function() {
        $(this).val($(this).val().replace(/[^0-9]/g, ''));
        if ($(this).val().length >= 10) {
            $(this).removeClass('is-invalid');
        }
    });

    $('input[maxlength]').on('input', function() {
        var max = parseInt($(this).attr('maxlength'));
        if ($(this).val().length > max) {
            $(this).val($(this).val().substring(0, max));
        }
    });
    // NOTE: .select2-city is initialised per-page (each page passes its own
    // city data/placeholder/tags). Initialising it here too double-wraps the
    // element and leaves a stray extra dropdown arrow, so it is not done here.
    $('#city_id').on('change', function() {
        var otherCityDiv = $('#otherCityDiv');
        var otherCityInput = $('#other_city');
        if ($(this).val() === 'other') {
            otherCityDiv.show();
            otherCityInput.attr('required', 'required');
        } else {
            otherCityDiv.hide();
            otherCityInput.removeAttr('required');
            otherCityInput.val('');
        }
    });
    $('.qty, .rate').on('input', function() {
        var row = $(this).closest('tr');
        var qty = parseFloat(row.find('.qty').val()) || 0;
        var rate = parseFloat(row.find('.rate').val()) || 0;
        row.find('.amount').val((qty * rate).toFixed(2));
        calculateTotals();
    });
    function calculateTotals() {
        var subtotal = 0;
        $('.amount').each(function() {
            subtotal += parseFloat($(this).val()) || 0;
        });
        $('#subtotal').text('₹' + subtotal.toFixed(2));
    }
    $('#payment_mode').on('change', function() {
        var bankDetails = $('#bankDetails');
        var bankSelect = $('#bank');
        if ($(this).val() === 'cash') {
            bankDetails.hide();
            bankSelect.removeAttr('required');
        } else {
            bankDetails.show();
            bankSelect.attr('required', 'required');
        }
    });
    $('#invoice').on('change', function() {
        var placeholder = $('#invoicePlaceholder');
        var summary = $('#invoiceSummary');
        if ($(this).val()) {
            placeholder.hide();
            summary.show();
        } else {
            placeholder.show();
            summary.hide();
        }
    });

    // Toggle password visibility
    $('.toggle-password').on('click', function() {
        var target = $($(this).data('target'));
        var icon = $(this).find('i');
        if (target.attr('type') === 'password') {
            target.attr('type', 'text');
            icon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            target.attr('type', 'password');
            icon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
    });
});
