<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function superadmin(Request $request)
    {
        $clients = Company::withCount(['users', 'shortUrls'])
            ->withSum('shortUrls', 'clicks')
            ->latest()
            ->take(2)
            ->get();

        $totalClients = Company::count();


        $query = ShortUrl::with(['user', 'company']);

        if ($request->period == 'today') {
            $query->whereDate('created_at', today());
        }

        if ($request->period == 'last_week') {
            $query->where('created_at', '>=', now()->subWeek());
        }

        if ($request->period == 'this_month') {
            $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
        }

        if ($request->period == 'last_month') {
            $lastMonth = now()->startOfMonth()->subMonth();
            $query->whereMonth('created_at', $lastMonth->month)->whereYear('created_at', $lastMonth->year);
        }

        $totalUrls = $query->count();
        $shortUrls = $query->latest()->take(2)->get();

        return view('dashboards.superadmin', compact('clients', 'totalClients', 'shortUrls', 'totalUrls'));
    }

    public function clients()
    {
        $clients = Company::withCount(['users', 'shortUrls'])
            ->withSum('shortUrls', 'clicks')
            ->latest()
            ->paginate(10);

        return view('dashboards.clients', compact('clients'));
    }

    public function admin(Request $request)
    {
        $teamMembers = User::with('role')
            ->where('company_id', $request->user()->company_id)
            ->withCount('shortUrls')
            ->withSum('shortUrls', 'clicks')
            ->latest()
            ->paginate(10, pageName: 'members_page');

        // Admin sees urls of own company only
        $query = ShortUrl::with(['user', 'company'])
            ->where('company_id', $request->user()->company_id);

        // Date filter
        if ($request->period == 'today') {
            $query->whereDate('created_at', today());
        }

        if ($request->period == 'last_week') {
            $query->where('created_at', '>=', now()->subWeek());
        }

        if ($request->period == 'this_month') {
            $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
        }

        if ($request->period == 'last_month') {
            $lastMonth = now()->startOfMonth()->subMonth();
            $query->whereMonth('created_at', $lastMonth->month)->whereYear('created_at', $lastMonth->year);
        }

        $totalUrls = $query->count();
        $shortUrls = $query->latest()->take(2)->get();

        return view('dashboards.admin', compact('teamMembers', 'shortUrls', 'totalUrls'));
    }

    public function member(Request $request)
    {
        $query = ShortUrl::with(['user', 'company'])
            ->where('user_id', $request->user()->id);

        if ($request->period == 'today') {
            $query->whereDate('created_at', today());
        }

        if ($request->period == 'last_week') {
            $query->where('created_at', '>=', now()->subWeek());
        }

        if ($request->period == 'this_month') {
            $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
        }

        if ($request->period == 'last_month') {
            $lastMonth = now()->startOfMonth()->subMonth();
            $query->whereMonth('created_at', $lastMonth->month)->whereYear('created_at', $lastMonth->year);
        }

        $shortUrls = $query->latest()->paginate(10);

        return view('dashboards.member', compact('shortUrls'));
    }
}
