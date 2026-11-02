<?php

namespace App\Http\Controllers;

use App\Models\ShortUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShortUrlController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'original_url' => ['required', 'url', 'max:2048'],
        ]);

        $user = $request->user();
        $shortUrl = ShortUrl::create([
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'original_url' => $request->original_url,
            'short_code' => Str::random(6),
        ]);

        return back()->with('success', 'Short URL generated: ' . $shortUrl->short_url);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $query = ShortUrl::with(['user', 'company']);

        if ($user->isAdmin()) {
            $query->where('company_id', $user->company_id);
        }

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

        return view('short-urls.index', compact('shortUrls'));
    }

    public function download(Request $request)
    {
        $user = $request->user();
        $query = ShortUrl::with(['user', 'company']);
        if ($user->isAdmin()) {
            $query->where('company_id', $user->company_id);
        } elseif ($user->isMember()) {
            $query->where('user_id', $user->id);
        }

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

        $shortUrls = $query->latest()->get();

        return response()->streamDownload(function () use ($shortUrls) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Short URL', 'Long URL', 'Hits', 'Created By', 'Company', 'Created On']);

            foreach ($shortUrls as $url) {
                fputcsv($file, [
                    $url->short_url,
                    $url->original_url,
                    $url->clicks,
                    $url->user?->name,
                    $url->company?->name,
                    $url->created_at->format('d M Y'),
                ]);
            }

            fclose($file);
        }, 'short-urls-' . now()->format('Y-m-d') . '.csv');
    }

    public function redirect(string $code)
    {
        $shortUrl = ShortUrl::where('short_code', $code)->firstOrFail();
        $shortUrl->increment('clicks', 1, ['last_clicked_at' => now()]);
        return redirect()->away($shortUrl->original_url);
    }
}
