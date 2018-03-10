$(document).ready(function() {

    var userId = '{{ user.id }}';

    /**
     * Validate the form
     */
    $('#formProfile').validate({
        rules: {
            email: {
                required: true,
                email: true,
                remote: {
                    url: '/account/validate-email',
                    data: {
                        ignore_id: function() {
                            return userId;
                        }
                    }
                }
            },
            password: {
                minlength: 6,
                validPassword: true
            }
        },
        messages: {
            email: {
                remote: 'email already taken'
            }
        }
    });


    /**
      * Show password toggle button
      */
    $('#inputPassword').hideShowPassword({
        show: false,
        innerToggle: 'focus'
    });
});