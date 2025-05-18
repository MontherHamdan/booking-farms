@extends('admin.layout')

@section('content')
<div class="container-fluid">
  <div class="row mb-3 align-items-center">
    <div class="col">
      <h4 class="font-weight-bold text-primary">Cities Management</h4>
    </div>
    <div class="col-auto">
      <a href="{{ route('cities.create') }}" class="btn btn-sm btn-primary">
        <i class="fas fa-plus-circle mr-1"></i> Add City
      </a>
    </div>
  </div>

  @if($cities->isEmpty())
    <div class="text-center py-4 text-muted">
      <i class="fas fa-info-circle fa-2x mb-2"></i>
      <p>No cities yet. Click “Add City” to create one.</p>
    </div>
  @else
    <div class="row">
      @foreach($cities as $city)
        <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
          <div class="card h-100 shadow-sm">
            @if($city->image)
              <img 
                src="{{ $city->image }}" 
                class="card-img-top" 
                alt="{{ $city->name_en }}" 
                style="height:120px; object-fit:cover;"
              >
            @else
              <div 
                class="d-flex align-items-center justify-content-center bg-light" 
                style="height:120px;"
              >
                <i class="fas fa-image fa-2x text-secondary"></i>
              </div>
            @endif

            <div class="card-body p-2 d-flex flex-column">
              <h6 class="card-title mb-1 text-dark text-truncate">{{ $city->name_en }}</h6>

              <div class="mt-auto d-flex justify-content-between align-items-center">
                <span class="badge rounded-pill
                  {{ $city->status == \App\Models\City::STATUS_PUBLISHED 
                      ? 'bg-success' 
                      : 'bg-secondary' 
                  }} py-1 px-2">
                  {{ $city->status == \App\Models\City::STATUS_PUBLISHED ? 'Published' : 'Unpublished' }}
                </span>
                <small class="text-muted">#{{ $city->order }}</small>
              </div>
            </div>

            <div class="card-footer bg-white border-top-0 p-2">
              <div class="btn-group btn-block" role="group">
                <a 
                  href="{{ route('cities.edit', $city->id) }}" 
                  class="btn btn-sm btn-outline-info"
                >
                  <i class="fas fa-edit"></i>
                </a>
                <button 
                  type="button" 
                  class="btn btn-sm btn-outline-danger" 
                  onclick="confirmDelete('delete-form-{{ $city->id }}')"
                >
                  <i class="fas fa-trash"></i>
                </button>
              </div>
              <form 
                id="delete-form-{{ $city->id }}" 
                action="{{ route('cities.destroy', $city->id) }}" 
                method="POST" 
                style="display: none;"
              >
                @csrf
                @method('DELETE')
              </form>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <div class="d-flex justify-content-end">
                {{ $cities->links() }}
            </div>
        </div>
    </div>
  @endif
</div>
@endsection