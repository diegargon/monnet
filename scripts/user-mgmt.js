$(document).ready(function () {
    // Common Code
    function showFormError(formSelector, msg) {
        let $form = $(formSelector);
        let $err = $form.find('.form-error');
        if ($err.length === 0) {
            $err = $('<div class="form-error" style="color:red;margin-bottom:8px;"></div>');
            $form.prepend($err);
        }
        $err.text(msg).show();
    }
    function clearFormError(formSelector) {
        $(formSelector).find('.form-error').hide();
    }
    function markInvalidFields(formSelector, fields) {
        $(formSelector + ' input, ' + formSelector + ' select').removeClass('invalid-field');
        fields.forEach(function (id) {
            $(id).addClass('invalid-field');
        });
    }

    // Función para mostrar/ocultar contraseña
    document.querySelectorAll('.toggle-password').forEach(function(element) {
        element.addEventListener('click', function() {
            const input = this.previousElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                this.textContent = '👁️';
            } else {
                input.type = 'password';
                this.textContent = '👁️';
            }
        });
    });

    // Create User Code
    function validateUserForm() {
        const username = $('#newUsername').val().trim();
        const email = $('#newEmail').val().trim();
        const pass = $('#userPassword').val();
        const pass2 = $('#confirmUserPassword').val();
        let invalid = [];
        if (!username) invalid.push('#newUsername');
        if (!email) invalid.push('#newEmail');
        if (!pass) invalid.push('#userPassword');
        if (!pass2) invalid.push('#confirmUserPassword');
        if (pass && pass2 && pass !== pass2) invalid.push('#userPassword', '#confirmUserPassword');
        markInvalidFields('#createUserForm', invalid);
        return invalid.length === 0;
    }

    $('#createUserForm input').on('input', function () {
        clearFormError('#createUserForm');
        $('#createUserForm button[type=submit]').prop('disabled', !validateUserForm());
    });


    $('#createUserForm').on('submit', function (e) {
        e.preventDefault();
        clearFormError('#createUserForm');
        $('.status-msg-create').text('').hide();
        if (!validateUserForm()) {
            showFormError('#createUserForm', 'Todos los campos son obligatorios y las contraseñas deben coincidir.');
            return;
        }

        const command_values = {
            username: $('#newUsername').val().trim(),
            email: $('#newEmail').val().trim(),
            password: $('#userPassword').val(),
            isAdmin: $('#isAdmin').is(':checked') ? 1 : 0
        };

        const data = {
            command: 'createUser',
            command_values: command_values
        };

        console.log('Enviando (createUser):', data);

        $.post('submitter.php', data)
            .done(function (resp) {
                console.log('Recibido (createUser):', resp);
                let json;
                try {
                    json = (typeof resp === 'object') ? resp : JSON.parse(resp);
                } catch (e) {
                    $('.status-msg-create').text('Respuesta inesperada del servidor.').show();
                    return;
                }
                if (json.success) {
                    alert('Usuario creado correctamente');
                    $('#createUserForm')[0].reset();
                    $('#createUserForm button[type=submit]').prop('disabled', true);
                    clearFormError('#createUserForm');
                    markInvalidFields('#createUserForm', []);
                    $('.status-msg-create').text('').hide();
                } else if (json.command_error) {
                    $('.status-msg-create').text(json.command_error_msg || 'Error al crear usuario').show();
                } else {
                    showFormError('#createUserForm', json.message || 'Error al crear usuario');
                }
            })
            .fail(function () {
                $('.status-msg-create').text('Error al crear usuario').show();
            });
    });

    // EDIT USER

    function validateProfileForm() {
        const username = $('#username').val().trim();
        const email = $('#email').val().trim();
        const currentPass = $('#currentPassword').val();
        const newPass = $('#newPassword').val();
        const confirmPass = $('#confirmPassword').val();
        let invalid = [];
        if (!username) invalid.push('#username');
        if (!email) invalid.push('#email');
        if (!currentPass) invalid.push('#currentPassword');
        if (newPass || confirmPass) {
            if (!newPass) invalid.push('#newPassword');
            if (!confirmPass) invalid.push('#confirmPassword');
            if (newPass && confirmPass && newPass !== confirmPass) invalid.push('#newPassword', '#confirmPassword');
        }
        markInvalidFields('#profileForm', invalid);
        return invalid.length === 0;
    }

    $('#profileForm input').on('input', function () {
        clearFormError('#profileForm');
        $('#profileForm button[type=submit]').prop('disabled', !validateProfileForm());
    });

    $('#profileForm').on('submit', function (e) {
        e.preventDefault();
        clearFormError('#profileForm');
        $('.status-msg-modify').text('').hide();
        if (!validateProfileForm()) {
            showFormError('#profileForm', 'Todos los campos obligatorios deben estar completos y las contraseñas deben coincidir.');
            return;
        }

        const command_values = {
            username: $('#username').val().trim(),
            email: $('#email').val().trim(),
            current_password: $('#currentPassword').val(),
            timezone: $('#timezone').val(),
            theme: $('#theme').val(),
            lang: $('#lang').val()
        };
        const newPass = $('#newPassword').val();
        if (newPass) command_values.new_password = newPass;

        const data = {
            command: 'updateProfile',
            command_values: command_values
        };

        console.log('Enviando (updateProfile):', data);

        $.post('submitter.php', data)
            .done(function (resp) {
                console.log('Recibido (updateProfile):', resp);
                let json;
                try {
                    json = (typeof resp === 'object') ? resp : JSON.parse(resp);
                } catch (e) {
                    $('.status-msg-modify').text('Respuesta inesperada del servidor.').show();
                    return;
                }
                if (json.success) {
                    alert('Perfil actualizado correctamente');
                    clearFormError('#profileForm');
                    markInvalidFields('#profileForm', []);
                    $('.status-msg-modify').text('').hide();
                } else if (json.command_error) {
                    $('.status-msg-modify').text(json.command_error_msg || 'Error al actualizar perfil').show();
                } else {
                    showFormError('#profileForm', json.message || 'Error al actualizar perfil');
                }
            })
            .fail(function () {
                $('.status-msg-modify').text('Error al actualizar perfil').show();
            });
    });

});

// (mover esto CSS)
$('<style>.invalid-field{border:2.5px solid red !important;}</style>').appendTo('head');
