@extends('admin.layout')

@section('content')
<div class="container">
    <h3 class="text-center mb-4">Edit Pre-made Category</h3>
    <form action="{{ route('premade-categories.update', $premadeCategory->id) }}" method="POST" class="p-4 bg-light rounded shadow-sm">
        @csrf
        @method('PUT')

        <!-- Category Name -->
        <div class="mb-3">
            <label for="name" class="form-label fw-bold">Category Name</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $premadeCategory->name) }}" placeholder="Enter category name" required>
            @error('name')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <!-- Price Type -->
        <div class="mb-3">
            <label for="price_type" class="form-label fw-bold">Price Type</label>
            <select name="price_type" id="price_type" class="form-select" required>
                <option value="">Select price type</option>
                <option value="per_hour" {{ old('price_type', $premadeCategory->price_type) == 'per_hour' ? 'selected' : '' }}>Per Hour</option>
                <option value="per_game" {{ old('price_type', $premadeCategory->price_type) == 'per_game' ? 'selected' : '' }}>Per Game</option>
                <option value="both" {{ old('price_type', $premadeCategory->price_type) == 'both' ? 'selected' : '' }}>Both</option>
                <option value="none" {{ old('price_type', $premadeCategory->price_type) == 'none' ? 'selected' : '' }}>None</option>
            </select>
            @error('price_type')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <!-- Prices -->
        <div class="row g-3">
            <div class="col-md-6 price-field per-hour-price">
                <div class="mb-3">
                    <label for="price_per_hour" class="form-label fw-bold">Price Per Hour</label>
                    <input type="number" name="price_per_hour" id="price_per_hour" class="form-control" value="{{ old('price_per_hour', $premadeCategory->price_per_hour) }}" step="0.01" placeholder="e.g., 19.99">
                    @error('price_per_hour')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>
            <div class="col-md-6 price-field per-game-price">
                <div class="mb-3">
                    <label for="price_per_game" class="form-label fw-bold">Price Per Game</label>
                    <input type="number" name="price_per_game" id="price_per_game" class="form-control" value="{{ old('price_per_game', $premadeCategory->price_per_game) }}" step="0.01" placeholder="e.g., 29.99">
                    @error('price_per_game')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Status -->
        <div class="mb-3">
            <label for="status" class="form-label fw-bold">Status</label>
            <select name="status" id="statuss" class="form-select" required>
                <option value="active" {{ old('status', $premadeCategory->status) == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status', $premadeCategory->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            @error('status')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <!-- Category Image -->
        <div class="mb-3">
            <label class="form-label fw-bold">Category Image</label>
            <div class="input-group">
                <input type="hidden" name="image_id" id="image_id" value="{{ old('image_id', $premadeCategory->image_id) }}" required>
                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#imageModal">
                    Select Image
                </button>
                <div id="selected-image-preview" class="ms-3 d-flex align-items-center">
                    @if($premadeCategory->image)
                        <img src="{{ $premadeCategory->image->image_path }}" alt="Selected Image" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                    @endif
                </div>
            </div>
            @error('image_id')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <!-- Subcategories Section -->
        <div class="mb-4 mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold">Subcategories</h5>
                <button type="button" class="btn btn-sm btn-success" id="add-subcategory">
                    <i class="bi bi-plus-circle"></i> Add Subcategory
                </button>
            </div>
            <div id="subcategories-container">
                @if(old('subcategories', $premadeCategory->subcategories->toArray()))
                    @foreach(old('subcategories', $premadeCategory->subcategories->toArray()) as $index => $subcategory)
                        <div class="subcategory-item card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Subcategory #<span class="subcategory-number">{{ $index + 1 }}</span></h6>
                                <button type="button" class="btn btn-sm btn-danger remove-subcategory">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <!-- Subcategory Name -->
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">Name</label>
                                            <input type="text" name="subcategories[{{ $index }}][name]" class="form-control" value="{{ $subcategory['name'] ?? '' }}" placeholder="Enter subcategory name" required>
                                            @error("subcategories.$index.name")
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                    <!-- Subcategory Price Type -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Price Type</label>
                                            <select name="subcategories[{{ $index }}][price_type]" class="form-select subcategory-price-type" required>
                                                <option value="">Select price type</option>
                                                <option value="per_hour" {{ ($subcategory['price_type'] ?? '') == 'per_hour' ? 'selected' : '' }}>Per Hour</option>
                                                <option value="per_game" {{ ($subcategory['price_type'] ?? '') == 'per_game' ? 'selected' : '' }}>Per Game</option>
                                                <option value="both" {{ ($subcategory['price_type'] ?? '') == 'both' ? 'selected' : '' }}>Both</option>
                                                <option value="none" {{ ($subcategory['price_type'] ?? '') == 'none' ? 'selected' : '' }}>None</option>
                                            </select>
                                            @error("subcategories.$index.price_type")
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                    <!-- Subcategory Status -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select name="subcategories[{{ $index }}][status]" class="form-select" required>
                                                <option value="available" {{ ($subcategory['status'] ?? '') == 'available' ? 'selected' : '' }}>Available</option>
                                                <option value="unavailable" {{ ($subcategory['status'] ?? '') == 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                                                <option value="maintenance" {{ ($subcategory['status'] ?? '') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                            </select>
                                            @error("subcategories.$index.status")
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                    <!-- Subcategory Prices -->
                                    <div class="col-md-6 subcategory-price-field subcategory-per-hour-price">
                                        <div class="mb-3">
                                            <label class="form-label">Price Per Hour</label>
                                            <input type="number" name="subcategories[{{ $index }}][price_per_hour]" class="form-control" value="{{ $subcategory['price_per_hour'] ?? '' }}" step="0.01" placeholder="e.g., 19.99">
                                            @error("subcategories.$index.price_per_hour")
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6 subcategory-price-field subcategory-per-game-price">
                                        <div class="mb-3">
                                            <label class="form-label">Price Per Game</label>
                                            <input type="number" name="subcategories[{{ $index }}][price_per_game]" class="form-control" value="{{ $subcategory['price_per_game'] ?? '' }}" step="0.01" placeholder="e.g., 29.99">
                                            @error("subcategories.$index.price_per_game")
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Additional Items Section -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold">Additional Items</h5>
                <button type="button" class="btn btn-sm btn-success" id="add-additional-item">
                    <i class="bi bi-plus-circle"></i> Add Additional Item
                </button>
            </div>
            <div id="additional-items-container">
                @if(old('additional_items', $premadeCategory->additionalItems->toArray()))
                    @foreach(old('additional_items', $premadeCategory->additionalItems->toArray()) as $index => $item)
                        <div class="additional-item card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Additional Item #<span class="additional-item-number">{{ $index + 1 }}</span></h6>
                                <button type="button" class="btn btn-sm btn-danger remove-additional-item">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <!-- Additional Item Name -->
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">Name</label>
                                            <input type="text" name="additional_items[{{ $index }}][name]" class="form-control" value="{{ $item['name'] ?? '' }}" placeholder="Enter item name" required>
                                            @error("additional_items.$index.name")
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                    <!-- Additional Item Price Type -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Price Type</label>
                                            <select name="additional_items[{{ $index }}][price_type]" class="form-select additional-item-price-type" required>
                                                <option value="">Select price type</option>
                                                <option value="per_hour" {{ ($item['price_type'] ?? '') == 'per_hour' ? 'selected' : '' }}>Per Hour</option>
                                                <option value="per_game" {{ ($item['price_type'] ?? '') == 'per_game' ? 'selected' : '' }}>Per Game</option>
                                                <option value="both" {{ ($item['price_type'] ?? '') == 'both' ? 'selected' : '' }}>Both</option>
                                                <option value="none" {{ ($item['price_type'] ?? '') == 'none' ? 'selected' : '' }}>None</option>
                                            </select>
                                            @error("additional_items.$index.price_type")
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                    <!-- Additional Item Count -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Count</label>
                                            <input type="number" name="additional_items[{{ $index }}][count]" class="form-control" value="{{ $item['count'] ?? 0 }}" min="0" required>
                                            @error("additional_items.$index.count")
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                    <!-- Additional Item Prices -->
                                    <div class="col-md-6 additional-item-price-field additional-item-per-hour-price">
                                        <div class="mb-3">
                                            <label class="form-label">Price Per Hour</label>
                                            <input type="number" name="additional_items[{{ $index }}][price_per_hour]" class="form-control" value="{{ $item['price_per_hour'] ?? '' }}" step="0.01" placeholder="e.g., 19.99">
                                            @error("additional_items.$index.price_per_hour")
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6 additional-item-price-field additional-item-per-game-price">
                                        <div class="mb-3">
                                            <label class="form-label">Price Per Game</label>
                                            <input type="number" name="additional_items[{{ $index }}][price_per_game]" class="form-control" value="{{ $item['price_per_game'] ?? '' }}" step="0.01" placeholder="e.g., 29.99">
                                            @error("additional_items.$index.price_per_game")
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Games Section -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold">Games</h5>
                <button type="button" class="btn btn-sm btn-success" id="add-game">
                    <i class="bi bi-plus-circle"></i> Add Game
                </button>
            </div>
            <div id="games-container">
                @if(old('games', $premadeCategory->games->toArray()))
                    @foreach(old('games', $premadeCategory->games->toArray()) as $index => $game)
                        <div class="game-item card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Game #<span class="game-number">{{ $index + 1 }}</span></h6>
                                <button type="button" class="btn btn-sm btn-danger remove-game">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <!-- Game Name -->
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">Name</label>
                                            <input type="text" name="games[{{ $index }}][name]" class="form-control" value="{{ $game['name'] ?? '' }}" placeholder="Enter game name" required>
                                            @error("games.$index.name")
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                    <!-- Game Price Type -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Price Type</label>
                                            <select name="games[{{ $index }}][price_type]" class="form-select game-price-type" required>
                                                <option value="">Select price type</option>
                                                <option value="per_hour" {{ ($game['price_type'] ?? '') == 'per_hour' ? 'selected' : '' }}>Per Hour</option>
                                                <option value="per_game" {{ ($game['price_type'] ?? '') == 'per_game' ? 'selected' : '' }}>Per Game</option>
                                                <option value="both" {{ ($game['price_type'] ?? '') == 'both' ? 'selected' : '' }}>Both</option>
                                                <option value="none" {{ ($game['price_type'] ?? '') == 'none' ? 'selected' : '' }}>None</option>
                                            </select>
                                            @error("games.$index.price_type")
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                    <!-- Game Count -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Count</label>
                                            <input type="number" name="games[{{ $index }}][count]" class="form-control" value="{{ $game['count'] ?? 0 }}" min="0" required>
                                            @error("games.$index.count")
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                    <!-- Game Prices -->
                                    <div class="col-md-6 game-price-field game-per-hour-price">
                                        <div class="mb-3">
                                            <label class="form-label">Price Per Hour</label>
                                            <input type="number" name="games[{{ $index }}][price_per_hour]" class="form-control" value="{{ $game['price_per_hour'] ?? '' }}" step="0.01" placeholder="e.g., 19.99">
                                            @error("games.$index.price_per_hour")
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6 game-price-field game-per-game-price">
                                        <div class="mb-3">
                                            <label class="form-label">Price Per Game</label>
                                            <input type="number" name="games[{{ $index }}][price_per_game]" class="form-control" value="{{ $game['price_per_game'] ?? '' }}" step="0.01" placeholder="e.g., 29.99">
                                            @error("games.$index.price_per_game")
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Submit Button -->
        <div class="d-grid">
            <button type="submit" class="btn btn-primary">Update Category</button>
        </div>
    </form>
</div>

<!-- Image Selection Modal (same as before) -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Select an Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    @forelse($images as $image)
                        <div class="col-md-3">
                            <div class="card image-card" data-id="{{ $image->id }}" data-url="{{ $image->image_path }}">
                                <img src="{{ $image->image_path }}" class="card-img-top" alt="Image" style="height: 150px; object-fit: cover; cursor: pointer;">
                                <div class="card-body text-center p-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary select-image">Select</button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center">No images available.</p>
                    @endforelse
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Templates for dynamic elements -->
<template id="subcategory-template">
    <div class="subcategory-item card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Subcategory #<span class="subcategory-number">__INDEX__</span></h6>
            <button type="button" class="btn btn-sm btn-danger remove-subcategory">
                <i class="bi bi-x-circle"></i>
            </button>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <!-- Subcategory Name -->
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="subcategories[__INDEX__][name]" class="form-control" placeholder="Enter subcategory name" required>
                    </div>
                </div>
                <!-- Subcategory Price Type -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Price Type</label>
                        <select name="subcategories[__INDEX__][price_type]" class="form-select subcategory-price-type" required>
                            <option value="">Select price type</option>
                            <option value="per_hour">Per Hour</option>
                            <option value="per_game">Per Game</option>
                            <option value="both">Both</option>
                            <option value="none">None</option>
                        </select>
                    </div>
                </div>
                <!-- Subcategory Status -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="subcategories[__INDEX__][status]" class="form-select" required>
                            <option value="available">Available</option>
                            <option value="unavailable">Unavailable</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                </div>
                <!-- Subcategory Prices -->
                <div class="col-md-6 subcategory-price-field subcategory-per-hour-price">
                    <div class="mb-3">
                        <label class="form-label">Price Per Hour</label>
                        <input type="number" name="subcategories[__INDEX__][price_per_hour]" class="form-control" step="0.01" placeholder="e.g., 19.99">
                    </div>
                </div>
                <div class="col-md-6 subcategory-price-field subcategory-per-game-price">
                    <div class="mb-3">
                        <label class="form-label">Price Per Game</label>
                        <input type="number" name="subcategories[__INDEX__][price_per_game]" class="form-control" step="0.01" placeholder="e.g., 29.99">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<template id="additional-item-template">
    <div class="additional-item card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Additional Item #<span class="additional-item-number">__INDEX__</span></h6>
            <button type="button" class="btn btn-sm btn-danger remove-additional-item">
                <i class="bi bi-x-circle"></i>
            </button>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <!-- Additional Item Name -->
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="additional_items[__INDEX__][name]" class="form-control" placeholder="Enter item name" required>
                    </div>
                </div>
                <!-- Additional Item Price Type -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Price Type</label>
                        <select name="additional_items[__INDEX__][price_type]" class="form-select additional-item-price-type" required>
                            <option value="">Select price type</option>
                            <option value="per_hour">Per Hour</option>
                            <option value="per_game">Per Game</option>
                            <option value="both">Both</option>
                            <option value="none">None</option>
                        </select>
                    </div>
                </div>
                <!-- Additional Item Count -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Count</label>
                        <input type="number" name="additional_items[__INDEX__][count]" class="form-control" value="0" min="0" required>
                    </div>
                </div>
                <!-- Additional Item Prices -->
                <div class="col-md-6 additional-item-price-field additional-item-per-hour-price">
                    <div class="mb-3">
                        <label class="form-label">Price Per Hour</label>
                        <input type="number" name="additional_items[__INDEX__][price_per_hour]" class="form-control" step="0.01" placeholder="e.g., 19.99">
                    </div>
                </div>
                <div class="col-md-6 additional-item-price-field additional-item-per-game-price">
                    <div class="mb-3">
                        <label class="form-label">Price Per Game</label>
                        <input type="number" name="additional_items[__INDEX__][price_per_game]" class="form-control" step="0.01" placeholder="e.g., 29.99">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<template id="game-template">
    <div class="game-item card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Game #<span class="game-number">__INDEX__</span></h6>
            <button type="button" class="btn btn-sm btn-danger remove-game">
                <i class="bi bi-x-circle"></i>
            </button>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <!-- Game Name -->
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="games[__INDEX__][name]" class="form-control" placeholder="Enter game name" required>
                    </div>
                </div>
                <!-- Game Price Type -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Price Type</label>
                        <select name="games[__INDEX__][price_type]" class="form-select game-price-type" required>
                            <option value="">Select price type</option>
                            <option value="per_hour">Per Hour</option>
                            <option value="per_game">Per Game</option>
                            <option value="both">Both</option>
                            <option value="none">None</option>
                        </select>
                    </div>
                </div>
                <!-- Game Count -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Count</label>
                        <input type="number" name="games[__INDEX__][count]" class="form-control" value="0" min="0" required>
                    </div>
                </div>
                <!-- Game Prices -->
                <div class="col-md-6 game-price-field game-per-hour-price">
                    <div class="mb-3">
                        <label class="form-label">Price Per Hour</label>
                        <input type="number" name="games[__INDEX__][price_per_hour]" class="form-control" step="0.01" placeholder="e.g., 19.99">
                    </div>
                </div>
                <div class="col-md-6 game-price-field game-per-game-price">
                    <div class="mb-3">
                        <label class="form-label">Price Per Game</label>
                        <input type="number" name="games[__INDEX__][price_per_game]" class="form-control" step="0.01" placeholder="e.g., 29.99">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- JavaScript for dynamic behavior -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // IMAGE SELECTION
        const imageCards = document.querySelectorAll('.image-card');
        const imageIdInput = document.getElementById('image_id');
        const selectedImagePreview = document.getElementById('selected-image-preview');
        imageCards.forEach(card => {
            card.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const url = this.getAttribute('data-url');
                imageIdInput.value = id;
                selectedImagePreview.innerHTML = `<img src="${url}" alt="Selected Image" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">`;
                imageCards.forEach(c => c.classList.remove('border-primary'));
                this.classList.add('border', 'border-primary');
            });
        });
        // PRICE FIELDS VISIBILITY FOR MAIN CATEGORY
        const priceTypeSelect = document.getElementById('price_type');
        const perHourPrice = document.querySelector('.per-hour-price');
        const perGamePrice = document.querySelector('.per-game-price');
        function updatePriceFields() {
            const priceType = priceTypeSelect.value;
            perHourPrice.style.display = 'none';
            perGamePrice.style.display = 'none';
            if (priceType === 'per_hour' || priceType === 'both') {
                perHourPrice.style.display = 'block';
            }
            if (priceType === 'per_game' || priceType === 'both') {
                perGamePrice.style.display = 'block';
            }
        }
        updatePriceFields();
        priceTypeSelect.addEventListener('change', updatePriceFields);

        // SUBCATEGORIES MANAGEMENT
        const addSubcategoryBtn = document.getElementById('add-subcategory');
        const subcategoriesContainer = document.getElementById('subcategories-container');
        const subcategoryTemplate = document.getElementById('subcategory-template').innerHTML;
        let subcategoryCount = subcategoriesContainer.querySelectorAll('.subcategory-item').length || 0;
        function updateSubcategoryNumbers() {
            document.querySelectorAll('.subcategory-number').forEach((el, index) => {
                el.textContent = index + 1;
            });
        }
        function updateSubcategoryPriceFields(item) {
            const priceType = item.querySelector('.subcategory-price-type').value;
            const perHour = item.querySelector('.subcategory-per-hour-price');
            const perGame = item.querySelector('.subcategory-per-game-price');
            perHour.style.display = 'none';
            perGame.style.display = 'none';
            if (priceType === 'per_hour' || priceType === 'both') perHour.style.display = 'block';
            if (priceType === 'per_game' || priceType === 'both') perGame.style.display = 'block';
        }
        addSubcategoryBtn.addEventListener('click', function() {
            let newSubcategory = subcategoryTemplate.replace(/__INDEX__/g, subcategoryCount);
            subcategoriesContainer.insertAdjacentHTML('beforeend', newSubcategory);
            const newItem = subcategoriesContainer.lastElementChild;
            updateSubcategoryPriceFields(newItem);
            newItem.querySelector('.subcategory-price-type').addEventListener('change', function() {
                updateSubcategoryPriceFields(newItem);
            });
            newItem.querySelector('.remove-subcategory').addEventListener('click', function() {
                newItem.remove();
                updateSubcategoryNumbers();
            });
            subcategoryCount++;
            updateSubcategoryNumbers();
        });
        document.querySelectorAll('.subcategory-item').forEach(item => {
            updateSubcategoryPriceFields(item);
            item.querySelector('.subcategory-price-type').addEventListener('change', function() {
                updateSubcategoryPriceFields(item);
            });
            item.querySelector('.remove-subcategory').addEventListener('click', function() {
                item.remove();
                updateSubcategoryNumbers();
            });
        });

        // ADDITIONAL ITEMS MANAGEMENT
        const addAdditionalItemBtn = document.getElementById('add-additional-item');
        const additionalItemsContainer = document.getElementById('additional-items-container');
        const additionalItemTemplate = document.getElementById('additional-item-template').innerHTML;
        let additionalItemCount = additionalItemsContainer.querySelectorAll('.additional-item').length || 0;
        function updateAdditionalItemNumbers() {
            document.querySelectorAll('.additional-item-number').forEach((el, index) => {
                el.textContent = index + 1;
            });
        }
        function updateAdditionalItemPriceFields(item) {
            const priceType = item.querySelector('.additional-item-price-type').value;
            const perHour = item.querySelector('.additional-item-per-hour-price');
            const perGame = item.querySelector('.additional-item-per-game-price');
            perHour.style.display = 'none';
            perGame.style.display = 'none';
            if (priceType === 'per_hour' || priceType === 'both') perHour.style.display = 'block';
            if (priceType === 'per_game' || priceType === 'both') perGame.style.display = 'block';
        }
        addAdditionalItemBtn.addEventListener('click', function() {
            let newItem = additionalItemTemplate.replace(/__INDEX__/g, additionalItemCount);
            additionalItemsContainer.insertAdjacentHTML('beforeend', newItem);
            const newElement = additionalItemsContainer.lastElementChild;
            updateAdditionalItemPriceFields(newElement);
            newElement.querySelector('.additional-item-price-type').addEventListener('change', function() {
                updateAdditionalItemPriceFields(newElement);
            });
            newElement.querySelector('.remove-additional-item').addEventListener('click', function() {
                newElement.remove();
                updateAdditionalItemNumbers();
            });
            additionalItemCount++;
            updateAdditionalItemNumbers();
        });
        document.querySelectorAll('.additional-item').forEach(item => {
            updateAdditionalItemPriceFields(item);
            item.querySelector('.additional-item-price-type').addEventListener('change', function() {
                updateAdditionalItemPriceFields(item);
            });
            item.querySelector('.remove-additional-item').addEventListener('click', function() {
                item.remove();
                updateAdditionalItemNumbers();
            });
        });

        // GAMES MANAGEMENT
        const addGameBtn = document.getElementById('add-game');
        const gamesContainer = document.getElementById('games-container');
        const gameTemplate = document.getElementById('game-template').innerHTML;
        let gameCount = gamesContainer.querySelectorAll('.game-item').length || 0;
        function updateGameNumbers() {
            document.querySelectorAll('.game-number').forEach((el, index) => {
                el.textContent = index + 1;
            });
        }
        function updateGamePriceFields(item) {
            const priceType = item.querySelector('.game-price-type').value;
            const perHour = item.querySelector('.game-per-hour-price');
            const perGame = item.querySelector('.game-per-game-price');
            perHour.style.display = 'none';
            perGame.style.display = 'none';
            if (priceType === 'per_hour' || priceType === 'both') perHour.style.display = 'block';
            if (priceType === 'per_game' || priceType === 'both') perGame.style.display = 'block';
        }
        addGameBtn.addEventListener('click', function() {
            let newGame = gameTemplate.replace(/__INDEX__/g, gameCount);
            gamesContainer.insertAdjacentHTML('beforeend', newGame);
            const newItem = gamesContainer.lastElementChild;
            updateGamePriceFields(newItem);
            newItem.querySelector('.game-price-type').addEventListener('change', function() {
                updateGamePriceFields(newItem);
            });
            newItem.querySelector('.remove-game').addEventListener('click', function() {
                newItem.remove();
                updateGameNumbers();
            });
            gameCount++;
            updateGameNumbers();
        });
        document.querySelectorAll('.game-item').forEach(item => {
            updateGamePriceFields(item);
            item.querySelector('.game-price-type').addEventListener('change', function() {
                updateGamePriceFields(item);
            });
            item.querySelector('.remove-game').addEventListener('click', function() {
                item.remove();
                updateGameNumbers();
            });
        });
        
        // If an old image is set, trigger its selection
        const oldImageId = "{{ old('image_id', $premadeCategory->image_id) }}";
        if(oldImageId) {
            const oldCard = document.querySelector(`.image-card[data-id="${oldImageId}"]`);
            if(oldCard) oldCard.click();
        }
    });
</script>
@endsection
