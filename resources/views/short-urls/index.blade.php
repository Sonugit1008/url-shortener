@extends('layouts.dashboard')

@section('title', 'Generated Short URLs')

@section('dashboard')

    <div class="mb-3">
        <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
    </div>

    <!-- All Generated Short URLs (SuperAdmin: all companies, Admin: own company) -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <h5 class="text-primary mb-0">Generated Short URLs</h5>

                <div class="d-flex gap-2">
                    @if (auth()->user()->isAdmin())
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#generateUrlModal">Generate</button>
                    @endif

                    <form method="GET">
                        <select name="period" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Time</option>
                            <option value="today" @selected(request('period') == 'today')>Today</option>
                            <option value="last_week" @selected(request('period') == 'last_week')>Last Week</option>
                            <option value="this_month" @selected(request('period') == 'this_month')>This Month</option>
                            <option value="last_month" @selected(request('period') == 'last_month')>Last Month</option>
                        </select>
                    </form>

                    <a href="{{ route('short-urls.download', ['period' => request('period')]) }}" class="btn btn-outline-primary btn-sm">Download</a>
                </div>

            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle small">
                    <thead class="table-light">
                        <tr>
                            <th>Short URL</th>
                            <th>Long URL</th>
                            <th>Hits</th>
                            @if (auth()->user()->isSuperAdmin())
                                <th>Company</th>
                            @endif
                            <th>Created By</th>
                            <th>Created On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($shortUrls as $url)
                            <tr>
                                <td><a href="{{ $url->short_url }}" target="_blank">{{ $url->short_url }}</a></td>
                                <td class="text-break">{{ Str::limit($url->original_url, 50) }}</td>
                                <td>{{ $url->clicks }}</td>
                                @if (auth()->user()->isSuperAdmin())
                                    <td>{{ $url->company?->name }}</td>
                                @endif
                                <td>{{ $url->user?->name }}</td>
                                <td>{{ $url->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No Data Available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center small">
                <span class="text-muted">Showing {{ $shortUrls->count() }} of total {{ $shortUrls->total() }}</span>
                {{ $shortUrls->links('pagination::simple-bootstrap-5') }}
            </div>

        </div>
    </div>

    {{-- SuperAdmin cannot create short urls --}}
    @if (auth()->user()->isAdmin())
        @include('partials.generate-short-url')
    @endif

@endsection
