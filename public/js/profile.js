$('#edit_profile_form').validate({
    ignore:'.ignore',
    errorClass:'invalid',
    validClass:'success',
    rules:{
        first_name: {
            required: true,
            minlength:2,
            maxlength: 100,
        },
        last_name: {
            required: true,
            minlength:2,
            maxlength: 100,
        },
    },
    messages: {
        first_name: {
            required: "Please enter first name.",
            minlength: "First name must be more than 2 characters.",
            maxlength: "First name must be less than 100 characters."
        },
        last_name: {
            required: "Please enter last name.",
            minlength: "Last name must be more than 2 characters.",
            maxlength: "Last name must be less than 100 characters."
        },
    },
    errorPlacement:function(error,element){
        error.insertAfter(element);
    },
    submitHandler:function(form){

        $.LoadingOverlay("show");
        form.submit();
    }

});

$('#change_password_form').validate({
    ignore:'.ignore',
    errorClass:'invalid',
    validClass:'success',
    rules:{
        old_password: {
            required: true,
            minlength:6,
            maxlength: 100,
        },
        new_password: {
            required: true,
            minlength:6,
            maxlength: 100,
        },
        confirm_password: {
            required: true,
            equalTo: '#new_password',
        },
    },
    messages: {
        old_password: {
            required: "Please enter old password.",
            minlength: "Old password must be more than 6 characters.",
            maxlength: "Old password must be less than 100 characters."
        },
        new_password: {
            required: "Please enter new password.",
            minlength: "New password must be more than 6 characters.",
            maxlength: "New password must be less than 100 characters."
        },
        confirm_password: {
          required: 'Confirm password is required.',
          equalTo: 'New password and confirm password must be same.',
        },
    },
    errorPlacement:function(error,element){
        error.insertAfter(element);
    },
    submitHandler:function(form){

        $.LoadingOverlay("show");
        form.submit();
    }

});
