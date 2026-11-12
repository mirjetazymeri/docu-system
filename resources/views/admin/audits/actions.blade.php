<div class="dropdown">
    <button class="btn btn-outline-primary dropdown-toggle" type="button" id="actionsDropdown{{ $row->id }}"
        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fa fa-bars"></i> {{ __('words.actions') }}
    </button>
    <div class="dropdown-menu" aria-labelledby="actionsDropdown{{ $row->id }}">

        <a class="dropdown-item text-dark" href="{{ route('admin.audits.pdf', $row->id) }}">
            <i class="fa fa-file-pdf text-danger"></i> {{ __('words.view_as_pdf') }}
        </a>

        <a class="dropdown-item text-dark" href="{{ route('admin.audits.edit', $row->id) }}">
            <i class="fa fa-edit text-warning"></i> {{ __('words.edit') }}
        </a>

        <form action="{{ route('admin.audits.destroy', $row->id) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button class="dropdown-item text-dark" type="submit"
                onclick="return confirm(@js(__('words.sure_to_delete')));">
                <i class="fa fa-trash text-danger"></i> {{ __('words.delete') }}
            </button>
        </form>
    </div>
</div>


<style>
    .dropdown-menu .dropdown-item {
        color: #000 !important;
    }

    .dropdown-menu .dropdown-item:hover {
        background-color: #f8f9fa;
        color: #000 !important;
    }

    .fa-file-pdf {
        color: #dc3545;
    }

    .fa-edit {
        color: #ffc107;
    }

    .fa-trash {
        color: #98291B;
    }
</style>
