'use strict';
// bootstrap wizard//
$('#gender, #gender1').select2({
    theme: 'bootstrap',
    placeholder: '',
    width: '100%',
});
$('input[type="checkbox"].custom-checkbox, input[type="radio"].custom-radio').iCheck({
    checkboxClass: 'icheckbox_minimal-blue',
    radioClass: 'iradio_minimal-blue',
    increaseArea: '20%',
});
$('#dob').datetimepicker({
    format: 'YYYY-MM-DD',
    widgetPositioning: {
        vertical: 'bottom',
    },
    keepOpen: false,
    useCurrent: false,
    maxDate: moment()
        .add(1, 'h')
        .toDate(),
});
$('#commentForm').bootstrapValidator({
    fields: {
        first_name: {
            validators: {
                notEmpty: {
                    message: window.uiMessages.user.first_name_required,
                },
            },
            required: true,
            minlength: 3,
        },
        last_name: {
            validators: {
                notEmpty: {
                    message: window.uiMessages.user.last_name_required,
                },
            },
            required: true,
            minlength: 3,
        },
        password: {
            validators: {
                notEmpty: {
                    message: window.uiMessages.user.password_required,
                },
                different: {
                    field: 'first_name,last_name',
                    message: window.uiMessages.user.password_name,
                },
            },
        },
        password_confirm: {
            validators: {
                notEmpty: {
                    message: window.uiMessages.user.confirm_required,
                },
                identical: {
                    field: 'password',
                    message: window.uiMessages.user.confirm_match,
                },
                different: {
                    field: 'first_name,last_name',
                    message: window.uiMessages.user.confirm_match,
                },
            },
        },
        email: {
            validators: {
                notEmpty: {
                    message: window.uiMessages.user.email_required,
                },
                emailAddress: {
                    message: window.uiMessages.user.email_invalid,
                },
            },
        },
        activate: {
            validators: {
                notEmpty: {
                    message: window.uiMessages.user.activate,
                },
            },
        },
        group: {
            validators: {
                notEmpty: {
                    message: window.uiMessages.user.role_required,
                },
            },
        },
    },
});

$('#rootwizard').bootstrapWizard({
    tabClass: 'nav nav-pills',
    onNext: function(tab, navigation, index) {
        var $validator = $('#commentForm')
            .data('bootstrapValidator')
            .validate();
        return $validator.isValid();
    },
    onTabClick: function(tab, navigation, index) {
        return false;
    },
    onTabShow: function(tab, navigation, index) {
        var $total = navigation.find('li').length;
        var $current = index + 1;

        // If it's the last tab then hide the last button and show the finish instead
        if ($current >= $total) {
            $('#rootwizard')
                .find('.pager .next')
                .hide();
            $('#rootwizard')
                .find('.pager .finish')
                .show();
            $('#rootwizard')
                .find('.pager .finish')
                .removeClass('disabled');
        } else {
            $('#rootwizard')
                .find('.pager .next')
                .show();
            $('#rootwizard')
                .find('.pager .finish')
                .hide();
        }
    },
});

$('#rootwizard .finish').click(function() {
    var $validator = $('#commentForm')
        .data('bootstrapValidator')
        .validate();
    if ($validator.isValid()) {
        document.getElementById('commentForm').submit();
    }
});
$('#activate').on('ifChanged', function(event) {
    $('#commentForm').bootstrapValidator('revalidateField', $('#activate'));
});
$('#commentForm').keypress(function(event) {
    if (event.which == '13') {
        event.preventDefault();
    }
});
