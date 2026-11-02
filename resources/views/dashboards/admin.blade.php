@extends('layouts.dashboard')

@section('title', 'Admin Dashboard')

@section('dashboard')

    <!-- Generated Short URLs (own company, latest 2) -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <h5 class="text-primary mb-0">Generated Short URLs</h5>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#generateUrlModal">Generate</button>

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
                                <td>{{ $url->user?->name }}</td>
                                <td>{{ $url->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No Data Available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center small">
                <span class="text-muted">Showing {{ $shortUrls->count() }} of total {{ $totalUrls }}</span>
                <a href="{{ route('short-urls.index', ['period' => request('period')]) }}" class="btn btn-outline-primary btn-sm">View All</a>
            </div>

        </div>
    </div>

    @include('partials.generate-short-url')

    <!-- Team Members -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-primary mb-0">Team Members</h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#inviteMemberModal">Invite</button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle small">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Total Generated URLs</th>
                            <th>Total URL Hits</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($teamMembers as $member)
                            <tr>
                                <td>{{ $member->name }}</td>
                                <td>{{ $member->email }}</td>
                                <td>{{ $member->role?->name }}</td>
                                <td>{{ $member->short_urls_count }}</td>
                                <td>{{ $member->short_urls_sum_clicks ?? 0 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center small">
                <span class="text-muted">Showing {{ $teamMembers->count() }} of total {{ $teamMembers->total() }}</span>
                {{ $teamMembers->links('pagination::simple-bootstrap-5') }}
            </div>

        </div>
    </div>

    <!-- Invite Team Member Modal -->
    <div class="modal fade" id="inviteMemberModal" tabindex="-1" aria-labelledby="inviteMemberModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <form method="POST" action="{{ route('admin.invite-member') }}">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title text-primary" id="inviteMemberModalLabel">Invite New Team Member</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="User Name" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="ex. sample@example.com" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Role</label>
                            <select name="role" class="form-select @error('role') is-invalid @enderror">
                                <option value="Member" @selected(old('role') == 'Member')>Member</option>
                                <option value="Admin" @selected(old('role') == 'Admin')>Admin</option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Send Invitation</button>
                    </div>

                </form>

            </div>
        </div>
    </div>

@endsection

@if ($errors->has('name') || $errors->has('email') || $errors->has('role'))
    @push('scripts')
        <script>
            // Reopen the modal when validation fails
            $(document).ready(function () {
                new bootstrap.Modal('#inviteMemberModal').show();
            });
        </script>
    @endpush
@endif
