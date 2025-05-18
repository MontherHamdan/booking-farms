<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use DB;
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
