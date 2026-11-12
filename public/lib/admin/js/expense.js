

    $(document).ready(function() {
        $('#subject_id').select2({
            placeholder: placeholderText,
            ajax: {
                url: getSuppliersUrl,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        term: params.term, // search term
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

        // Preselect the supplier if editing an existing record
        if (supplierId && supplierName) {
            let option = new Option(supplierName, supplierId, true, true);
            $('#subject_id').append(option).trigger('change');
        }
    });
