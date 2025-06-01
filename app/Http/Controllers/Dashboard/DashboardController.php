<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        // ---Fetch 4 first users ---
        $recentUsers = User::orderBy('id', 'asc')->take(4)->get();

        return view('admin.dashboard', compact(
            'recentUsers'
        ));
    }
    
}
