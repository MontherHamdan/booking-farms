<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($majors as $major)
            <tr>
                <td>{{ $major->id }}</td>
                <td>{{ $major->name }}</td>
                <td>
                    <!-- Delete Major Form -->
                    <form
                        action="{{ route('diplomas.deleteMajor', ['diplomaId' => $major->diploma_id, 'majorId' => $major->id]) }}"
                        method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger"
                            onclick="return confirm('Are you sure you want to delete this major?')">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="text-center">No majors found.</td>
            </tr>
        @endforelse
    </tbody>
</table>
