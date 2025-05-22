$(document).ready(function () {
    // Utilidad para mostrar mensajes de error
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

    // --- Crear usuario ---
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

    $('#createUserForm .toggle-password').on('click', function () {
        let input = $(this).siblings('input');
        input.attr('type', input.attr('type') === 'password' ? 'text' : 'password');
    });

    $('#createUserForm').on('submit', function (e) {
        e.preventDefault();
        clearFormError('#createUserForm');
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
            ct: {
                command: 'createUser',
                command_values: command_values
            }
        };

        console.log('Enviando (createUser):', data);

        $.post('submitter.php', data)
            .done(function (resp) {
                console.log('Recibido (createUser):', resp);
                let json;
                try {
                    json = (typeof resp === 'object') ? resp : JSON.parse(resp);
                } catch (e) {
                    showFormError('#createUserForm', 'Respuesta inesperada del servidor.');
                    return;
                }
                if (json.success) {
                    alert('Usuario creado correctamente');
                    $('#createUserForm')[0].reset();
                    $('#createUserForm button[type=submit]').prop('disabled', true);
                    clearFormError('#createUserForm');
                    markInvalidFields('#createUserForm', []);
                } else {
                    showFormError('#createUserForm', json.message || 'Error al crear usuario');
                }
            })
            .fail(function () {
                showFormError('#createUserForm', 'Error al crear usuario');
            });
    });

    // --- Modificar usuario actual ---
    function validateEditUserForm() {
        const username = $('#editUsername').val().trim();
        const email = $('#editEmail').val().trim();
        const pass = $('#editUserPassword').val();
        const pass2 = $('#editConfirmUserPassword').val();
        let invalid = [];
        if (!username) invalid.push('#editUsername');
        if (!email) invalid.push('#editEmail');
        if (pass || pass2) {
            if (!pass) invalid.push('#editUserPassword');
            if (!pass2) invalid.push('#editConfirmUserPassword');
            if (pass && pass2 && pass !== pass2) invalid.push('#editUserPassword', '#editConfirmUserPassword');
        }
        markInvalidFields('#editUserForm', invalid);
        return invalid.length === 0;
    }

    $('#editUserForm input').on('input', function () {
        clearFormError('#editUserForm');
        $('#editUserForm button[type=submit]').prop('disabled', !validateEditUserForm());
    });

    $('#editUserForm .toggle-password').on('click', function () {
        let input = $(this).siblings('input');
        input.attr('type', input.attr('type') === 'password' ? 'text' : 'password');
    });

    $('#editUserForm').on('submit', function (e) {
        e.preventDefault();
        clearFormError('#editUserForm');
        if (!validateEditUserForm()) {
            showFormError('#editUserForm', 'Todos los campos son obligatorios y las contraseñas deben coincidir.');
            return;
        }

        const command_values = {
            user_id: $('#editUserId').val(),
            username: $('#editUsername').val().trim(),
            email: $('#editEmail').val().trim(),
            isAdmin: $('#editIsAdmin').is(':checked') ? 1 : 0
        };
        const pass = $('#editUserPassword').val();
        if (pass) command_values.password = pass;
        const data = {
            ct: {
                command: 'updateUser',
                command_values: command_values
            }
        };

        console.log('Enviando (updateUser):', data);

        $.post('submitter.php', data)
            .done(function (resp) {
                console.log('Recibido (updateUser):', resp);
                let json;
                try {
                    json = (typeof resp === 'object') ? resp : JSON.parse(resp);
                } catch (e) {
                    showFormError('#editUserForm', 'Respuesta inesperada del servidor.');
                    return;
                }
                if (json.success) {
                    alert('Usuario modificado correctamente');
                    clearFormError('#editUserForm');
                    markInvalidFields('#editUserForm', []);
                } else {
                    showFormError('#editUserForm', json.message || 'Error al modificar usuario');
                }
            })
            .fail(function () {
                showFormError('#editUserForm', 'Error al modificar usuario');
            });
    });

});

// (mover esto CSS)
$('<style>.invalid-field{border:1.5px solid red !important;}</style>').appendTo('head');
