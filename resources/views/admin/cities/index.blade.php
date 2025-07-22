@extends('admin.layout')

@section('content')
<div class="container-fluid">
  <div class="row mb-3 align-items-center">
    <div class="col">
      <h4 class="font-weight-bold text-primary">Cities Management</h4>
    </div>
    <div class="col-auto">
      <a href="{{ route('dashboard.cities.create') }}" class="btn btn-sm btn-primary">
        <i class="fas fa-plus-circle mr-1"></i> Add City
      </a>
    </div>
  </div>

  @if($cities->isEmpty())
    <div class="text-center py-4 text-muted">
      <i class="fas fa-info-circle fa-2x mb-2"></i>
      <p>No cities yet. Click "Add City" to create one.</p>
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
              <h6 class="card-title mb-1 text-dark text-truncate" title="{{ $city->name_en }}">
                {{ $city->name_en }}
              </h6>

              <!-- Statistics -->
              <div class="mb-2">
                <div class="d-flex justify-content-between text-muted small">
                  <span><i class="fas fa-seedling mr-1"></i>{{ $city->farms_count }} Farms</span>
                  <span><i class="fas fa-map-marker-alt mr-1"></i>{{ $city->areas_count }} Areas</span>
                </div>
              </div>

              <!-- Coordinates Info -->
              @if($city->hasCoordinates())
                <div class="mb-2">
                  <small class="text-muted d-block" title="Coordinates: {{ $city->coordinates }}">
                    <i class="fas fa-map-pin mr-1"></i>
                    <span class="text-truncate">{{ $city->coordinates }}</span>
                  </small>
                  <a href="https://www.google.com/maps?q={{ $city->latitude }},{{ $city->longitude }}" 
                     target="_blank" 
                     class="btn btn-outline-secondary btn-sm py-0 px-2 mt-1"
                     style="font-size: 0.7rem;"
                     title="View on Google Maps">
                    <i class="fas fa-external-link-alt"></i> Maps
                  </a>
                </div>
              @else
                <div class="mb-2">
                  <small class="text-muted">
                    <i class="fas fa-map-pin mr-1"></i>
                    <em>No coordinates</em>
                  </small>
                </div>
              @endif

              <!-- Description Preview -->
              @if($city->description_en)
                <p class="card-text small text-muted mb-2" style="font-size: 0.75rem; line-height: 1.2;">
                  {{ Str::limit($city->description_en, 50) }}
                </p>
              @endif

              <div class="mt-auto d-flex justify-content-between align-items-center">
                <span class="badge rounded-pill
                  {{ $city->status == \App\Models\City::STATUS_PUBLISHED 
                      ? 'bg-success' 
                      : 'bg-secondary' 
                  }} py-1 px-2" style="font-size: 0.7rem;">
                  {{ $city->status == \App\Models\City::STATUS_PUBLISHED ? 'Published' : 'Unpublished' }}
                </span>
                <small class="text-muted">#{{ $city->order }}</small>
              </div>
            </div>

            <div class="card-footer bg-white border-top-0 p-2">
              <div class="btn-group btn-block" role="group">
                <a 
                  href="{{ route('dashboard.cities.edit', $city->id) }}" 
                  class="btn btn-sm btn-outline-info"
                  title="Edit City"
                >
                  <i class="fas fa-edit"></i>
                </a>
                <button 
                  type="button" 
                  class="btn btn-sm btn-outline-danger" 
                  onclick="confirmDelete('delete-form-{{ $city->id }}')"
                  title="Delete City"
                  {{ ($city->farms_count > 0 || $city->areas_count > 0) ? 'disabled' : '' }}
                >
                  <i class="fas fa-trash"></i>
                </button>
              </div>
              
              @if($city->farms_count > 0 || $city->areas_count > 0)
                <small class="text-muted d-block text-center mt-1" style="font-size: 0.7rem;">
                  Cannot delete: contains data
                </small>
              @endif
              
              <form 
                id="delete-form-{{ $city->id }}" 
                action="{{ route('dashboard.cities.destroy', $city->id) }}" 
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