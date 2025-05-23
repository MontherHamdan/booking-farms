<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Feature extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name_ar',
        'name_en',
        'icon',
        'order',
    ];

    /**
     * Get the farms that have this feature.
     */
    public function farms(): BelongsToMany
    {
        return $this->belongsToMany(Farm::class, 'farm_feature');
    }
}