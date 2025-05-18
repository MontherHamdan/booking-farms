<ul class="list-group">
    @foreach ($addresses as $address)
        <li class="list-group-item d-flex justify-content-between align-items-center">
            {{ $address->name_en }} / {{ $address->name_ar }}
            <form action="{{ route('addresses.delete', $address->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            </form>
        </li>
    @endforeach
</ul>

<div class="mt-3">
    {{ $addresses->links() }}
</div>
