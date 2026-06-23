var PROFILEADM_CTRL = '/' + chrLocale + '/controller/profile';
var originalData = {};
//region events
$(document).ready(function () {
    $('#Email').prop('disabled', true); // Disable the e-mail field

    // Event listener for the cancel change password button
    $('#cancelChPass').on('click', function () {
        clearPasswordFields();
    });

    // Show the progress bar when typing in the password fields
    $('#repass1, #repass2').on('input', function () {
        $('.pass-graybar').show(); // Mostrar la barra de progreso
    });

    loadLanguages();
    load_user();
    initializePasswordStrengthMeter();
});

// Change theme
$('#flexSwitchCheckChecked').on('change', function () {
    let darkMode = $(this).is(':checked') ? 1 : 0;
    $('html').attr('data-bs-theme', darkMode ? 'dark' : 'light');

    // Guardar el estado en la base de datos
    $.ajax({
        url: PROFILEADM_CTRL,
        type: 'POST',
        data: {
            action: 'T',
            dark: darkMode,
        },
        success: function (res) {
        },
    });
});

$('#updateProfile').on('click', function (event) {
    event.preventDefault();

    var valid = true;
    var data = $('#Perfil input, #Perfil select').serializeObject();
    data.action = 'U';
    data.email = data.email.trim().toLowerCase();
    data.localeId = $('#langId').val() || 'en_US';

    $('input[type=text]').removeClass('is-invalid');
    // Validations
    if (data.first == '') {
        $('#First').addClass('is-invalid');
        valid = false;
    }
    if (data.last == '') {
        $('#Last').addClass('is-invalid');
        valid = false;
    }
    /*if (data.email == '') {
        $('#Email').addClass('is-invalid');
        valid = false;
    }
    if (!validateEmail(data.email)) {
        $('#Email').addClass('is-invalid');
        valid = false;
    }*/
    if (valid) {
        $.ajax({
            type: 'POST',
            url: PROFILEADM_CTRL,
            dataType: 'JSON',
            data,
            success: function (response) {
                if (response.result) {
                    alertNotify({
                        type: 'success',
                        text: labels.nteSaveSuccess,
                        icon: 'fas fa-check',
                        timeout: 3000,
                    });
                    // Update original data
                    originalData = {
                        first: data.first,
                        last: data.last,
                        email: data.email,
                        localeId: data.localeId,
                    };
                    // Disable the button after successful save
                    $('#updateProfile').prop('disabled', true);
                } else {
                    if (response.error.includes('Duplicate entry')) {
                        alertNotify({
                            type: 'warning',
                            text: labels.nteDuplicateEntry,
                            icon: 'fas fa-check',
                            timeout: 3000,
                        });
                    } else {
                        alertNotify({
                            type: 'danger',
                            text: labels.nteErrorSave,
                            icon: 'fas fa-check',
                            timeout: 3000,
                        });
                    }
                }
            },
        });
    } else {
        alertNotify({
            type: 'warning',
            text: labels.nteFields,
            icon: 'fas fa-check',
            timeout: 3000,
        });
    }
});

// Show/Hide password logic for the current password field
$('#Password').on('click', '#review', function (event) {
    event.preventDefault();
    var input = $('#repass');
    var type = input.attr('type');
    if (type === 'password') {
        input.attr('type', 'text');
        $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
    } else {
        input.attr('type', 'password');
        $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
    }
});

// Show/Hide password logic for new and confirm password fields
$('#Password').on('click', '[id^=review]', function (event) {
    event.preventDefault();
    var id = $(this).attr('id').slice(-1);
    var input = $('#repass' + id);
    var type = input.attr('type');
    if (type == 'password') {
        input.attr('type', 'text');
        $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
    } else {
        input.attr('type', 'password');
        $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
    }
});

// Password strength validation for the new password field
$('#repass1').on('password.score', (e, score) => {
    $('#repass1').data('score', score);

    if ($('#repass1').val()) {
        if (score < 64) {
            $('#repass1').addClass('is-invalid');
            $('#repass1').removeClass('is-valid');
        } else {
            $('#repass1').addClass('is-valid');
            $('#repass1').removeClass('is-invalid');
        }
    } else {
        $('#repass1').removeClass('is-invalid is-valid');
    }
});

// Password match validation for the confirm password field
$('#repass2').on('keyup', function () {
    var pass1 = $('#repass1').val();
    var pass2 = $(this).val();
    if (pass2) {
        if (pass1 !== pass2) {
            $('#repass2').addClass('is-invalid');
            $('#repass2').removeClass('is-valid');
        } else {
            $('#repass2').addClass('is-valid');
            $('#repass2').removeClass('is-invalid');
        }
    } else {
        $('#repass2').removeClass('is-invalid is-valid');
    }
});

$('#repass1').on('focus blur', function (e) {
    $(this).trigger('keyup');
});

$('#repass2').on('focus blur', function (e) {
    $(this).trigger('keyup');
});

// Update password
$('#updateChPass').on('click', function (event) {
    event.preventDefault();

    var validation = true;
    var data = $('#Password input').serializeObject();
    data.action = 'UP';
    $('#Password input').removeClass('is-invalid');
    var score = $('#repass1').data('score');

    if (data.currentPassword === '') {
        validation = false;
        $('#repass').addClass('is-invalid');
    }
    if (data.newPassword === '' || score < 64) {
        validation = false;
        $('#repass1').addClass('is-invalid');
    }
    if (data.newPassword !== data.newPassword2) {
        validation = false;
        $('#repass2').addClass('is-invalid');
    }

    if (validation) {
        $.ajax({
            type: 'POST',
            url: PROFILEADM_CTRL,
            data: data,
            dataType: 'json',
            success: function (response) {
                if (response.result) {
                    if (response.auth) {
                        if (response.info) {
                            alertNotify({
                                type: 'success',
                                text: labels.nteSaveSuccess,
                                icon: 'fas fa-check',
                                timeout: 3000,
                            });
                            clearPasswordFields();
                        } else {
                            alertNotify({
                                type: 'danger',
                                text: labels.nteCoudNotBeUpdated,
                                icon: 'fas fa-check',
                                timeout: 3000,
                            });
                        }
                    } else {
                        $('#repass').addClass('is-invalid');
                        alertNotify({
                            type: 'danger',
                            text: labels.nteCurrentPasswordIncorre,
                            icon: 'fas fa-check',
                            timeout: 3000,
                        });
                    }
                } else {
                    alertNotify({
                        type: 'danger',
                        text: labels.nteErrorSave,
                        icon: 'fas fa-check',
                        timeout: 3000,
                    });
                }
            },
        });
    } else {
        alertNotify({
            type: 'warning',
            text: labels.nteFields,
            icon: 'fas fa-check',
            timeout: 3000,
        });
    }
});
//endregion
// region functions
function loadLanguages() {
    // load language options into a select element
    var langSelect = $('#langId');
    langSelect.select2({
        data: locTree,
        allowClear: true,
        theme: 'bootstrap-5',
        selectionCssClass: 'select2--small',
        dropdownCssClass: 'select2--small',
        placeholder: labels.nteLanguages,
    });
}

// load user data from the server
function load_user() {
    $.ajax({
        url: PROFILEADM_CTRL,
        type: 'POST',
        dataType: 'JSON',
        data: {
            action: 'R',
            part: 'P',
        },
        success: function (response) {
            var data = response.data[0]; // Accesses the first element of the array
            $('#First').val(data.first);
            $('#Last').val(data.last);
            $('#Email').val(data.email);
            $('#langId').val(data.locale_id).trigger('change');

            // Configurar el switch de modo oscuro
            let isDark = data.dark == 1;
            $('#flexSwitchCheckChecked').prop('checked', isDark);
            $('html').attr('data-bs-theme', isDark ? 'dark' : 'light');

            // Store original data for comparison
            originalData = {
                first: data.first,
                last: data.last,
                email: data.email,
                localeId: data.locale_id,
                dark: data.dark,
            };

            // Make sure that the value exists in the options before assigning it.
            if ($("#langId option[value='" + data.locale_id + "']").length) {
                $('#langId').val(data.locale_id).trigger('change');
            }

            // Attach change and input event listeners after data is loaded
            $('input, select').on('change input', function () {
                checkForChanges();
            });
        },
    });
}

// validate the email format
function validateEmail(email) {
    var regex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return regex.test(String(email));
}

// check if the form data has changed from the original data
function checkForChanges() {
    var currentData = {
        first: $('#First').val(),
        last: $('#Last').val(),
        email: $('#Email').val().trim().toLowerCase(),
        localeId: $('#langId').val() || 'en_US',
    };

    var hasChanged = Object.keys(originalData).some((key) => originalData[key] !== currentData[key]);

    if (hasChanged) {
        $('#updateProfile').prop('disabled', false);
    } else {
        $('#updateProfile').prop('disabled', true);
    }
}

// Initialize the password strength meter
function initializePasswordStrengthMeter() {
    $('#repass1').data('score', 0);
    $('#repass1').password({
        closestSelector: 'div.form-group', // Element to append to, instead of input
        enterPass: 'Type your password',
        shortPass: 'The password is too short',
        containsField: 'The password contains your username',
        steps: {
            13: 'Really insecure password',
            33: 'Weak; try combining letters & numbers',
            64: 'Medium; try using special characters',
            94: 'Strong password',
        },
        showPercent: false,
        showText: false, // shows the text tips
        animate: true, // whether or not to animate the progress bar on input blur/focus
        animateSpeed: 'fast', // the above animation speed
        field: false, // select the match field (selector or jQuery instance) for better password checks
        fieldPartialMatch: true, // whether to check for partials in field
        minimumLength: 4, // minimum password length (below this threshold, the score is 0)
        useColorBarImage: true, // use the (old) colorbar image
        customColorBarRGB: {
            red: [0, 240],
            green: [0, 240],
            blue: 10,
        }, // set custom rgb color ranges for colorbar.
    });
}

// Clean and reset input fields and validation classes
function clearPasswordFields() {
    $('#repass').val('');
    $('#repass1').val('');
    $('#repass2').val('');

    // Remove validation classes
    $('#repass').removeClass('is-invalid').removeClass('is-valid');
    $('#repass1').removeClass('is-invalid').removeClass('is-valid');
    $('#repass2').removeClass('is-invalid').removeClass('is-valid');

    $('.pass-graybar').hide(); // Hide the progress bar
}
//endregion
