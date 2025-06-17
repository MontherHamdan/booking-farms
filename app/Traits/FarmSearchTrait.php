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
        
        if ($user) {
            $this->storeSearchHistory($user->id, $searchQuery);
        }
    }

    /**
     * Store search history for authenticated users
     * Prevents duplicate consecutive searches
     */
    private function storeSearchHistory($userId, $searchQuery): void
    {
        try {
            // Check if the last search by this user was the same query
            $lastSearch = SearchHistory::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->first();

            // Only store if it's different from the last search or if no previous search exists
            if (!$lastSearch || $lastSearch->search_term !== $searchQuery) {
                SearchHistory::create([
                    'user_id' => $userId,
                    'search_term' => $searchQuery,
                    'created_at' => now()
                ]);

                // Keep only the last 50 searches per user to prevent unlimited growth
                $this->cleanupOldSearchHistory($userId);
            } else {
                // Update the timestamp of the existing search to bring it to the top
                $lastSearch->touch();
            }
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
            
            if ($totalCount > 20) {
                // Get IDs of records to delete (all except the newest 20)
                $searchHistoryIds = SearchHistory::where('user_id', $userId)
                    ->orderBy('created_at', 'desc')
                    ->skip(20)
                    ->take($totalCount - 20)
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