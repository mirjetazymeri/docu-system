$(document).ready(function() {
    $('#saveUserButton').click(function() {
        $.ajax({
            // url: '{{ route("admin.employees.storeUser") }}',
            url: "/admin/employees/storeUser",
            method: 'POST',
            data: $('#createUserForm').serialize(),
            success: function(response) {
                $('#user_id').append(new Option(response.full_name, response.id, true, true));
                $('#createUserModal').modal('hide');
                $('#createUserForm')[0].reset();
                $('#full_name').val(response.full_name);
            },
            error: function(xhr) {
                alert('An error occurred while creating the user.');
            }
        });
    });

    $('#user_id').change(function() {
        var selectedUserId = $(this).val();
        var selectedUserFullName = $(this).find('option:selected').text();
        $('#full_name').val(selectedUserFullName);
    });
});


  $(document).ready(function() {
        $('#user_id').select2({
            placeholder: userPlaceholderText,
            ajax: {
                url: getUsersUrl,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        term: params.term // search term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.map(function(item) {
                            return { id: item.id, text: item.full_name };
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 2,
            theme: 'bootstrap'
        });

        // Preselect the user if editing an existing record
        if (selectedUserId) {
            $('#user_id').val(selectedUserId).trigger('change');
        }
    });
