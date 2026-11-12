<div class="dropdown">
    <button class="btn btn-outline-primary dropdown-toggle" type="button" id="actionsDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fa fa-bars"></i> {{ __('words.actions') }}
    </button>
    <div class="dropdown-menu" aria-labelledby="actionsDropdown">
        <a class="dropdown-item" href="{{ route('admin.subjects.edit', $row->id) }}">
            <i class="fa fa-edit"></i> {{ __('words.edit') }}
        </a>
        <form action="{{ route('admin.subjects.destroy', $row->id) }}" method="POST" style="display:inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="dropdown-item text-danger" onclick="return confirm(@js(__('words.are_you_sure')));">
                <i class="fa fa-trash"></i> {{ __('words.delete') }}
            </button>
        </form>
    </div>
</div>
