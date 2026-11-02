@extends('layouts.app')

@section('content')

<nav class="navbar bg-white border-bottom border-2">
    <div class="container">

        <span class="navbar-brand">
            Dashboard
        </span>

        <div>
            <span class="text-muted small me-3">
                {{ auth()->user()->name }} ({{ auth()->user()->role?->name }})
                @if (auth()->user()->company)
                    - {{ auth()->user()->company->name }}
                @endif
            </span>
            <button type="button" id="logoutButton" class="btn btn-outline-danger btn-sm">Logout</button>
        </div>

    </div>
</nav>

<div class="container py-4">

    @if (session('success'))
        <div class="alert alert-success text-break">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('dashboard')

</div>

@endsection
