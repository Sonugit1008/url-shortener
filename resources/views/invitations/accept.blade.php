@extends('layouts.app')

@section('title', 'Accept Invitation')

@section('content')

<div class="min-vh-100 d-flex align-items-center justify-content-center bg-light p-3">

    <div class="card border-0 shadow rounded-4 w-100" style="max-width: 430px;">
        <div class="card-body p-4 p-md-5">

            <h4 class="fw-bold mb-1">Join {{ $invitation->company->name }}</h4>
            <p class="text-muted small">You are invited as <strong>{{ $invitation->role->name }}</strong>. Set your password to continue.</p>

            @if ($errors->any())
                <div class="alert alert-danger small">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('invitations.accept', $invitation->token) }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" class="form-control" value="{{ $invitation->email }}" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $invitation->name) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Create Account</button>
            </form>

        </div>
    </div>

</div>

@endsection
