<a href="{{ route('positions.edit', $id) }}" class="edit btn btn-success btn-sm mr-1" data-id="{{ $id }}">
    <i class="fas fa-fw fa-edit"></i>
</a>
<a href="javascript:void(0)" class="delete btn btn-danger btn-sm" onclick="deletePosition({{ $id }})">
    <i class="fas fa-fw fa-trash"></i>
</a>