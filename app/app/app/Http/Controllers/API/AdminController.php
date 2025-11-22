<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function subscriptions()
    {
        // Load subscriptions with user and plan relationships
        $subscriptions = Subscription::with(['user', 'plan'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($subscriptions);
    }

    public function invoices()
    {
        // Load invoices with user and subscription relationships
        $invoices = Invoice::with(['user', 'subscription'])
            ->orderBy('invoice_date', 'desc')
            ->get();

        return response()->json($invoices);
    }

    public function users()
    {
        $users = User::with(['subscriptions', 'invoices'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($users);
    }

    public function stats()
    {
        $totalUsers = User::count();
        $activeSubscriptions = Subscription::where('status', 'active')->count();
        $totalRevenue = Invoice::where('status', 'paid')->sum('total_amount');
        $pendingInvoices = Invoice::where('status', 'pending')->count();

        return response()->json([
            'total_users' => $totalUsers,
            'active_subscriptions' => $activeSubscriptions,
            'total_revenue' => $totalRevenue,
            'pending_invoices' => $pendingInvoices,
        ]);
    }
}
