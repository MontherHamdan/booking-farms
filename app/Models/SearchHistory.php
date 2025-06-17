<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchHistory extends Model
{
    use HasFactory;

    protected $table = 'search_histories';

    protected $fillable = [
        'user_id',
        'search_term',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Hide updated_at from responses since we only care about when the search was made
    protected $hidden = [
        'updated_at'
    ];

    /**
     * Get the user who made this search
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}