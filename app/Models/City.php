<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'status',
        'image',
        'order',
        'latitude',
        'longitude',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Status constants
     */
    const STATUS_PUBLISHED = 'published';
    const STATUS_UNPUBLISHED = 'unpublished';

    /**
     * Get the users for the city.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the areas for the city.
     */
    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    /**
     * Get the farms for the city (through areas).
     */
    public function farms(): HasMany
    {
        return $this->hasMany(Farm::class);
    }

    /**
     * Get cities ordered by the order column
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    /**
     * Scope a query to only include published cities
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Get published areas for this city
     */
    public function publishedAreas()
    {
        return $this->areas()->published()->ordered();
    }

    /**
     * Get farms count attribute
     */
    public function getFarmsCountAttribute()
    {
        return $this->farms()->count();
    }

    /**
     * Get areas count attribute
     */
    public function getAreasCountAttribute()
    {
        return $this->areas()->count();
    }

    /**
     * Get active farms for this city
     */
    public function activeFarms()
    {
        return $this->farms()->active();
    }

    /**
     * Get active farms count attribute
     */
    public function getActiveFarmsCountAttribute()
    {
        // This will be used when active_farms_count is not loaded via withCount
        if (!array_key_exists('active_farms_count', $this->attributes)) {
            return $this->farms()->active()->count();
        }
        return $this->attributes['active_farms_count'];
    }

    /**
     * Get published areas count attribute
     */
    public function getPublishedAreasCountAttribute()
    {
        // This will be used when published_areas_count is not loaded via withCount
        if (!array_key_exists('published_areas_count', $this->attributes)) {
            return $this->areas()->published()->count();
        }
        return $this->attributes['published_areas_count'];
    }

    /**
     * Get the coordinates as a formatted string
     */
    public function getCoordinatesAttribute()
    {
        if ($this->latitude && $this->longitude) {
            return $this->latitude . ', ' . $this->longitude;
        }
        return null;
    }

    /**
     * Check if city has coordinates
     */
    public function hasCoordinates()
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }
}