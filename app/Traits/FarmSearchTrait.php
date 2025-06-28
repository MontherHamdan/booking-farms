<?php

namespace App\Traits;

use App\Models\SearchHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

trait FarmSearchTrait
{

    /**
     * Handle search history for authenticated users
     */
    private function handleSearchHistory($searchQuery): void
    {
        $user = Auth::guard('sanctum')->user();
        
        if ($user && !empty(trim($searchQuery))) {
            $this->storeSearchHistory($user->id, trim($searchQuery));
        }
    }

    /**
     * Store search history - ensures each search term appears only once
     * Most recent usage always appears at the top
     */
    private function storeSearchHistory($userId, $searchQuery): void
    {
        try {
            // First, check if this exact search term already exists for this user
            $existingSearch = SearchHistory::where('user_id', $userId)
                ->where('search_term', $searchQuery)
                ->first();

            if ($existingSearch) {
                // Delete the existing entry so we can create a fresh one at the top
                $existingSearch->delete();
            }

            // Create new entry (will be the most recent)
            SearchHistory::create([
                'user_id' => $userId,
                'search_term' => $searchQuery,
                'created_at' => now()
            ]);

            // Clean up old entries
            $this->cleanupOldSearchHistory($userId);

        } catch (Exception $e) {
            // Log the error but don't fail the search if history storage fails
            $this->logException($e, ['action' => 'store search history', 'user_id' => $userId]);
        }
    }

    /**
     * Clean up old search history entries (keep only last 20 per user)
     */
    private function cleanupOldSearchHistory($userId): void
    {
        try {
            // Count total search history entries for this user
            $totalCount = SearchHistory::where('user_id', $userId)->count();
            
            $maxEntries = 20; // Keep only 20 unique searches
            
            if ($totalCount > $maxEntries) {
                // Get IDs of records to delete (all except the newest ones)
                $searchHistoryIds = SearchHistory::where('user_id', $userId)
                    ->orderBy('created_at', 'desc')
                    ->skip($maxEntries)
                    ->take($totalCount - $maxEntries)
                    ->pluck('id');

                if ($searchHistoryIds->isNotEmpty()) {
                    SearchHistory::whereIn('id', $searchHistoryIds)->delete();
                }
            }
        } catch (Exception $e) {
            $this->logException($e, ['action' => 'cleanup search history', 'user_id' => $userId]);
        }
    }
}