<div class="dropdown">
    <button class="btn btn-outline-primary mr-2 dropdown-toggle" type="button" id="actionsDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fa fa-bars ml-1"></i> {{ __('words.actions') }}
    </button>
    <div class="dropdown-menu" aria-labelledby="actionsDropdown">
        <a class="dropdown-item" href="{{ route('admin.customers.edit', $row->id) }}">
            <i class="fa fa-edit"></i> {{ __('words.edit') }}
        </a>
        <form action="{{ route('admin.customers.destroy', $row->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this customer?');">
                <i class="fa fa-trash"></i> {{ __('words.delete') }}
            </button>
        </form>
    </div>
</div>
