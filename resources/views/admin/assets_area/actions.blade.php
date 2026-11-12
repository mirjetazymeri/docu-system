<div class="dropdown">
    <button class="btn btn-outline-primary dropdown-toggle" type="button" id="actionsDropdown-{{ $asset->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fa fa-bars"></i> {{ __('words.actions') }}
    </button>
    <div class="dropdown-menu" aria-labelledby="actionsDropdown-{{ $asset->id }}">
        <a class="dropdown-item" href="{{ route('admin.assets-areas.edit', $asset->id) }}" style="color: black;">
            <i class="fa fa-edit" style="color: black;"></i> {{ __('words.edit') }}
        </a>
        <form action="{{ route('admin.assets-areas.destroy', $asset->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="dropdown-item" style="color: black;" onclick="return confirm(@js(__('words.confirm_delete')));">
                <i class="fa fa-trash" style="color: black;"></i> {{ __('words.delete') }}
            </button>
        </form>
    </div>
</div>
