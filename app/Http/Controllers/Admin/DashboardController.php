<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContactRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\ContactRequest;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $pendingRequestsCount = ContactRequest::query()
            ->where('status', ContactRequestStatus::Pending)
            ->count();

        $recentRequests = ContactRequest::query()
            ->where('created_at', '>=', now()->subDays(7))
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'pendingRequestsCount',
            'recentRequests'
        ));
    }
}
