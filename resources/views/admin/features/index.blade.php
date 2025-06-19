@extends('admin.layout')

@section('content')
<div class="container-fluid">
  <div class="row mb-3 align-items-center">
    <div class="col">
      <h4 class="font-weight-bold text-primary">Features Management</h4>
    </div>
    <div class="col-auto">
      <a href="{{ route('dashboard.features.create') }}" class="btn btn-sm btn-primary">
        <i class="fas fa-plus-circle mr-1"></i> Add New Feature
      </a>
    </div>
  </div>

  @if($features->isEmpty())
    <div class="text-center py-4 text-muted">
      <i class="fas fa-info-circle fa-2x mb-2"></i>
      <p>No features yet. Click “Add New Feature” to create one.</p>
    </div>
  @else
    <div class="card shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive py-4 px-4">
          <table class="table table-bordered table-striped mb-0">
            <thead class="thead-light">
              <tr>
                <th class="text-center">ID</th>
                <th class="text-center">Icon</th>
                <th class="text-center">Name (EN)</th>
                <th class="text-center">Name (AR)</th>
                <th class="text-center">Order</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($features as $feature)
              <tr>
                <td class="text-center align-middle">{{ $feature->id }}</td>
                <td class="text-center align-middle">
                  @if($feature->icon)
                    <img src="{{ $feature->icon }}" alt="{{ $feature->name_en }}" width="50">
                  @else
                    <span class="bg bg-secondary">None</span>
                  @endif
                </td>
                <td class="text-center align-middle">{{ $feature->name_en }}</td>
                <td class="text-center align-middle">{{ $feature->name_ar }}</td>
                <td class="text-center align-middle">{{ $feature->order }}</td>
                <td class="text-center align-middle">
                    <!-- Actions Dropdown -->
                    <div class="dropdown d-inline-block">
                      <a class="dropdown-toggle text-dark" id="dropdownMenuButton{{ $feature->id }}"
                         data-bs-toggle="dropdown" style="cursor: pointer;" aria-expanded="false" title="Actions">
                        <i class="fas fa-cog"></i>
                      </a>
                      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton{{ $feature->id }}">
                        <li>
                          <a href="{{ route('dashboard.features.edit', $feature->id) }}" class="dropdown-item" title="Edit Feature">
                            <i class="fas fa-edit me-2"></i>Edit
                          </a>
                        </li>
                        <li>
                          <button type="button" class="dropdown-item text-danger" onclick="confirmDelete('delete-feature-{{ $feature->id }}')">
                            <i class="fas fa-trash-alt me-2"></i>Delete
                          </button>
                        </li>
                      </ul>
                    </div>
                    <form id="delete-feature-{{ $feature->id }}" action="{{ route('dashboard.features.destroy', $feature->id) }}" method="POST" style="display: none;">
                      @csrf
                      @method('DELETE')
                    </form>
                  </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer bg-white border-top-0">
        <div class="d-flex justify-content-end mb-0">
          {{ $features->links() }}
        </div>
      </div>
    </div>
  @endif
</div>
@endsection