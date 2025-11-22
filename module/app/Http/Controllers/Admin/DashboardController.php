<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $userCount = User::count();
        $activeSubscriptions = Subscription::where('status', 'active')->count();
        $revenue = Invoice::where('status', 'paid')->sum('amount');

        return view('admin.dashboard', compact('userCount', 'activeSubscriptions', 'revenue'));
    }
}
