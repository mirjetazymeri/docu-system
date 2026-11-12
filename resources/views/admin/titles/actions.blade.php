<div class="dropdown">
    <button class="btn btn-outline-primary dropdown-toggle" type="button" id="actionsDropdown{{ $id }}"
        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fa fa-bars"></i> {{ __('ui.actions') }}
    </button>
    <div class="dropdown-menu" aria-labelledby="actionsDropdown{{ $id }}">
        <!-- Edit -->
        <a class="dropdown-item text-dark" href="{{ $editRoute }}">
            <i class="fa fa-edit text-warning"></i> {{ __('button.edit') }}
        </a>

        <!-- Delete -->
        <form action="{{ $deleteRoute }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button class="dropdown-item text-dark" type="submit"
                onclick="return confirm(@js(__('ui.are_you_sure_you_want_to_delete_this')));">
                <i class="fa fa-trash text-danger"></i> {{ __('button.delete') }}
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

    .fa-edit {
        color: #ffc107;
    }

    .fa-trash {
        color: #98291B;
    }
</style>
