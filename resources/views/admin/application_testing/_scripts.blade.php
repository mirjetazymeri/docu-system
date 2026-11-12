<script>
document.addEventListener('DOMContentLoaded', function () {
    const applicationSelect = document.getElementById('application_id');
    const container = document.getElementById('testing-details-container');
    const template = document.getElementById('testing-detail-template');
    const addButton = document.getElementById('add-testing-detail');
    const unitsUrl = @json(url('admin/application_testing/getUnits'));
    let nextIndex = {{ $nextDetailIndex }};
    let units = [];

    function renumberRows() {
        container.querySelectorAll('.testing-detail').forEach(function (row, index) {
            row.querySelector('.testing-detail__number').textContent =
                row.dataset.reference || (@js(__('ui.test_case_number_prefix')) + (index + 1));
            row.querySelector('.js-remove-detail').disabled =
                container.querySelectorAll('.testing-detail').length === 1;
        });
    }

    function populateSelect(select, selectedValue) {
        select.innerHTML = '';
        select.appendChild(new Option(@js(__('ui.select_module')), ''));
        units.forEach(function (unit) {
            select.appendChild(new Option(
                unit.name,
                unit.id,
                false,
                String(unit.id) === String(selectedValue || '')
            ));
        });
    }

    async function loadUnits(clearSelections) {
        const applicationId = applicationSelect.value;
        units = [];

        if (!applicationId) {
            container.querySelectorAll('.js-unit-select').forEach(function (select) {
                populateSelect(select, '');
            });
            return;
        }

        try {
            const response = await fetch(unitsUrl + '/' + encodeURIComponent(applicationId), {
                headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) throw new Error('Units request failed');
            const data = await response.json();
            units = Array.isArray(data.units) ? data.units : [];

            container.querySelectorAll('.js-unit-select').forEach(function (select) {
                const selected = clearSelections
                    ? ''
                    : (select.dataset.selected || select.value);
                populateSelect(select, selected);
                select.dataset.selected = '';
            });
        } catch (error) {
            container.querySelectorAll('.js-unit-select').forEach(function (select) {
                select.replaceChildren(new Option(@js(__('ui.the_modules_could_not_be_loaded')), ''));
            });
        }
    }

    function addDetail() {
        const fragment = template.content.cloneNode(true);
        const row = fragment.querySelector('.testing-detail');
        row.querySelectorAll('[data-name]').forEach(function (field) {
            field.name = 'testing_details[' + nextIndex + '][' + field.dataset.name + ']';
            field.removeAttribute('data-name');
        });
        nextIndex++;
        container.appendChild(fragment);
        populateSelect(container.lastElementChild.querySelector('.js-unit-select'), '');
        renumberRows();
    }

    applicationSelect.addEventListener('change', function () {
        loadUnits(true);
    });
    addButton.addEventListener('click', addDetail);
    container.addEventListener('click', function (event) {
        const removeButton = event.target.closest('.js-remove-detail');
        if (!removeButton || container.querySelectorAll('.testing-detail').length === 1) return;
        removeButton.closest('.testing-detail').remove();
        renumberRows();
    });

    renumberRows();
    if (applicationSelect.value) loadUnits(false);
});
</script>
