@extends('layouts.dashboard')

@section('title', 'Clients')

@section('dashboard')

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-primary mb-0">Clients</h5>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#inviteClientModal">Invite</button>
                    <a href="{{ route('superadmin.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle small">
                    <thead class="table-light">
                        <tr>
                            <th>Client Name</th>
                            <th>Users</th>
                            <th>Total Generated URLs</th>
                            <th>Total URL Hits</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($clients as $client)
                            <tr>
                                <td>{{ $client->name }}</td>
                                <td>{{ $client->users_count }}</td>
                                <td>{{ $client->short_urls_count }}</td>
                                <td>{{ $client->short_urls_sum_clicks ?? 0 }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No Data Available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center small">
                <span class="text-muted">Showing {{ $clients->count() }} of total {{ $clients->total() }}</span>
                {{ $clients->links('pagination::simple-bootstrap-5') }}
            </div>

        </div>
    </div>

    @include('partials.invite-client-modal')

@endsection
